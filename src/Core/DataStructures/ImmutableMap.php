<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

/**
 * Değiştirilemez Map sınıfı.
 *
 * Anahtar-değer çiftlerini tutan, değiştirilemez (immutable) map.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends AbstractImmutableMap<TKey, TValue>
 */
class ImmutableMap extends AbstractImmutableMap
{
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
     * Değiştirilebilir bir map'e dönüştürür.
     *
     * @return Map<TKey, TValue> Değiştirilebilir map
     */
    public function toMutable(): Map
    {
        return new Map($this->items);
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

    /**
     * Map içindeki tüm anahtar-değer çiftlerini bir callback ile döngüler.
     *
     * @param callable(TValue, TKey): void $callback Her anahtar-değer çifti için çalıştırılacak callback
     * @return $this Akıcı arayüz için
     */
    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $value) {
            $callback($value, $key);
        }

        return $this;
    }

    /**
     * Bu map ile başka bir map'i birleştirir ve yeni bir map döndürür.
     *
     * @param iterable<TKey, TValue> $map Birleştirilecek map
     * @return static<TKey, TValue> Yeni map
     */
    public function merge(iterable $map): static
    {
        if ($map instanceof \Traversable) {
            $map = iterator_to_array($map);
        }

        $items = array_merge($this->items, (array) $map);
        return new static($items);
    }

    /**
     * Map'i tersine çevirir (anahtar ve değerleri değiştirir).
     *
     * @return static<TValue, TKey> Ters çevrilmiş map
     * @throws \RuntimeException Değerler eşsiz değilse
     */
    public function flip(): static
    {
        $flipped = array_flip($this->items);

        if (count($flipped) !== count($this->items)) {
            throw new \RuntimeException('Cannot flip map: values are not unique');
        }

        return new static($flipped);
    }

    /**
     * Map içinde verilen koşula uyan ilk anahtar-değer çiftini döndürür.
     *
     * @param callable(TValue, TKey): bool $callback Koşul fonksiyonu
     * @param TValue|null $default Varsayılan değer
     * @return TValue|null Bulunan değer veya varsayılan
     */
    public function first(callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return parent::first();
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }
}