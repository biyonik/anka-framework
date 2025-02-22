<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Değiştirilemez Map arayüzü.
 *
 * Değiştirilemez anahtar-değer çiftlerini tutan bir veri yapısını tanımlar.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends MapInterface<TKey, TValue>
 * @extends ImmutableCollectionInterface<TValue>
 */
interface ImmutableMapInterface extends MapInterface, ImmutableCollectionInterface
{
    /**
     * Verilen anahtarla yeni bir harita döndürür.
     *
     * @param TKey $key Anahtar
     * @param TValue $value Değer
     * @return static<TKey, TValue> Yeni harita
     */
    public function with(mixed $key, mixed $value): static;

    /**
     * Verilen anahtarsız yeni bir harita döndürür.
     *
     * @param TKey $key Silinecek anahtar
     * @return static<TKey, TValue> Yeni harita
     */
    public function without(mixed $key): static;
}