<?php

declare(strict_types=1);

namespace Framework\Core\Http\Response\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * HTTP Response'larının davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz PSR-7 ResponseInterface'ini extend eder ve framework'e özgü
 * ek metodları tanımlar. Response'un yaşam döngüsü boyunca ihtiyaç duyulan
 * tüm davranışları içerir.
 * 
 * Özellikler:
 * - Status code ve reason phrase yönetimi
 * - Header yönetimi
 * - Body yönetimi
 * - JSON response desteği
 * - File download desteği
 * - Redirect yönetimi
 * - Cookie yönetimi
 * 
 * @package Framework\Core\Http
 * @subpackage Response
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ResponseInterface extends PsrResponseInterface
{
    /**
     * Response'a JSON içeriği ekler.
     * 
     * @param mixed $data JSON'a çevrilecek veri
     * @param int $status HTTP status kodu
     * @param int $flags JSON encode flags
     * @param int $depth Maximum depth
     * @return static
     */
    public function withJson(mixed $data, int $status = 200, int $flags = 0, int $depth = 512): static;

    /**
     * Response'a dosya içeriği ekler.
     * 
     * @param string $path Dosya yolu
     * @param string|null $name İndirme ismi
     * @param string|null $contentType Content type
     * @return static
     */
    public function withFile(string $path, ?string $name = null, ?string $contentType = null): static;

    /**
     * Response'a indirilebilir dosya içeriği ekler.
     * 
     * @param string $path Dosya yolu
     * @param string|null $name İndirme ismi
     * @param string|null $contentType Content type
     * @return static
     */
    public function withDownload(string $path, ?string $name = null, ?string $contentType = null): static;

    /**
     * Yönlendirme response'u oluşturur.
     * 
     * @param string $url Hedef URL
     * @param int $status HTTP status kodu
     * @return static
     */
    public function withRedirect(string $url, int $status = 302): static;

    /**
     * Response'a cookie ekler.
     * 
     * @param string $name Cookie ismi
     * @param string $value Cookie değeri
     * @param array<string,mixed> $options Cookie options
     * @return static
     */
    public function withCookie(string $name, string $value, array $options = []): static;

    /**
     * Response'dan cookie siler.
     * 
     * @param string $name Cookie ismi
     * @param array<string,mixed> $options Cookie options
     * @return static
     */
    public function withoutCookie(string $name, array $options = []): static;

    /**
     * Response'a cache control header'ı ekler.
     * 
     * @param int|string $value Cache değeri (saniye veya direktif)
     * @return static
     */
    public function withCache(int|string $value): static;

    /**
     * Response'u cache'lenemez yapar.
     * 
     * @return static
     */
    public function withNoCache(): static;

    /**
     * Response'a CORS header'ları ekler.
     * 
     * @param array<string,string|array<string>> $options CORS options
     * @return static
     */
    public function withCors(array $options = []): static;

    /**
     * Response'u gönderir.
     * 
     * @return void
     */
    public function send(): void;

    /**
     * Response'un gönderilip gönderilmediğini kontrol eder.
     * 
     * @return bool
     */
    public function isSent(): bool;
}