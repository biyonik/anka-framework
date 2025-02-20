<?php

declare(strict_types=1);

namespace Framework\Core\Http\Response\Factory;

use Framework\Core\Http\Response\Response;
use Framework\Core\Http\Message\Stream;
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, StreamInterface};

/**
 * Response nesnelerini oluşturmak için factory sınıfı.
 * 
 * Bu sınıf, PSR-17 ResponseFactoryInterface'ini implemente eder ve
 * framework'e özgü response oluşturma metodları sunar. Response nesnelerinin
 * tutarlı bir şekilde oluşturulmasını sağlar.
 * 
 * Özellikler:
 * - PSR-17 uyumlu response factory
 * - Özelleştirilmiş response tipleri (JSON, File, Download)
 * - Stream desteği
 * - Default header ve cookie yönetimi
 * 
 * @package Framework\Core\Http
 * @subpackage Response\Factory
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Default HTTP headers.
     * 
     * @var array<string,string|string[]>
     */
    protected array $defaultHeaders = [];

    /**
     * Default cookies.
     * 
     * @var array<string,array{value:string,options:array<string,mixed>}>
     */
    protected array $defaultCookies = [];

    /**
     * Constructor.
     * 
     * @param array<string,string|string[]> $defaultHeaders Default HTTP headers
     * @param array<string,array{value:string,options:array<string,mixed>}> $defaultCookies Default cookies
     */
    public function __construct(array $defaultHeaders = [], array $defaultCookies = [])
    {
        $this->defaultHeaders = $defaultHeaders;
        $this->defaultCookies = $defaultCookies;
    }

    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new Response($code, $this->defaultHeaders, null, '1.1', $reasonPhrase);

        // Default cookie'leri ekle
        foreach ($this->defaultCookies as $name => $cookie) {
            $response = $response->withCookie($name, $cookie['value'], $cookie['options']);
        }

        return $response;
    }

    /**
     * JSON response oluşturur.
     * 
     * @param mixed $data JSON'a çevrilecek veri
     * @param int $status HTTP status kodu
     * @param array<string,string|string[]> $headers Extra headers
     * @return ResponseInterface
     */
    public function createJsonResponse(mixed $data, int $status = 200, array $headers = []): ResponseInterface
    {
        $headers = array_merge($this->defaultHeaders, $headers);
        return Response::json($data, $status, $headers);
    }

    /**
     * Redirect response oluşturur.
     * 
     * @param string $url Hedef URL
     * @param int $status HTTP status kodu
     * @param array<string,string|string[]> $headers Extra headers
     * @return ResponseInterface
     */
    public function createRedirectResponse(string $url, int $status = 302, array $headers = []): ResponseInterface
    {
        $headers = array_merge($this->defaultHeaders, $headers);
        return Response::redirect($url, $status, $headers);
    }

    /**
     * File response oluşturur.
     * 
     * @param string $path Dosya yolu
     * @param string|null $name İndirme ismi
     * @param string|null $contentType Content type
     * @param array<string,string|string[]> $headers Extra headers
     * @return ResponseInterface
     */
    public function createFileResponse(
        string $path,
        ?string $name = null,
        ?string $contentType = null,
        array $headers = []
    ): ResponseInterface {
        $headers = array_merge($this->defaultHeaders, $headers);
        return Response::file($path, $name, $contentType, $headers);
    }

    /**
     * Download response oluşturur.
     * 
     * @param string $path Dosya yolu
     * @param string|null $name İndirme ismi
     * @param string|null $contentType Content type
     * @param array<string,string|string[]> $headers Extra headers
     * @return ResponseInterface
     */
    public function createDownloadResponse(
        string $path,
        ?string $name = null,
        ?string $contentType = null,
        array $headers = []
    ): ResponseInterface {
        $headers = array_merge($this->defaultHeaders, $headers);
        return Response::download($path, $name, $contentType, $headers);
    }

    /**
     * HTML response oluşturur.
     * 
     * @param string $html HTML içeriği
     * @param int $status HTTP status kodu
     * @param array<string,string|string[]> $headers Extra headers
     * @return ResponseInterface
     */
    public function createHtmlResponse(string $html, int $status = 200, array $headers = []): ResponseInterface
    {
        $headers = array_merge(
            ['Content-Type' => 'text/html; charset=utf-8'],
            $this->defaultHeaders,
            $headers
        );

        return $this->createResponse($status)
            ->withHeaders($headers)
            ->withBody(new Stream($html));
    }

    /**
     * Empty response oluşturur (204 No Content).
     * 
     * @param array<string,string|string[]> $headers Extra headers
     * @return ResponseInterface
     */
    public function createEmptyResponse(array $headers = []): ResponseInterface
    {
        $headers = array_merge($this->defaultHeaders, $headers);
        return $this->createResponse(204, '', $headers);
    }

    /**
     * Default header ekler.
     * 
     * @param string $name Header adı
     * @param string|string[] $value Header değeri
     * @return static
     */
    public function withDefaultHeader(string $name, string|array $value): static
    {
        $new = clone $this;
        $new->defaultHeaders[$name] = $value;
        return $new;
    }

    /**
     * Default cookie ekler.
     * 
     * @param string $name Cookie adı
     * @param string $value Cookie değeri
     * @param array<string,mixed> $options Cookie options
     * @return static
     */
    public function withDefaultCookie(string $name, string $value, array $options = []): static
    {
        $new = clone $this;
        $new->defaultCookies[$name] = [
            'value' => $value,
            'options' => $options
        ];
        return $new;
    }
}