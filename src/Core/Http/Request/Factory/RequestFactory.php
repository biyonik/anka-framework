<?php

declare(strict_types=1);

namespace Framework\Core\Http\Request\Factory;

use Framework\Core\Http\Request\Request;
use Framework\Core\Http\Message\{Stream, Uri, UploadedFile};
use Psr\Http\Message\{
    RequestFactoryInterface,
    RequestInterface,
    UriInterface,
    StreamInterface,
    ServerRequestFactoryInterface,
    ServerRequestInterface
};

/**
 * Request nesnelerini oluşturmak için factory sınıfı.
 * 
 * Bu sınıf, PSR-17 ServerRequestFactoryInterface'ini implemente eder ve
 * framework'e özgü request oluşturma metodları sunar. Request nesnelerinin
 * tutarlı bir şekilde oluşturulmasını sağlar.
 * 
 * Özellikler:
 * - PSR-17 uyumlu request factory
 * - Superglobals'dan request oluşturma
 * - URI ve Stream desteği
 * - JSON request desteği
 * - Upload dosyaları yönetimi
 * 
 * @package Framework\Core\Http
 * @subpackage Request\Factory
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RequestFactory implements ServerRequestFactoryInterface, RequestFactoryInterface
{
    /**
     * Default HTTP headers.
     * 
     * @var array<string,string|string[]>
     */
    protected array $defaultHeaders = [];

    /**
     * Constructor.
     * 
     * @param array<string,string|string[]> $defaultHeaders Default HTTP headers
     */
    public function __construct(array $defaultHeaders = [])
    {
        $this->defaultHeaders = $defaultHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request(
            $method,
            $uri instanceof UriInterface ? $uri : new Uri($uri),
            $this->defaultHeaders
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        $uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $request = new Request($method, $uri, $this->defaultHeaders);

        return $request
            ->withServerParams($serverParams)
            ->withUploadedFiles([])
            ->withParsedBody(null)
            ->withQueryParams([])
            ->withCookieParams([]);
    }

    /**
     * Superglobals'dan yeni bir request oluşturur.
     * 
     * @param array<string,mixed> $server $_SERVER array
     * @param array<string,mixed> $get $_GET array
     * @param array<string,mixed> $post $_POST array
     * @param array<string,mixed> $cookies $_COOKIE array
     * @param array<string,mixed> $files $_FILES array
     * @return ServerRequestInterface
     */
    public function createFromGlobals(
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookies = [],
        array $files = []
    ): ServerRequestInterface {
        $server = $server ?: $_SERVER;
        $get = $get ?: $_GET;
        $post = $post ?: $_POST;
        $cookies = $cookies ?: $_COOKIE;
        $files = $files ?: $_FILES;

        // Base URI oluştur
        $uri = $this->createUriFromGlobals($server);

        // Method belirle
        $method = $server['REQUEST_METHOD'] ?? 'GET';

        // Request oluştur
        $request = $this->createServerRequest($method, $uri, $server);

        // Headers ekle
        foreach ($this->getHeadersFromServer($server) as $name => $values) {
            $request = $request->withHeader($name, $values);
        }

        // Body stream'i ayarla
        $body = new Stream('php://input');
        $request = $request->withBody($body);

        // Parametreleri ekle
        return $request
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withCookieParams($cookies)
            ->withUploadedFiles($this->normalizeFiles($files));
    }

    /**
     * JSON request oluşturur.
     * 
     * @param string $method HTTP method
     * @param string|UriInterface $uri Request URI
     * @param mixed $data JSON data
     * @param array<string,string|string[]> $headers Extra headers
     * @return ServerRequestInterface
     */
    public function createJsonRequest(
        string $method,
        string|UriInterface $uri,
        mixed $data,
        array $headers = []
    ): ServerRequestInterface {
        $json = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON data: ' . json_last_error_msg());
        }

        $stream = new Stream('php://memory', 'wb+');
        $stream->write($json);
        $stream->rewind();

        $headers = array_merge(
            ['Content-Type' => 'application/json'],
            $this->defaultHeaders,
            $headers
        );

        return $this->createServerRequest($method, $uri)
            ->withBody($stream)
            ->withParsedBody($data)
            ->withHeaders($headers);
    }

    /**
     * Sunucu parametrelerinden URI oluşturur.
     * 
     * @param array<string,mixed> $server Server parametreleri
     * @return UriInterface
     */
    protected function createUriFromGlobals(array $server): UriInterface
    {
        $uri = new Uri();

        // Scheme
        $https = $server['HTTPS'] ?? '';
        $scheme = (empty($https) || $https === 'off') ? 'http' : 'https';
        $uri = $uri->withScheme($scheme);

        // Host ve port
        if (isset($server['HTTP_HOST'])) {
            $hostHeaderParts = explode(':', $server['HTTP_HOST']);
            $host = $hostHeaderParts[0];
            $port = $hostHeaderParts[1] ?? null;
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
        $uriParts = explode('?', $requestUri);
        
        $path = $uriParts[0];
        $query = $uriParts[1] ?? '';

        return $uri
            ->withPath($path)
            ->withQuery($query);
    }

    /**
     * Server parametrelerinden headerları alır.
     * 
     * @param array<string,mixed> $server Server parametreleri
     * @return array<string,array<string>>
     */
    protected function getHeadersFromServer(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = (array) $value;
            } elseif (str_starts_with($key, 'CONTENT_')) {
                $name = str_replace('_', '-', $key);
                $headers[$name] = (array) $value;
            }
        }

        return $headers;
    }

    /**
     * Yüklenen dosyaları normalize eder.
     * 
     * @param array<string,mixed> $files FILES array
     * @return array<string,mixed>
     */
    protected function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($this->isUploadedFile($value)) {
                $normalized[$key] = $this->createUploadedFile($value);
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
            }
        }

        return $normalized;
    }

    /**
     * Array'in yüklenmiş dosya olup olmadığını kontrol eder.
     * 
     * @param mixed $file Kontrol edilecek array
     * @return bool
     */
    protected function isUploadedFile(mixed $file): bool
    {
        if (!is_array($file)) {
            return false;
        }

        $keys = ['tmp_name', 'name', 'type', 'size', 'error'];
        return count(array_intersect_key(array_flip($keys), $file)) === count($keys);
    }

    /**
     * Yüklenmiş dosya oluşturur.
     * 
     * @param array<string,mixed> $file Dosya bilgileri
     * @return mixed
     */
    protected function createUploadedFile(array $file): mixed
    {
        if (is_array($file['tmp_name'])) {
            return $this->normalizeFiles($file);
        }

        return new UploadedFile(
            $file['tmp_name'],
            (int) $file['size'],
            (int) $file['error'],
            $file['name'],
            $file['type']
        );
    }
}