<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\ImmutableSetInterface;
use Framework\Core\DataStructures\Contracts\ImmutableCollectionInterface;
use Framework\Core\DataStructures\Contracts\SetInterface;

/**
 * Soyut değiştirilemez Set sınıfı.
 *
 * Değiştirilemez eşsiz değerler tutan Set için temel implementasyon.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractImmutableCollection<T>
 * @implements ImmutableSetInterface<T>
 */
abstract class AbstractImmutableSet extends AbstractImmutableCollection implements ImmutableSetInterface
{
    /**
     * Constructor.
     *
     * @param iterable<int|string, T> $items Başlangıç öğeleri
     */
    public function __construct(iterable $items = [])
    {
        // Değerleri eşsizleştir
        parent::__construct($this->makeUnique($items));
    }

    /**
     * {@inheritdoc}
     */
    public function add(mixed $value): static
    {
        $items = $this->items;

        // Eğer değer zaten varsa, aynı set'i döndür
        if (in_array($value, $items, true)) {
            return clone $this;
        }

        $items[] = $value;
        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(mixed $value): static
    {
        $items = $this->items;

        $key = array_search($value, $items, true);

        if ($key !== false) {
            unset($items[$key]);
        }

        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function union(SetInterface $set): static
    {
        $items = array_merge($this->items, $set->toArray());
        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function intersect(SetInterface $set): static
    {
        $items = array_intersect($this->items, $set->toArray());
        return new static($items);
    }

    /**
     * {@inheritdoc}
     */
    public function diff(SetInterface $set): static
    {
        $items = array_diff($this->items, $set->toArray());
        return new static($items);
    }

    /**
     * {@inheritdoc}
     *
     * Not: CollectionInterface'deki has metodunu, değer kontrolü için override ediyoruz.
     */
    public function has(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * {@inheritdoc}
     */
    public function toImmutable(string $collectionClass): ImmutableCollectionInterface
    {
        if (!is_subclass_of($collectionClass, ImmutableSetInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" must implement "%s"',
                    $collectionClass,
                    ImmutableSetInterface::class)
            );
        }

        return new $collectionClass($this->items);
    }

    /**
     * Verilen iterable'daki değerleri eşsizleştirir.
     *
     * @param iterable<int|string, T> $items Eşsizleştirilecek öğeler
     * @return array<int, T> Eşsizleştirilmiş öğeler
     */
    protected function makeUnique(iterable $items): array
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }

        $result = [];

        foreach ($items as $item) {
            if (!in_array($item, $result, true)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}