<?php

declare(strict_types=1);

namespace Framework\Core\Http\Response;

use Framework\Core\Http\Response\Interfaces\ResponseInterface;
use Framework\Core\Http\Response\Traits\ResponseTrait;
use Framework\Core\Http\Message\Stream;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * HTTP Response'larının temel implementasyonu.
 * 
 * Bu sınıf, framework'ün HTTP response'larını temsil eder.
 * PSR-7 ResponseInterface ve kendi ResponseInterface'imizi implemente eder.
 * İmmutable (değişmez) bir yapı sunar.
 * 
 * Özellikler:
 * - PSR-7 uyumlu response
 * - JSON response desteği
 * - File download desteği
 * - Redirect yönetimi
 * - Cookie yönetimi
 * - Cache control
 * - CORS desteği
 * 
 * @package Framework\Core\Http
 * @subpackage Response
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Response implements ResponseInterface
{
    use ResponseTrait;

    /**
     * HTTP protokol versiyonu.
     */
    protected string $protocolVersion = '1.1';

    /**
     * HTTP status kodu.
     */
    protected int $statusCode = 200;

    /**
     * HTTP status açıklaması.
     */
    protected string $reasonPhrase = '';

    /**
     * HTTP headers.
     * 
     * @var array<string,string[]>
     */
    protected array $headers = [];

    /**
     * Response body'si.
     */
    protected StreamInterface $body;

    /**
     * Constructor.
     * 
     * @param int $status HTTP status kodu
     * @param array<string,string|string[]> $headers HTTP headers
     * @param string|resource|StreamInterface|null $body Response body'si
     * @param string $version HTTP protokol versiyonu
     * @param string|null $reason Status açıklaması
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        string|resource|StreamInterface|null $body = 'php://memory',
        string $version = '1.1',
        ?string $reason = null
    ) {
        $this->statusCode = $status;
        $this->protocolVersion = $version;

        // Status açıklamasını ayarla
        $this->reasonPhrase = $reason ?? static::getPhrase($status);

        // Stream oluştur
        if ($body instanceof StreamInterface) {
            $this->body = $body;
        } elseif (is_resource($body)) {
            $this->body = new Stream($body);
        } else {
            $stream = new Stream('php://memory', 'wb+');
            if ($body !== null) {
                $stream->write($body);
                $stream->rewind();
            }
            $this->body = $stream;
        }

        // Headers'ı ayarla
        foreach ($headers as $name => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->headers[$name] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion(string $version): static
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name): array
    {
        $name = strtolower($name);
        if (!isset($this->headers[$name])) {
            return [];
        }

        return $this->headers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader(string $name, $value): static
    {
        $normalized = strtolower($name);
        $value = is_array($value) ? $value : [$value];

        $new = clone $this;
        $new->headers[$normalized] = $value;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader(string $name, $value): static
    {
        $normalized = strtolower($name);
        $value = is_array($value) ? $value : [$value];

        $new = clone $this;
        if (isset($new->headers[$normalized])) {
            $new->headers[$normalized] = array_merge($new->headers[$normalized], $value);
        } else {
            $new->headers[$normalized] = $value;
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name): static
    {
        $normalized = strtolower($name);
        if (!isset($this->headers[$normalized])) {
            return $this;
        }

        $new = clone $this;
        unset($new->headers[$normalized]);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): static
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        if ($this->statusCode === $code && $this->reasonPhrase === $reasonPhrase) {
            return $this;
        }

        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase ?: static::getPhrase($code);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Yeni bir JSON response oluşturur.
     * 
     * @param mixed $data JSON'a çevrilecek veri
     * @param int $status HTTP status kodu
     * @param array<string,string|string[]> $headers Extra headers
     * @return static
     */
    public static function json(mixed $data, int $status = 200, array $headers = []): static
    {
        return (new static($status, $headers))->withJson($data);
    }

    /**
     * Yeni bir redirect response oluşturur.
     * 
     * @param string $url Hedef URL
     * @param int $status HTTP status kodu
     * @param array<string,string|string[]> $headers Extra headers
     * @return static
     */
    public static function redirect(string $url, int $status = 302, array $headers = []): static
    {
        return (new static($status, $headers))->withRedirect($url);
    }

    /**
     * Yeni bir file response oluşturur.
     * 
     * @param string $path Dosya yolu
     * @param string|null $name İndirme ismi
     * @param string|null $contentType Content type
     * @param array<string,string|string[]> $headers Extra headers
     * @return static
     */
    public static function file(
        string $path,
        ?string $name = null,
        ?string $contentType = null,
        array $headers = []
    ): static {
        return (new static(200, $headers))->withFile($path, $name, $contentType);
    }

    /**
     * Yeni bir download response oluşturur.
     * 
     * @param string $path Dosya yolu
     * @param string|null $name İndirme ismi
     * @param string|null $contentType Content type
     * @param array<string,string|string[]> $headers Extra headers
     * @return static
     */
    public static function download(
        string $path,
        ?string $name = null,
        ?string $contentType = null,
        array $headers = []
    ): static {
        return (new static(200, $headers))->withDownload($path, $name, $contentType);
    }
}