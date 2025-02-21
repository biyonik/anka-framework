<?php

declare(strict_types=1);

namespace Framework\Core\Event\Domain;

use Framework\Core\Event\AbstractEvent;

/**
 * Domain Event sınıfı.
 *
 * Bu sınıf, domain olayları için temel bir abstract sınıftır.
 * Domain olayları, domain modelleri içinde gerçekleşen önemli değişiklikleri temsil eder.
 *
 * @package Framework\Core\Event
 * @subpackage Domain
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class DomainEvent extends AbstractEvent
{
    /**
     * Olayın ilişkili olduğu aggregate root ID'si.
     *
     * @var string|int|null
     */
    protected string|int|null $aggregateId = null;

    /**
     * Olayın ilişkili olduğu aggregate root türü.
     */
    protected ?string $aggregateType = null;

    /**
     * Olay versiyonu.
     */
    protected int $version = 1;

    /**
     * Constructor.
     *
     * @param string|int|null $aggregateId Aggregate ID
     * @param array<string, mixed> $data Olay verileri
     * @param int $version Olay versiyonu
     */
    public function __construct(string|int|null $aggregateId = null, array $data = [], int $version = 1)
    {
        parent::__construct($data);
        $this->aggregateId = $aggregateId;
        $this->version = $version;
    }

    /**
     * Aggregate ID'sini döndürür.
     *
     * @return string|int|null Aggregate ID
     */
    public function getAggregateId(): string|int|null
    {
        return $this->aggregateId;
    }

    /**
     * Aggregate tipini döndürür.
     *
     * @return string|null Aggregate tipi
     */
    public function getAggregateType(): ?string
    {
        return $this->aggregateType;
    }

    /**
     * Versiyonu döndürür.
     *
     * @return int Versiyon
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Olayın diziye dönüştürülmüş halini döndürür.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'aggregate_id' => $this->aggregateId,
            'aggregate_type' => $this->aggregateType,
            'version' => $this->version,
        ]);
    }
}