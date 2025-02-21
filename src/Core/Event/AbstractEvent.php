<?php

declare(strict_types=1);

namespace Framework\Core\Event;

use Framework\Core\Event\Contracts\EventInterface;

/**
 * Temel Event sınıfı.
 *
 * Bu sınıf, tüm Event nesneleri için temel yapıyı sağlar.
 *
 * @package Framework\Core\Event
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * Olayın zamanı.
     */
    protected \DateTimeImmutable $timestamp;

    /**
     * Olay verileri.
     *
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $data Olay verileri
     */
    public function __construct(array $data = [])
    {
        $this->timestamp = new \DateTimeImmutable();
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
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
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Olayı diziye dönüştürür.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'timestamp' => $this->timestamp->format(\DateTimeInterface::ATOM),
            'data' => $this->data,
        ];
    }

    /**
     * Olayı JSON'a dönüştürür.
     *
     * @return string JSON formatında olay
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Magic method - String temsili.
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s [%s]',
            $this->getName(),
            $this->timestamp->format(\DateTimeInterface::ATOM)
        );
    }
}