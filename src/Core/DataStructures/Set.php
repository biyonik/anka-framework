<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\SetInterface;

/**
 * Set sınıfı.
 *
 * Eşsiz değerler tutan, değiştirilebilir (mutable) set.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractSet<T>
 */
class Set extends AbstractSet
{
    /**
     * Bir değer ekler ve zincir için kendi örneğini döndürür.
     *
     * @param T $value Eklenecek değer
     * @return $this Akıcı arayüz için
     */
    public function addValue(mixed $value): self
    {
        // Eğer değer zaten varsa, değişiklik yapma
        if (!$this->has($value)) {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Bir değeri kaldırır ve zincir için kendi örneğini döndürür.
     *
     * @param T $value Kaldırılacak değer
     * @return $this Akıcı arayüz için
     */
    public function removeValue(mixed $value): self
    {
        $key = array_search($value, $this->items, true);

        if ($key !== false) {
            unset($this->items[$key]);
            $this->items = array_values($this->items); // Anahtarları yeniden indeksle
        }

        return $this;
    }

    /**
     * Tüm değerleri kaldırır ve zincir için kendi örneğini döndürür.
     *
     * @return $this Akıcı arayüz için
     */
    public function clear(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * Başka bir set ile birleştirir ve zincir için kendi örneğini döndürür.
     *
     * @param SetInterface<T> $set Birleştirilecek set
     * @return $this Akıcı arayüz için
     */
    public function addAll(SetInterface $set): self
    {
        foreach ($set as $value) {
            $this->addValue($value);
        }

        return $this;
    }

    /**
     * Başka bir set ile kesişir ve zincir için kendi örneğini döndürür.
     *
     * @param SetInterface<T> $set Kesişim için kullanılacak set
     * @return $this Akıcı arayüz için
     */
    public function retainAll(SetInterface $set): self
    {
        $this->items = array_intersect($this->items, $set->toArray());
        $this->items = array_values($this->items); // Anahtarları yeniden indeksle

        return $this;
    }

    /**
     * Başka bir set ile farkını alır ve zincir için kendi örneğini döndürür.
     *
     * @param SetInterface<T> $set Fark için kullanılacak set
     * @return $this Akıcı arayüz için
     */
    public function removeAll(SetInterface $set): self
    {
        $this->items = array_diff($this->items, $set->toArray());
        $this->items = array_values($this->items); // Anahtarları yeniden indeksle

        return $this;
    }

    /**
     * Set'i değiştirilemez bir set'e dönüştürür.
     *
     * @return ImmutableSet<T> Değiştirilemez set
     */
    public function toImmutable(): ImmutableSet
    {
        return new ImmutableSet($this->items);
    }

    /**
     * Set'in başka bir set'in alt kümesi olup olmadığını kontrol eder.
     *
     * @param SetInterface<T> $set Üst küme
     * @return bool Alt kümeyse true
     */
    public function isSubsetOf(SetInterface $set): bool
    {
        foreach ($this->items as $item) {
            if (!$set->has($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set'in başka bir set'in üst kümesi olup olmadığını kontrol eder.
     *
     * @param SetInterface<T> $set Alt küme
     * @return bool Üst kümeyse true
     */
    public function isSupersetOf(SetInterface $set): bool
    {
        return $set->isSubsetOf($this);
    }

    /**
     * Set'in başka bir set ile kesişiminin boş olup olmadığını kontrol eder.
     *
     * @param SetInterface<T> $set Kontrol edilecek set
     * @return bool Kesişim boşsa true
     */
    public function isDisjointWith(SetInterface $set): bool
    {
        foreach ($this->items as $item) {
            if ($set->has($item)) {
                return false;
            }
        }

        return true;
    }
}