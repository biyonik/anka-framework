<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Contracts;

/**
 * Konfigürasyon yükleyici arayüzü.
 *
 * Bu arayüz, çeşitli kaynaklardan konfigürasyon verilerinin yüklenmesini sağlar.
 *
 * @package Framework\Core\Configuration
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ConfigLoaderInterface
{
    /**
     * Bir dosyadan konfigürasyon verilerini yükler.
     *
     * @param string $path Konfigürasyon dosyasının yolu
     * @return array<string, mixed> Konfigürasyon verileri
     *
     * @throws \InvalidArgumentException Dosya bulunamazsa veya işlenemezse
     */
    public function loadFromFile(string $path): array;

    /**
     * Bir dizindeki konfigürasyon dosyalarını yükler.
     *
     * @param string $directory Konfigürasyon dosyalarının bulunduğu dizin
     * @param string $extension Dosya uzantısı (php, json, yaml, vb.)
     * @return array<string, mixed> Konfigürasyon verileri
     *
     * @throws \InvalidArgumentException Dizin bulunamazsa veya erişilemezse
     */
    public function loadFromDirectory(string $directory, string $extension = 'php'): array;

    /**
     * Bir diziden konfigürasyon verilerini yükler.
     *
     * @param array<string, mixed> $data Konfigürasyon verileri
     * @return array<string, mixed> İşlenmiş konfigürasyon verileri
     */
    public function loadFromArray(array $data): array;

    /**
     * Desteklenen dosya formatlarını döndürür.
     *
     * @return array<string> Desteklenen dosya uzantıları
     */
    public function getSupportedExtensions(): array;
}