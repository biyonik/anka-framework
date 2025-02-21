<?php

declare(strict_types=1);

namespace Framework\Domain;

use Framework\Domain\Contracts\DomainEventInterface;

/**
 * Soyut Domain Event sınıfı.
 *
 * Domain event'ler için temel implementasyon sağlayan soyut sınıf.
 *
 * @package Framework\Domain
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractDomainEvent implements DomainEventInterface
{
    /**
     * Event'in gerçekleştiği zaman.
     *
     * @var \DateTimeImmutable
     */
    protected \DateTimeImmutable $occurredAt;

    /**
     * Event'in verileri.
     *
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * Constructor.
     *
     * @param mixed $aggregateId Aggregate ID
     * @param array<string, mixed> $data Event verileri
     * @param \DateTimeImmutable|null $occurredAt Event zamanı (null ise şimdiki zaman)
     */
    public function __construct(
        protected mixed $aggregateId,
        array $data = [],
        ?\DateTimeImmutable $occurredAt = null
    ) {
        $this->data = $data;
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getType(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getAggregateType(): string;

    /**
     * {@inheritdoc}
     */
    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateId(): mixed
    {
        return $this->aggregateId;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     * @throws \JsonException
     */
    public function serialize(): string
    {
        return json_encode([
            'type' => $this->getType(),
            'aggregateType' => $this->getAggregateType(),
            'aggregateId' => $this->getAggregateId(),
            'occurredAt' => $this->getOccurredAt()->format(\DateTimeInterface::ATOM),
            'data' => $this->getData()
        ], JSON_THROW_ON_ERROR) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(string $serialized): self
    {
        $data = json_decode($serialized, true);

        if (!$data) {
            throw new \InvalidArgumentException('Invalid serialized event data');
        }

        return new static(
            $data['aggregateId'],
            $data['data'],
            new \DateTimeImmutable($data['occurredAt'])
        );
    }
}