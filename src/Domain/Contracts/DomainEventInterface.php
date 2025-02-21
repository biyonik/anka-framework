<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Domain Event arayüzü.
 *
 * Domain event'lerin uygulaması gereken temel arayüz.
 * Domain event'ler, domain'de gerçekleşen olayları temsil eder.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface DomainEventInterface
{
    /**
     * Event'in benzersiz tipini döndürür.
     *
     * @return string Event tipi
     */
    public function getType(): string;

    /**
     * Event'in gerçekleştiği zamanı döndürür.
     *
     * @return \DateTimeImmutable Event zamanı
     */
    public function getOccurredAt(): \DateTimeImmutable;

    /**
     * Event'in aggregate ID'sini döndürür.
     *
     * @return mixed Aggregate ID
     */
    public function getAggregateId(): mixed;

    /**
     * Event'in aggregate tipini döndürür.
     *
     * @return string Aggregate tipi
     */
    public function getAggregateType(): string;

    /**
     * Event verilerini döndürür.
     *
     * @return array<string, mixed> Event verileri
     */
    public function getData(): array;

    /**
     * Event'in seri hale getirilmiş temsilini döndürür.
     *
     * @return string Serileştirilmiş temsil
     */
    public function serialize(): string;

    /**
     * Serileştirilmiş bir temsilden event oluşturur.
     *
     * @param string $serialized Serileştirilmiş temsil
     * @return self Event örneği
     */
    public static function deserialize(string $serialized): self;
}