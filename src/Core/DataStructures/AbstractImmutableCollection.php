<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\ImmutableCollectionInterface;

/**
 * Soyut değiştirilemez koleksiyon sınıfı.
 *
 * Değiştirilemez (immutable) koleksiyon implementasyonları için temel işlevleri sağlar.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractCollection<T>
 * @implements ImmutableCollectionInterface<T>
 */
abstract class AbstractImmutableCollection extends AbstractCollection implements ImmutableCollectionInterface
{
    /**
     * Constructor.
     *
     * @param iterable<int|string, T> $items Başlangıç öğeleri
     */
    public function __construct(iterable $items = [])
    {
        parent::__construct($items);
    }

    /**
     * {@inheritdoc}
     */
    public function with(mixed $key, mixed $value): static
    {
        $items = $this->items;
        $items[$key] = $value;

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function without(mixed $key): static
    {
        $items = $this->items;
        unset($items[$key]);

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function append(mixed $value): static
    {
        $items = $this->items;
        $items[] = $value;

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(mixed $value): static
    {
        $items = array_merge([$value], $this->items);

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function toImmutable(string $collectionClass): ImmutableCollectionInterface
    {
        if (!is_subclass_of($collectionClass, ImmutableCollectionInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" must implement "%s"',
                    $collectionClass,
                    ImmutableCollectionInterface::class)
            );
        }

        return new $collectionClass($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException(
            sprintf('Cannot modify immutable collection "%s"', static::class)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException(
            sprintf('Cannot modify immutable collection "%s"', static::class)
        );
    }
}