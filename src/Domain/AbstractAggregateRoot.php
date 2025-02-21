<?php

declare(strict_types=1);

namespace Framework\Domain;

use Framework\Domain\Contracts\AggregateRootInterface;
use Framework\Domain\Contracts\DomainEventInterface;

/**
 * Soyut Aggregate Root sınıfı.
 *
 * Aggregate root'lar için temel implementasyon sağlayan soyut sınıf.
 *
 * @package Framework\Domain
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractAggregateRoot extends AbstractEntity implements AggregateRootInterface
{
    /**
     * Kaydedilmiş domain event'ler.
     *
     * @var array<DomainEventInterface>
     */
    protected array $events = [];

    /**
     * Aggregate'in versiyon numarası.
     * Her değişiklikte artar.
     *
     * @var int
     */
    protected int $version = 0;

    /**
     * {@inheritdoc}
     */
    public function recordEvent(DomainEventInterface $event): self
    {
        $this->events[] = $event;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function hasEvents(): bool
    {
        return !empty($this->events);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function setVersion(int $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function applyEvents(array $events): self
    {
        foreach ($events as $event) {
            $this->applyEvent($event);
            $this->version++;
        }

        return $this;
    }

    /**
     * Tek bir domain event'i uygular.
     * Alt sınıflar bu metodu override etmelidir.
     *
     * @param DomainEventInterface $event Uygulanacak domain event
     * @return self Akıcı arayüz için
     */
    abstract protected function applyEvent(DomainEventInterface $event): self;
}