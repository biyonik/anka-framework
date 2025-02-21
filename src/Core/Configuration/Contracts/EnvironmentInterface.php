<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Contracts;

/**
 * Çevre (environment) yönetim arayüzü.
 *
 * Bu arayüz, uygulama çalışma ortamı (development, production, testing)
 * bilgilerinin yönetilmesini sağlar.
 *
 * @package Framework\Core\Configuration
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface EnvironmentInterface
{
    /**
     * Mevcut çevre adını döndürür.
     *
     * @return string Çevre adı
     */
    public function getEnvironment(): string;

    /**
     * Çevre değişkenini döndürür.
     *
     * @param string $key Çevre değişkeni adı
     * @param mixed $default Varsayılan değer
     * @return mixed Çevre değişkeni değeri
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Çevre değişkenini ayarlar.
     *
     * @param string $key Çevre değişkeni adı
     * @param mixed $value Değer
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Belirtilen çevre değişkeninin var olup olmadığını kontrol eder.
     *
     * @param string $key Çevre değişkeni adı
     * @return bool Çevre değişkeni varsa true
     */
    public function has(string $key): bool;

    /**
     * Mevcut çevrenin belirtilen çevre ile eşleşip eşleşmediğini kontrol eder.
     *
     * @param string|array<string> $environment Kontrol edilecek çevre(ler)
     * @return bool Eşleşiyorsa true
     */
    public function is(string|array $environment): bool;

    /**
     * Çevre değişkenlerini belirtilen kaynaktan yükler.
     *
     * @param string $path .env dosyasının yolu
     * @return void
     */
    public function load(string $path): void;
}