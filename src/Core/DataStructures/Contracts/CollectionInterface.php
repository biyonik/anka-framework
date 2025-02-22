<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Koleksiyon arayüzü.
 *
 * Bu arayüz, tüm koleksiyon veri yapıları için temel metotları tanımlar.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends \IteratorAggregate<int|string, T>
 * @extends \ArrayAccess<int|string, T>
 */
interface CollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    /**
     * Koleksiyondaki tüm öğeleri döndürür.
     *
     * @return array<int|string, T> Tüm öğeler
     */
    public function all(): array;

    /**
     * Koleksiyonun boş olup olmadığını kontrol eder.
     *
     * @return bool Boşsa true, değilse false
     */
    public function isEmpty(): bool;

    /**
     * Koleksiyonda verilen anahtarın olup olmadığını kontrol eder.
     *
     * @param int|string $key Kontrol edilecek anahtar
     * @return bool Varsa true, yoksa false
     */
    public function has(int|string $key): bool;

    /**
     * Koleksiyonda verilen değerin olup olmadığını kontrol eder.
     *
     * @param mixed $value Kontrol edilecek değer
     * @param bool $strict Katı tip kontrolü yapılsın mı?
     * @return bool Varsa true, yoksa false
     */
    public function contains(mixed $value, bool $strict = true): bool;

    /**
     * Verilen anahtardaki değeri döndürür.
     *
     * @param int|string $key Değeri alınacak anahtar
     * @param mixed $default Varsayılan değer (anahtar bulunamazsa döner)
     * @return T|mixed Değer veya varsayılan değer
     */
    public function get(int|string $key, mixed $default = null): mixed;

    /**
     * Koleksiyondaki ilk öğeyi döndürür.
     *
     * @return T|null İlk öğe veya boşsa null
     */
    public function first(): mixed;

    /**
     * Koleksiyondaki son öğeyi döndürür.
     *
     * @return T|null Son öğe veya boşsa null
     */
    public function last(): mixed;

    /**
     * Bir callback ile koleksiyondaki tüm öğeleri dönüştürür.
     *
     * @template R
     * @param callable(T, int|string): R $callback Dönüştürme fonksiyonu
     * @return static<R> Dönüştürülmüş koleksiyon
     */
    public function map(callable $callback): static;

    /**
     * Bir callback ile koleksiyondaki öğeleri filtreler.
     *
     * @param callable(T, int|string): bool $callback Filtreleme fonksiyonu
     * @return static<T> Filtrelenmiş koleksiyon
     */
    public function filter(callable $callback): static;

    /**
     * Verilen callback ile koleksiyonu azaltır.
     *
     * @template R
     * @param callable(R, T): R $callback Azaltma fonksiyonu
     * @param R $initial Başlangıç değeri
     * @return R Sonuç
     */
    public function reduce(callable $callback, mixed $initial = null): mixed;

    /**
     * Belirtilen öğeleri koleksiyona ekler (yeni koleksiyon döner).
     *
     * @param iterable<int|string, T> $items Eklenecek öğeler
     * @return static<T> Yeni koleksiyon
     */
    public function merge(iterable $items): static;

    /**
     * Koleksiyonu bir diziye dönüştürür.
     *
     * @return array<int|string, T> Dizi
     */
    public function toArray(): array;

    /**
     * Koleksiyonu sıralar.
     *
     * @param callable(T, T): int|null $callback Sıralama fonksiyonu
     * @return static<T> Sıralanmış koleksiyon
     */
    public function sort(?callable $callback = null): static;

    /**
     * Koleksiyonu tersine çevirir.
     *
     * @return static<T> Ters çevrilmiş koleksiyon
     */
    public function reverse(): static;

    /**
     * Koleksiyondaki öğe sayısını döndürür.
     *
     * @return int Öğe sayısı
     */
    public function count(): int;

    /**
     * Koleksiyonun string temsilini döndürür.
     *
     * @return string String temsili
     */
    public function __toString(): string;
}