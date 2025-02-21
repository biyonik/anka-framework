<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Aggregate Root arayüzü.
 *
 * Aggregate root'ların uygulaması gereken temel arayüz.
 * Aggregate root, birbirine bağlı entity ve value object'lerden oluşan bir kümenin giriş noktasıdır.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AggregateRootInterface extends EntityInterface
{
    /**
     * Domain event'i kaydeder.
     *
     * @param DomainEventInterface $event Kaydedilecek domain event
     * @return self Akıcı arayüz için
     */
    public function recordEvent(DomainEventInterface $event): self;

    /**
     * Kaydedilmiş tüm domain event'leri döndürür ve temizler.
     *
     * @return array<DomainEventInterface> Domain event'ler
     */
    public function releaseEvents(): array;

    /**
     * Kaydedilmiş domain event'lerin olup olmadığını kontrol eder.
     *
     * @return bool Domain event varsa true
     */
    public function hasEvents(): bool;

    /**
     * Aggregate'in versiyon numarasını döndürür.
     *
     * @return int Versiyon numarası
     */
    public function getVersion(): int;

    /**
     * Aggregate'in versiyon numarasını ayarlar.
     *
     * @param int $version Versiyon numarası
     * @return self Akıcı arayüz için
     */
    public function setVersion(int $version): self;

    /**
     * Domain event'leri uygular.
     *
     * @param array<DomainEventInterface> $events Uygulanacak domain event'ler
     * @return self Akıcı arayüz için
     */
    public function applyEvents(array $events): self;
}