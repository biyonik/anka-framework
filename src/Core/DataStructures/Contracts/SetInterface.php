<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Set arayüzü.
 *
 * Eşsiz değerleri tutan bir veri yapısını tanımlar.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends CollectionInterface<T>
 */
interface SetInterface extends CollectionInterface
{
    /**
     * Set'e bir öğe ekler ve yeni bir set döndürür.
     *
     * @param T $value Eklenecek değer
     * @return static<T> Yeni set
     */
    public function add(mixed $value): static;

    /**
     * Set'ten bir öğeyi kaldırır ve yeni bir set döndürür.
     *
     * @param T $value Kaldırılacak değer
     * @return static<T> Yeni set
     */
    public function remove(mixed $value): static;

    /**
     * İki set'in birleşimini (union) döndürür.
     *
     * @param SetInterface<T> $set Birleştirilecek set
     * @return static<T> Yeni set
     */
    public function union(SetInterface $set): static;

    /**
     * İki set'in kesişimini (intersection) döndürür.
     *
     * @param SetInterface<T> $set Kesişim için kullanılacak set
     * @return static<T> Yeni set
     */
    public function intersect(SetInterface $set): static;

    /**
     * İki set'in farkını (difference) döndürür.
     *
     * @param SetInterface<T> $set Fark için kullanılacak set
     * @return static<T> Yeni set
     */
    public function diff(SetInterface $set): static;

    /**
     * Belirtilen değerin set'te olup olmadığını kontrol eder.
     *
     * @param T $value Kontrol edilecek değer
     * @return bool Varsa true, yoksa false
     */
    public function has(mixed $value): bool;
}