<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Contracts;

/**
 * Framework'ün konfigürasyon yönetim sistemi için temel arayüz.
 * 
 * Bu arayüz, uygulama genelinde konfigürasyon değerlerinin yönetimini sağlar.
 * Hiyerarşik konfigürasyon yapısı, çevre bazlı değer yönetimi ve tip güvenliği sunar.
 * 
 * @package Framework\Core\Configuration
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ConfigurationInterface
{
    /**
     * Belirtilen anahtara sahip konfigürasyon değerini döndürür.
     * 
     * Nokta notasyonu ile nested değerlere erişim sağlar:
     * - database.host
     * - app.name
     * - services.cache.ttl
     * 
     * @param string $key Konfigürasyon anahtarı
     * @param mixed $default Değer bulunamazsa dönecek varsayılan değer
     * @return mixed Konfigürasyon değeri
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Bir konfigürasyon değerini ayarlar.
     * 
     * @param string $key Konfigürasyon anahtarı
     * @param mixed $value Konfigürasyon değeri
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Belirtilen anahtarın var olup olmadığını kontrol eder.
     * 
     * @param string $key Kontrol edilecek anahtar
     * @return bool Anahtar varsa true
     */
    public function has(string $key): bool;

    /**
     * Konfigürasyon değerlerini yükler.
     * 
     * @param array<string,mixed> $configuration Yüklenecek konfigürasyon array'i
     * @param bool $merge Mevcut değerlerle birleştirilip birleştirilmeyeceği
     * @return void
     */
    public function load(array $configuration, bool $merge = true): void;

    /**
     * Tüm konfigürasyon değerlerini döndürür.
     * 
     * @return array<string,mixed> Konfigürasyon değerleri
     */
    public function all(): array;
}