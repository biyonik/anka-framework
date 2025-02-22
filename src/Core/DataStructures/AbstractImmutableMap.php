<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\ImmutableMapInterface;
use Framework\Core\DataStructures\Contracts\CollectionInterface;
use Framework\Core\DataStructures\Contracts\ImmutableCollectionInterface;

/**
 * Soyut değiştirilemez Map sınıfı.
 *
 * Değiştirilemez anahtar-değer çiftlerini tutan Map için temel implementasyon.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends AbstractImmutableCollection<TValue>
 * @implements ImmutableMapInterface<TKey, TValue>
 */
abstract class AbstractImmutableMap extends AbstractImmutableCollection implements ImmutableMapInterface
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
        return new ImmutableCollection(array_keys($this->items));
    }

    /**
     * {@inheritdoc}
     */
    public function values(): CollectionInterface
    {
        return new ImmutableCollection(array_values($this->items));
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
     * {@inheritdoc}
     */
    public function toImmutable(string $collectionClass): ImmutableCollectionInterface
    {
        if (!is_subclass_of($collectionClass, ImmutableMapInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" must implement "%s"',
                    $collectionClass,
                    ImmutableMapInterface::class)
            );
        }

        return new $collectionClass($this->items);
    }
}