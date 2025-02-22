<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\MapInterface;
use Framework\Core\DataStructures\Contracts\CollectionInterface;

/**
 * Soyut Map sınıfı.
 *
 * Anahtar-değer çiftlerini tutan Map için temel implementasyon.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends AbstractCollection<TValue>
 * @implements MapInterface<TKey, TValue>
 */
abstract class AbstractMap extends AbstractCollection implements MapInterface
{
    /**
     * Constructor.
     *
     * @param iterable<TKey, TValue> $items Başlangıç öğeleri
     */
    public function __construct(iterable $items = [])
    {
        parent::__construct($items);
    }

    /**
     * {@inheritdoc}
     */
    public function keys(): CollectionInterface
    {
        return new Collection(array_keys($this->items));
    }

    /**
     * {@inheritdoc}
     */
    public function values(): CollectionInterface
    {
        return new Collection(array_values($this->items));
    }

    /**
     * {@inheritdoc}
     */
    public function pairs(): array
    {
        $result = [];
        $i = 0;

        foreach ($this->items as $key => $value) {
            $result[$i++] = [$key, $value];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey(mixed $key): bool
    {
        return $this->has($key);
    }

    /**
     * Koleksiyonu mutable map formatına dönüştürür.
     *
     * @return Map<TKey, TValue> Map
     */
    public function toMap(): Map
    {
        return new Map($this->items);
    }

    /**
     * Koleksiyonu immutable map formatına dönüştürür.
     *
     * @return ImmutableMap<TKey, TValue> Immutable Map
     */
    public function toImmutableMap(): ImmutableMap
    {
        return new ImmutableMap($this->items);
    }
}