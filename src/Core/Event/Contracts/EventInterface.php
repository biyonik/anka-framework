<?php

declare(strict_types=1);

namespace Framework\Core\Event\Contracts;

/**
 * Event arayüzü.
 *
 * Bu arayüz, tüm olay nesnelerinin uygulaması gereken metotları tanımlar.
 *
 * @package Framework\Core\Event
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface EventInterface
{
    /**
     * Olayın adını döndürür.
     *
     * @return string Olay adı
     */
    public function getName(): string;

    /**
     * Olayın gerçekleştiği zamanı döndürür.
     *
     * @return \DateTimeImmutable Olay zamanı
     */
    public function getTimestamp(): \DateTimeImmutable;

    /**
     * Olayın verilerini döndürür.
     *
     * @return array<string, mixed> Olay verileri
     */
    public function getData(): array;

    /**
     * Belirtilen anahtarla ilişkilendirilmiş veriyi döndürür.
     *
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed İlgili veri
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Olayın belirli bir veri anahtarına sahip olup olmadığını kontrol eder.
     *
     * @param string $key Kontrol edilecek anahtar
     * @return bool Anahtar varsa true
     */
    public function has(string $key): bool;
}