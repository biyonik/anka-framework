<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Value Object arayüzü.
 *
 * Value object'lerin uygulaması gereken temel arayüz.
 * Value object'ler, kimliği olmayan ve değerleri ile tanımlanan domain nesneleridir.
 * Value object'ler immutable (değiştirilemez) olmalıdır.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ValueObjectInterface
{
    /**
     * Value object'in eşitliğini kontrol eder.
     * İki value object, aynı sınıftan ve aynı değerlere sahipse eşittir.
     *
     * @param ValueObjectInterface $valueObject Karşılaştırılacak value object
     * @return bool Eşitlik durumu
     */
    public function equals(ValueObjectInterface $valueObject): bool;

    /**
     * Value object'in hash'ini döndürür.
     * Hash, value object'in string temsiline dayalı olarak oluşturulur.
     *
     * @return string Hash
     */
    public function hash(): string;

    /**
     * Value object'in değerlerini bir array olarak döndürür.
     *
     * @return array<string, mixed> Değerler
     */
    public function toArray(): array;
}