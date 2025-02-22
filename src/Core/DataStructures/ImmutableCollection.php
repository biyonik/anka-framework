<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

/**
 * Değiştirilemez koleksiyon sınıfı.
 *
 * Değiştirilemez (immutable) koleksiyon işlevselliğini sağlar.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractImmutableCollection<T>
 */
class ImmutableCollection extends AbstractImmutableCollection
{
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
            : function ($item) use ($keyOrCallback) {
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

    /**
     * Koleksiyonu değiştirilebilir bir koleksiyona dönüştürür.
     *
     * @return Collection<T> Değiştirilebilir koleksiyon
     */
    public function toMutable(): Collection
    {
        return new Collection($this->items);
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
     * İki koleksiyonu birleştirir ve yeni bir koleksiyon döndürür.
     *
     * @param iterable<int|string, T> $items Birleştirilecek öğeler
     * @return static<T> Yeni koleksiyon
     */
    public function concat(iterable $items): static
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }

        $newItems = array_merge($this->items, (array) $items);
        return new static($newItems);
    }

    /**
     * Belirli bir koşulu sağlayan ilk öğeyi döndürür.
     *
     * @param callable(T, int|string): bool $callback Koşul
     * @param mixed $default Varsayılan değer (hiçbir öğe koşulu sağlamazsa döner)
     * @return T|mixed Bulunan öğe veya varsayılan değer
     */
    public function firstWhere(callable $callback, mixed $default = null): mixed
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }
}