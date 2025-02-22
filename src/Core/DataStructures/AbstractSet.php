<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\SetInterface;

/**
 * Soyut Set sınıfı.
 *
 * Eşsiz değerler tutan Set için temel implementasyon.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractCollection<T>
 * @implements SetInterface<T>
 */
abstract class AbstractSet extends AbstractCollection implements SetInterface
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

    /**
     * Set'i değiştirilebilir set'e dönüştürür.
     *
     * @return Set<T> Değiştirilebilir set
     */
    public function toMutableSet(): Set
    {
        return new Set($this->items);
    }

    /**
     * Set'i değiştirilemez set'e dönüştürür.
     *
     * @return ImmutableSet<T> Değiştirilemez set
     */
    public function toImmutableSet(): ImmutableSet
    {
        return new ImmutableSet($this->items);
    }
}