<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Lazy Collection arayüzü.
 *
 * Lazy (tembel) değerlendirme yapan koleksiyon arayüzü.
 * Öğeler, gerçekten ihtiyaç duyulana kadar hesaplanmaz.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @extends \IteratorAggregate<int|string, T>
 */
interface LazyCollectionInterface extends \IteratorAggregate, \Countable
{
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
     * Koleksiyonda belirli bir öğeyi arar ve ilk bulduğunu döndürür.
     *
     * @param callable(T, int|string): bool $callback Arama fonksiyonu
     * @param mixed $default Bulunamazsa döndürülecek değer
     * @return T|mixed Bulunan öğe veya default değer
     */
    public function find(callable $callback, mixed $default = null): mixed;

    /**
     * Koleksiyonu belirli bir sayıda öğeye sınırlar.
     *
     * @param int $limit Sınır
     * @return static<T> Sınırlanmış koleksiyon
     */
    public function take(int $limit): static;

    /**
     * Koleksiyonu belirli bir sayıda öğeyi atlayarak oluşturur.
     *
     * @param int $count Atlanacak öğe sayısı
     * @return static<T> Yeni koleksiyon
     */
    public function skip(int $count): static;

    /**
     * Koleksiyonu tembel değerlendirme yerine eager (hevesli) değerlendirme ile bir diziye dönüştürür.
     *
     * @return array<int|string, T> Dizi
     */
    public function toArray(): array;

    /**
     * Koleksiyonu tembel değerlendirme yerine eager (hevesli) değerlendirme ile standart Collection sınıfına dönüştürür.
     *
     * @return CollectionInterface<T> Collection
     */
    public function toCollection(): CollectionInterface;

    /**
     * Koleksiyonda verilen callback'i sağlayan herhangi bir öğenin olup olmadığını kontrol eder.
     *
     * @param callable(T, int|string): bool $callback Kontrol fonksiyonu
     * @return bool Varsa true, yoksa false
     */
    public function any(callable $callback): bool;

    /**
     * Koleksiyondaki tüm öğelerin verilen callback'i sağlayıp sağlamadığını kontrol eder.
     *
     * @param callable(T, int|string): bool $callback Kontrol fonksiyonu
     * @return bool Tümü sağlıyorsa true, en az biri sağlamıyorsa false
     */
    public function all(callable $callback): bool;

    /**
     * Koleksiyonu verilen callback ile reduce işlemine tabi tutar.
     *
     * @template R
     * @param callable(R, T): R $callback Reduce fonksiyonu
     * @param R $initial Başlangıç değeri
     * @return R Sonuç
     */
    public function reduce(callable $callback, mixed $initial = null): mixed;

    /**
     * Koleksiyonu sona kadar işler ve hiçbir değer döndürmez.
     * Yan etki yaratmak için kullanılabilir.
     *
     * @return void
     */
    public function consume(): void;

    /**
     * Koleksiyonu verilen callback ile işler ve hiçbir değer döndürmez.
     *
     * @param callable(T, int|string): void $callback İşlenecek fonksiyon
     * @return void
     */
    public function each(callable $callback): void;

    /**
     * Koleksiyonu chunk'lara (parçalara) böler.
     *
     * @param int $size Chunk boyutu
     * @return static<array<int|string, T>> Chunk'lar koleksiyonu
     */
    public function chunk(int $size): static;

    /**
     * Iterator oluşturur.
     *
     * @return \Traversable<int|string, T> Iterator
     */
    public function getIterator(): \Traversable;
}