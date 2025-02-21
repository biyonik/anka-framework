<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Contracts;

/**
 * Command arayüzü.
 *
 * Bu arayüz, tüm Command nesnelerinin uygulaması gereken temel metotları tanımlar.
 * Command nesneleri, sistemde bir değişiklik yaratmak için kullanılır (yazma işlemleri).
 *
 * @package Framework\Core\CQRS
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface CommandInterface
{
    /**
     * Command tipini döndürür.
     * Bu, Command'in benzersiz tanımlayıcısıdır.
     *
     * @return string Command tipi
     */
    public function getType(): string;

    /**
     * Command'in verilerini döndürür.
     *
     * @return array<string, mixed> Command verileri
     */
    public function toArray(): array;

    /**
     * Command'in validation kurallarını döndürür.
     *
     * @return array<string, string|array> Validation kuralları
     */
    public function validationRules(): array;

    /**
     * Command'in tekil tanımlayıcısını döndürür.
     * İdempotent komutlarda faydalıdır.
     *
     * @return string|null Command ID'si
     */
    public function getCommandId(): ?string;
}