<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\SetInterface;

/**
 * Değiştirilemez Set sınıfı.
 *
 * Eşsiz değerler tutan, değiştirilemez (immutable) set.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends AbstractImmutableSet<T>
 */
class ImmutableSet extends AbstractImmutableSet
{
    /**
     * Değiştirilebilir bir set'e dönüştürür.
     *
     * @return Set<T> Değiştirilebilir set
     */
    public function toMutable(): Set
    {
        return new Set($this->items);
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

    /**
     * Set'in simetrik farkını döndürür (iki set'ten birinde olup diğerinde olmayan öğeler).
     *
     * @param SetInterface<T> $set Simetrik fark için kullanılacak set
     * @return static<T> Yeni set
     */
    public function symmetricDiff(SetInterface $set): static
    {
        $diffA = $this->diff($set);
        $diffB = $set->diff($this);

        return $diffA->union($diffB);
    }

    /**
     * Set'in başka bir set ile eşitliğini kontrol eder.
     *
     * @param SetInterface<T> $set Kontrol edilecek set
     * @return bool Eşitse true
     */
    public function equals(SetInterface $set): bool
    {
        if ($this->count() !== $set->count()) {
            return false;
        }

        return $this->isSubsetOf($set);
    }

    /**
     * İki set'in kartezyen çarpımını döndürür.
     *
     * @template U
     * @param SetInterface<U> $set Çarpım için kullanılacak set
     * @return ImmutableSet<array{0: T, 1: U}> Yeni set
     */
    public function cartesianProduct(SetInterface $set): ImmutableSet
    {
        $result = [];

        foreach ($this->items as $item1) {
            foreach ($set as $item2) {
                $result[] = [$item1, $item2];
            }
        }

        return new ImmutableSet($result);
    }

    /**
     * Set'in güç kümesini (power set) döndürür.
     *
     * @return ImmutableSet<array<int, T>> Yeni set
     */
    public function powerSet(): ImmutableSet
    {
        $values = array_values($this->items);
        $count = count($values);
        $subsets = [[]]; // Boş küme her zaman dahildir

        for ($i = 0; $i < $count; $i++) {
            $current = $values[$i];
            $newSubsets = [];

            foreach ($subsets as $subset) {
                $newSubsets[] = array_merge($subset, [$current]);
            }

            $subsets = array_merge($subsets, $newSubsets);
        }

        return new ImmutableSet($subsets);
    }
}