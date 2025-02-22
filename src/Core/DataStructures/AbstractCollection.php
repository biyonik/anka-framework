<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\CollectionInterface;

/**
 * Soyut koleksiyon sınıfı.
 *
 * Tüm koleksiyon implementasyonları için temel işlevleri sağlar.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @implements CollectionInterface<T>
 */
abstract class AbstractCollection implements CollectionInterface
{
    /**
     * Koleksiyonun içerdiği öğeler.
     *
     * @var array<int|string, T>
     */
    protected array $items = [];

    /**
     * Constructor.
     *
     * @param iterable<int|string, T> $items Başlangıç öğeleri
     */
    public function __construct(iterable $items = [])
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }

        $this->items = (array) $items;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(mixed $value, bool $strict = true): bool
    {
        return in_array($value, $this->items, $strict);
    }

    /**
     * {@inheritdoc}
     */
    public function get(int|string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->items[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function first(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }

        $key = array_key_first($this->items);
        return $this->items[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function last(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }

        $key = array_key_last($this->items);
        return $this->items[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return new static($result);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                $result[$key] = $value;
            }
        }

        return new static($result);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $carry = $initial;

        foreach ($this->items as $key => $value) {
            $carry = $callback($carry, $value, $key);
        }

        return $carry;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(iterable $items): static
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }

        return new static(array_merge($this->items, (array) $items));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(?callable $callback = null): static
    {
        $items = $this->items;

        if ($callback === null) {
            asort($items);
        } else {
            uasort($items, $callback);
        }

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): mixed
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Koleksiyonu JSON string'e dönüştürür.
     *
     * @param int $options JSON encode seçenekleri
     * @return string JSON string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}