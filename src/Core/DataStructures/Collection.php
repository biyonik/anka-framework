<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

/**
 * Standart koleksiyon sınıfı.
 *
 * Temel koleksiyon işlevselliğini sağlayan, değiştirilebilir (mutable) koleksiyon.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractCollection<T>
 */
class Collection extends AbstractCollection
{
    /**
     * Yeni bir öğe ekler ve zincir için kendi örneğini döndürür.
     *
     * @param int|string|null $key Anahtar (null ise otomatik atanır)
     * @param T $value Değer
     * @return $this Akıcı arayüz için
     */
    public function add(int|string|null $key, mixed $value): self
    {
        if ($key === null) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Bir öğeyi kaldırır ve zincir için kendi örneğini döndürür.
     *
     * @param int|string $key Kaldırılacak öğenin anahtarı
     * @return $this Akıcı arayüz için
     */
    public function remove(int|string $key): self
    {
        unset($this->items[$key]);
        return $this;
    }

    /**
     * Tüm öğeleri kaldırır ve zincir için kendi örneğini döndürür.
     *
     * @return $this Akıcı arayüz için
     */
    public function clear(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * Koleksiyonu değiştirmeden bir anahtarı ve değerini değiştirerek yeni bir koleksiyon döndürür.
     * (Immutable davranışı taklit eder)
     *
     * @param int|string $key Anahtar
     * @param T $value Değer
     * @return static Yeni koleksiyon
     */
    public function with(int|string $key, mixed $value): static
    {
        $items = $this->items;
        $items[$key] = $value;

        return new static($items);
    }

    /**
     * Koleksiyonu değiştirmeden bir anahtarı kaldırarak yeni bir koleksiyon döndürür.
     * (Immutable davranışı taklit eder)
     *
     * @param int|string $key Silinecek anahtar
     * @return static Yeni koleksiyon
     */
    public function without(int|string $key): static
    {
        $items = $this->items;
        unset($items[$key]);

        return new static($items);
    }

    /**
     * Koleksiyonu değiştirilemeyen (immutable) bir koleksiyona dönüştürür.
     *
     * @return ImmutableCollection<T> Değiştirilemeyen koleksiyon
     */
    public function toImmutable(): ImmutableCollection
    {
        return new ImmutableCollection($this->items);
    }

    /**
     * Koleksiyonu döngüleyerek her öğe için bir callback çalıştırır.
     *
     * @param callable(T, int|string): void $callback Her öğe için çalıştırılacak callback
     * @return $this Akıcı arayüz için
     */
    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }

        return $this;
    }

    /**
     * Belirli bir kritere göre koleksiyonu gruplar.
     *
     * @param callable(T, int|string): mixed $callback Gruplama kriteri
     * @return static<array<int|string, T>> Gruplandırılmış koleksiyon
     */
    public function groupBy(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $groupKey = $callback($value, $key);

            if (!isset($result[$groupKey])) {
                $result[$groupKey] = [];
            }

            $result[$groupKey][$key] = $value;
        }

        return new static($result);
    }

    /**
     * Belirli bir sayıda öğeyi atlayıp geriye kalanı döndürür.
     *
     * @param int $count Atlanacak öğe sayısı
     * @return static<T> Yeni koleksiyon
     */
    public function skip(int $count): static
    {
        return new static(array_slice($this->items, $count, null, true));
    }

    /**
     * Belirli bir sayıda öğeyi alır.
     *
     * @param int $count Alınacak öğe sayısı
     * @return static<T> Yeni koleksiyon
     */
    public function take(int $count): static
    {
        return new static(array_slice($this->items, 0, $count, true));
    }

    /**
     * Koleksiyondaki anahtarları döndürür.
     *
     * @return array<int, int|string> Anahtarlar
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * Koleksiyondaki değerleri döndürür.
     *
     * @return array<int, T> Değerler
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * Belirli bir anahtara göre sıralar.
     *
     * @param callable|string $keyOrCallback Anahtar veya callback fonksiyonu
     * @param string $direction Sıralama yönü ('asc' veya 'desc')
     * @return static<T> Sıralanmış koleksiyon
     */
    public function sortBy(callable|string $keyOrCallback, string $direction = 'asc'): static
    {
        $items = $this->items;

        $callback = is_callable($keyOrCallback)
            ? $keyOrCallback
            : static function ($item) use ($keyOrCallback) {
                return is_array($item) ? ($item[$keyOrCallback] ?? null) : ($item->{$keyOrCallback} ?? null);
            };

        uasort($items, function ($a, $b) use ($callback, $direction) {
            $a = $callback($a);
            $b = $callback($b);

            if ($a === $b) {
                return 0;
            }

            if ($direction === 'desc') {
                return $a > $b ? -1 : 1;
            }

            return $a > $b ? 1 : -1;
        });

        return new static($items);
    }

    /**
     * Verilen callback'e göre koleksiyondaki öğeleri uniqleştirir.
     *
     * @param callable|null $callback Eşsizlik kriterleri için callback
     * @return static<T> Eşsizleştirilmiş koleksiyon
     */
    public function unique(?callable $callback = null): static
    {
        if ($callback === null) {
            // Basit uniqueleştirme (varsayılan)
            return new static(array_unique($this->items, SORT_REGULAR));
        }

        // Callback kullanarak uniqueleştirme
        $result = [];
        $seen = [];

        foreach ($this->items as $key => $value) {
            $id = $callback($value, $key);

            if (!isset($seen[$id])) {
                $seen[$id] = true;
                $result[$key] = $value;
            }
        }

        return new static($result);
    }
}