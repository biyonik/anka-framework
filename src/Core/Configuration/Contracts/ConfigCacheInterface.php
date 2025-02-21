<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Contracts;

/**
 * Konfigürasyon önbellekleme arayüzü.
 *
 * Bu arayüz, konfigürasyon verilerinin önbelleğe alınması ve
 * önbellekten yüklenmesi işlemlerini sağlar.
 *
 * @package Framework\Core\Configuration
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ConfigCacheInterface
{
    /**
     * Konfigürasyon verilerini önbelleğe kaydeder.
     *
     * @param array<string, mixed> $config Konfigürasyon verileri
     * @return bool İşlem başarılıysa true
     */
    public function cache(array $config): bool;

    /**
     * Önbellekteki konfigürasyon verilerini yükler.
     *
     * @return array<string, mixed>|null Konfigürasyon verileri veya null (önbellek yoksa)
     */
    public function load(): ?array;

    /**
     * Konfigürasyon önbelleğinin geçerli olup olmadığını kontrol eder.
     *
     * @return bool Önbellek geçerliyse true
     */
    public function isValid(): bool;

    /**
     * Konfigürasyon önbelleğini temizler.
     *
     * @return bool İşlem başarılıysa true
     */
    public function clear(): bool;
}