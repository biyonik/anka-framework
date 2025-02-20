<?php

declare(strict_types=1);

namespace Framework\Core\Http\Request;

use Framework\Core\Http\Request\Interfaces\RequestInterface;
use Framework\Core\Http\Request\Traits\{RequestTrait, UploadedFilesTrait};
use Framework\Core\Http\Message\{Stream, Uri};
use Psr\Http\Message\{UriInterface, StreamInterface};
use InvalidArgumentException;

/**
 * HTTP Request'lerinin concrete implementasyonu.
 * 
 * Bu sınıf, PSR-7 uyumlu ve framework'e özgü request işlevselliğini sağlar.
 * HTTP request'lerinin tüm yönlerini (headers, body, query parameters, files vs.)
 * yönetir ve immutable bir yapı sunar.
 * 
 * Özellikler:
 * - PSR-7 uyumlu request implementasyonu
 * - Query string, body, headers ve cookie yönetimi
 * - Dosya upload desteği
 * - JSON request desteği
 * - XHR/AJAX request tespiti
 * - Route parametre yönetimi
 * 
 * @package Framework\Core\Http
 * @subpackage Request
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Request implements RequestInterface
{
    use RequestTrait;
    use UploadedFilesTrait;

    /**
     * HTTP protokol versiyonu.
     */
    protected string $protocolVersion = '1.1';

    /**
     * HTTP metodu.
     */
    protected string $method;

    /**
     * Request target.
     */
    protected string $requestTarget;

    /**
     * URI instance'ı.
     */
    protected UriInterface $uri;

    /**
     * Headers.
     * 
     * @var array<string,array<string>>
     */
    protected array $headers = [];

    /**
     * Header isimleri için case mapping.
     * 
     * @var array<string,string>
     */
    protected array $headerNames = [];

    /**
     * Request body'si.
     */
    protected StreamInterface $body;

    /**
     * Server parametreleri.
     * 
     * @var array<string,mixed>
     */
    protected array $serverParams;

    /**
     * Cookie parametreleri.
     * 
     * @var array<string,mixed>
     */
    protected array $cookieParams = [];

    /**
     * Query string parametreleri.
     * 
     * @var array<string,mixed>
     */
    protected array $queryParams = [];

    /**
     * Yüklenen dosyalar.
     * 
     * @var array<string,mixed>
     */
    protected array $uploadedFiles = [];

    /**
     * Parse edilmiş body.
     * 
     * @var array<string,mixed>|object|null
     */
    protected array|object|null $parsedBody = null;

    /**
     * Request attributeleri.
     * 
     * @var array<string,mixed>
     */
    protected array $attributes = [];

    /**
     * Constructor.
     * 
     * @param string $method HTTP metodu
     * @param string|UriInterface $uri URI
     * @param array<string,mixed> $headers Headers
     * @param string|resource|StreamInterface|null $body Body
     * @param string $version HTTP protokol versiyonu
     */
    public function __construct(
        string $method = 'GET',
        string|UriInterface $uri = '',
        array $headers = [],
        string|resource|StreamInterface|null $body = 'php://input',
        string $version = '1.1'
    ) {
        $this->method = strtoupper($method);
        $this->uri = is_string($uri) ? new Uri($uri) : $uri;
        $this->setHeaders($headers);
        $this->protocolVersion = $version;

        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        // Stream oluştur
        if ($body instanceof StreamInterface) {
            $this->body = $body;
        } elseif (is_resource($body)) {
            $this->body = new Stream($body);
        } else {
            $this->body = new Stream(is_string($body) ? $body : '');
        }

        $this->initRequestTime();
    }

    /**
     * Sunucu parametrelerinden yeni bir request oluşturur.
     * 
     * @param array<string,mixed> $server $_SERVER array
     * @param array<string,mixed> $query $_GET array
     * @param Stream $body $_POST array
     * @param array<string,mixed> $cookies $_COOKIE array
     * @param array<string,mixed> $files $_FILES array
     * @return static
     */
    public static function fromGlobals(
        array $server = [],
        array $query = [],
        array $body,
        array $cookies = [],
        array $files = []
    ): static {
        $server = $server ?: $_SERVER;
        $files = $files ?: $_FILES;
        $headers = static::prepareHeaders($server);

        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $uri = static::prepareUri($server);
        $body = new Stream('php://input');
        $protocol = isset($server['SERVER_PROTOCOL']) 
            ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) 
            : '1.1';

        $request = new static($method, $uri, $headers, $body, $protocol);

        return $request
            ->withCookieParams($cookies ?: $_COOKIE)
            ->withQueryParams($query ?: $_GET)
            ->withParsedBody($body ?: $_POST)
            ->withUploadedFiles(static::normalizeFiles($files));
    }

    /**
     * Sunucu parametrelerinden URI oluşturur.
     * 
     * @param array<string,mixed> $server $_SERVER array
     */
    protected static function prepareUri(array $server): UriInterface
    {
        $uri = new Uri();

        // Scheme
        $https = $server['HTTPS'] ?? '';
        $scheme = (empty($https) || $https === 'off') ? 'http' : 'https';
        $uri = $uri->withScheme($scheme);

        // Host ve port
        if (isset($server['HTTP_HOST'])) {
            $parts = explode(':', $server['HTTP_HOST']);
            $host = $parts[0];
            $port = $parts[1] ?? null;
        } else {
            $host = $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '';
            $port = $server['SERVER_PORT'] ?? null;
        }

        $uri = $uri->withHost($host);
        if ($port !== null) {
            $uri = $uri->withPort((int)$port);
        }

        // Path ve query
        $requestUri = $server['REQUEST_URI'] ?? '';
        $parts = explode('?', $requestUri);
        
        $path = $parts[0];
        $query = $parts[1] ?? '';

        return $uri
            ->withPath($path)
            ->withQuery($query);
    }

    /**
     * Sunucu parametrelerinden headerları hazırlar.
     * 
     * @param array<string,mixed> $server $_SERVER array
     * @return array<string,array<string>>
     */
    protected static function prepareHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            // Apache
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = [$value];
                continue;
            }

            // FastCGI
            if (str_starts_with($key, 'CONTENT_')) {
                $name = str_replace('_', '-', $key);
                $headers[$name] = [$value];
            }
        }

        return $headers;
    }

    /**
     * URI'den host header'ını günceller.
     */
    protected function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();
        if ($host === '') {
            return;
        }

        $port = $this->uri->getPort();
        if ($port !== null) {
            $host .= ':' . $port;
        }

        $this->headers['Host'] = [$host];
        $this->headerNames['host'] = 'Host';
    }

    /**
     * Header array'ini ayarlar.
     * 
     * @param array<string,mixed> $headers Headers
     */
    protected function setHeaders(array $headers): void
    {
        $this->headerNames = $this->headers = [];
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            $normalized = strtolower($header);
            $this->headerNames[$normalized] = $header;
            $this->headers[$header] = $value;
        }
    }

    // PSR-7 Message Interface implementasyonları

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return [];
        }

        $header = $this->headerNames[$normalized];
        return $this->headers[$header];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): static
    {
        $normalized = strtolower($name);
        $value = is_array($value) ? $value : [$value];

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;

        return $new;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $normalized = strtolower($name);
        $value = is_array($value) ? $value : [$value];

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $new->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name] = $value;
        }

        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $new = clone $this;
        unset(
            $new->headers[$this->headerNames[$normalized]],
            $new->headerNames[$normalized]
        );

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    // PSR-7 Request Interface implementasyonları

    public function getRequestTarget(): string
    {
        if (isset($this->requestTarget)) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }
        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(mixed $uri, bool $preserveHost = false): static
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            $new->updateHostFromUri();
        }

        return $new;
    }

    // PSR-7 ServerRequest Interface implementasyonları

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): static
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody(): null|array|object
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): static
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new InvalidArgumentException('Parsed body must be array, object or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, $value): static
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute(string $name): static
    {
        if (!isset($this->attributes[$name])) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    /**
     * Request'in benchmark sonuçlarını döndürür.
     * 
     * @return array{
     *  start: float,
     *  end: float,
     *  duration: float,
     *  memory: int,
     *  peak_memory: int
     * } Benchmark sonuçları
     */
    public function getBenchmark(): array
    {
        return [
            'start' => $this->requestTime,
            'end' => microtime(true),
            'duration' => $this->getElapsedTime(),
            'memory' => memory_get_usage(),
            'peak_memory' => memory_get_peak_usage()
        ];
    }
}