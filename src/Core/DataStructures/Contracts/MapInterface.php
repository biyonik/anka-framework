<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Map arayüzü.
 *
 * Anahtar-değer çiftlerini tutan bir veri yapısını tanımlar.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends CollectionInterface<TValue>
 */
interface MapInterface extends CollectionInterface
{
    /**
     * Anahtarlar koleksiyonunu döndürür.
     *
     * @return CollectionInterface<TKey> Anahtarlar
     */
    public function keys(): CollectionInterface;

    /**
     * Değerler koleksiyonunu döndürür.
     *
     * @return CollectionInterface<TValue> Değerler
     */
    public function values(): CollectionInterface;

    /**
     * Anahtar-değer çiftlerini döndürür.
     *
     * @return array<int, array{0: TKey, 1: TValue}> Anahtar-değer çiftleri
     */
    public function pairs(): array;

    /**
     * Belirli bir anahtarın map'te olup olmadığını kontrol eder.
     *
     * @param TKey $key Kontrol edilecek anahtar
     * @return bool Varsa true, yoksa false
     */
    public function hasKey(mixed $key): bool;

    /**
     * Map'i filtrelemek için bir callback kullanır.
     *
     * @param callable(TValue, TKey): bool $callback Filtreleme fonksiyonu
     * @return static<TKey, TValue> Filtrelenmiş map
     */
    public function filter(callable $callback): static;

    /**
     * Map'i dönüştürmek için bir callback kullanır.
     *
     * @template TNewValue
     * @param callable(TValue, TKey): TNewValue $callback Dönüştürme fonksiyonu
     * @return static<TKey, TNewValue> Dönüştürülmüş map
     */
    public function map(callable $callback): static;
}