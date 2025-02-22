<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

/**
 * Map sınıfı.
 *
 * Anahtar-değer çiftlerini tutan, değiştirilebilir (mutable) map.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends AbstractMap<TKey, TValue>
 */
class Map extends AbstractMap
{
    /**
     * Bir anahtar-değer çifti ekler.
     *
     * @param TKey $key Anahtar
     * @param TValue $value Değer
     * @return $this Akıcı arayüz için
     */
    public function put(mixed $key, mixed $value): self
    {
        $this->items[$key] = $value;
        return $this;
    }

    /**
     * Bir anahtarı kaldırır.
     *
     * @param TKey $key Anahtar
     * @return $this Akıcı arayüz için
     */
    public function remove(mixed $key): self
    {
        unset($this->items[$key]);
        return $this;
    }

    /**
     * Tüm öğeleri kaldırır.
     *
     * @return $this Akıcı arayüz için
     */
    public function clear(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * Değiştirmeden yeni bir anahtar-değer çifti eklenmiş map döndürür.
     * (Immutable davranışı taklit eder)
     *
     * @param TKey $key Anahtar
     * @param TValue $value Değer
     * @return static<TKey, TValue> Yeni map
     */
    public function with(mixed $key, mixed $value): static
    {
        $map = clone $this;
        $map->items[$key] = $value;

        return $map;
    }

    /**
     * Değiştirmeden bir anahtarı kaldırılmış map döndürür.
     * (Immutable davranışı taklit eder)
     *
     * @param TKey $key Anahtar
     * @return static<TKey, TValue> Yeni map
     */
    public function without(mixed $key): static
    {
        $map = clone $this;
        unset($map->items[$key]);

        return $map;
    }

    /**
     * Bir değiştirilemeyen (immutable) map'e dönüştürür.
     *
     * @return ImmutableMap<TKey, TValue> Değiştirilemeyen map
     */
    public function toImmutable(): ImmutableMap
    {
        return new ImmutableMap($this->items);
    }

    /**
     * Bu map ile başka bir map'i birleştirir.
     *
     * @param iterable<TKey, TValue> $map Birleştirilecek map
     * @return $this Akıcı arayüz için
     */
    public function merge(iterable $map): static
    {
        if ($map instanceof \Traversable) {
            $map = iterator_to_array($map);
        }

        $this->items = array_merge($this->items, (array) $map);
        return $this;
    }

    /**
     * Belirli anahtarları içeren yeni bir map döndürür.
     *
     * @param array<int, TKey> $keys Seçilecek anahtarlar
     * @return static<TKey, TValue> Yeni map
     */
    public function only(array $keys): static
    {
        $items = array_intersect_key($this->items, array_flip($keys));
        return new static($items);
    }

    /**
     * Belirli anahtarları hariç tutan yeni bir map döndürür.
     *
     * @param array<int, TKey> $keys Hariç tutulacak anahtarlar
     * @return static<TKey, TValue> Yeni map
     */
    public function except(array $keys): static
    {
        $items = array_diff_key($this->items, array_flip($keys));
        return new static($items);
    }

    /**
     * Her anahtarı bir callback ile işleyerek yeni bir map döndürür.
     *
     * @template TNewKey of array-key
     * @param callable(TKey): TNewKey $callback Anahtar dönüştürme fonksiyonu
     * @return static<TNewKey, TValue> Yeni map
     */
    public function mapKeys(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $newKey = $callback($key);
            $result[$newKey] = $value;
        }

        return new static($result);
    }

    /**
     * Map içindeki tüm anahtarları bir callback ile döngüler.
     *
     * @param callable(TKey): void $callback Her anahtar için çalıştırılacak callback
     * @return $this Akıcı arayüz için
     */
    public function eachKey(callable $callback): self
    {
        foreach ($this->items as $key => $_) {
            $callback($key);
        }

        return $this;
    }

    /**
     * Map içindeki tüm değerleri bir callback ile döngüler.
     *
     * @param callable(TValue): void $callback Her değer için çalıştırılacak callback
     * @return $this Akıcı arayüz için
     */
    public function eachValue(callable $callback): self
    {
        foreach ($this->items as $value) {
            $callback($value);
        }

        return $this;
    }
}