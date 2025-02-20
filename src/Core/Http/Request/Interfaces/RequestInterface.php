<?php

declare(strict_types=1);

namespace Framework\Core\Http\Request\Interfaces;

use Psr\Http\Message\ServerRequestInterface;
use Framework\Core\Http\Message\Uri;

/**
 * HTTP Request'lerinin davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz PSR-7 ServerRequestInterface'ini extend eder ve framework'e özgü
 * ekstra metodları tanımlar. Request'in yaşam döngüsü boyunca ihtiyaç duyulan
 * tüm davranışları içerir.
 * 
 * Özellikler:
 * - HTTP metod yönetimi
 * - Header yönetimi
 * - Query string, POST data ve dosya yönetimi
 * - Session ve Cookie yönetimi
 * - İstek validasyonu
 * - AJAX/JSON istek tespiti
 * 
 * @package Framework\Core\Http
 * @subpackage Request
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface RequestInterface extends ServerRequestInterface
{
    /**
     * Request'in AJAX isteği olup olmadığını kontrol eder.
     */
    public function isXhr(): bool;

    /**
     * Request'in JSON içeriği olup olmadığını kontrol eder.
     */
    public function isJson(): bool;

    /**
     * Request'in HTTPS üzerinden gelip gelmediğini kontrol eder.
     */
    public function isSecure(): bool;

    /**
     * Request'in belirtilen metodla yapılıp yapılmadığını kontrol eder.
     * 
     * @param string $method HTTP metodu
     */
    public function isMethod(string $method): bool;

    /**
     * Request'in IP adresini döndürür.
     * 
     * @param bool $checkProxy Proxy headerlarını kontrol etmek için
     */
    public function getIp(bool $checkProxy = true): string;

    /**
     * Request'in başlangıç zamanını döndürür.
     * 
     * @return float UNIX timestamp with microseconds
     */
    public function getRequestTime(): float;

    /**
     * Query string parametresini döndürür.
     * 
     * @param string $key Parametre adı
     * @param mixed $default Varsayılan değer
     * @return mixed Parametre değeri
     */
    public function query(string $key, mixed $default = null): mixed;

    /**
     * POST parametresini döndürür.
     * 
     * @param string $key Parametre adı
     * @param mixed $default Varsayılan değer
     * @return mixed Parametre değeri
     */
    public function post(string $key, mixed $default = null): mixed;

    /**
     * Input parametresini döndürür (POST, PUT, PATCH data).
     * 
     * @param string $key Parametre adı
     * @param mixed $default Varsayılan değer
     * @return mixed Parametre değeri
     */
    public function input(string $key, mixed $default = null): mixed;

    /**
     * JSON input parametresini döndürür.
     * 
     * @param string $key Parametre adı
     * @param mixed $default Varsayılan değer
     * @return mixed Parametre değeri
     */
    public function json(string $key, mixed $default = null): mixed;

    /**
     * Request header'ını döndürür.
     * 
     * @param string $key Header adı
     * @param mixed $default Varsayılan değer
     * @return mixed Header değeri
     */
    public function header(string $key, mixed $default = null): mixed;

    /**
     * Cookie değerini döndürür.
     * 
     * @param string $key Cookie adı
     * @param mixed $default Varsayılan değer
     * @return mixed Cookie değeri
     */
    public function cookie(string $key, mixed $default = null): mixed;

    /**
     * Yüklenen dosyayı döndürür.
     * 
     * @param string $key Dosya adı
     * @return mixed Dosya
     */
    public function file(string $key): mixed;

    /**
     * Request URL'ini döndürür.
     * 
     * @param bool $withQuery Query string dahil edilsin mi
     */
    public function url(bool $withQuery = true): string;

    /**
     * Tam Request URL'ini döndürür (scheme ve host dahil).
     * 
     * @param bool $withQuery Query string dahil edilsin mi
     */
    public function fullUrl(bool $withQuery = true): string;

    /**
     * Request path'ini döndürür.
     */
    public function path(): string;

    /**
     * Kullanıcı ajanını (User Agent) döndürür.
     */
    public function userAgent(): ?string;

    /**
     * Referer URL'ini döndürür.
     */
    public function referer(): ?string;

    /**
     * Kabul edilen content type'ları döndürür.
     * 
     * @return array<string> Content type listesi
     */
    public function accepts(): array;

    /**
     * Belirtilen content type'ın kabul edilip edilmediğini kontrol eder.
     * 
     * @param string $contentType Content type
     */
    public function accepts_json(): bool;

    /**
     * Request başlangıcından bu yana geçen süreyi döndürür.
     */
    public function getElapsedTime(): float;

    /**
     * Request'i yeni bir URI ile klonlar.
     * 
     * @param string|Uri $uri Yeni URI
     */
    public function withUri(mixed $uri, bool $preserveHost = false): static;

    /**
     * Request'in route parametrelerini döndürür.
     * 
     * @return array<string,mixed> Route parametreleri
     */
    public function getRouteParams(): array;

    /**
     * Route parametresini ayarlar.
     * 
     * @param string $name Parametre adı
     * @param mixed $value Parametre değeri
     */
    public function setRouteParam(string $name, mixed $value): static;
}