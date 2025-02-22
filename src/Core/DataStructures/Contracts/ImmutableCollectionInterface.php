<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Değiştirilemez koleksiyon arayüzü.
 *
 * Bu arayüz, değiştirilemez (immutable) koleksiyon veri yapıları için metotları tanımlar.
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
interface ImmutableCollectionInterface extends CollectionInterface
{
    /**
     * Verilen anahtar ve değerle yeni bir koleksiyon döndürür.
     *
     * @param int|string $key Anahtar
     * @param T $value Değer
     * @return static<T> Yeni koleksiyon
     */
    public function with(int|string $key, mixed $value): static;

    /**
     * Verilen anahtarsız yeni bir koleksiyon döndürür.
     *
     * @param int|string $key Silinecek anahtar
     * @return static<T> Yeni koleksiyon
     */
    public function without(int|string $key): static;

    /**
     * Verilen değeri koleksiyonun sonuna ekler ve yeni bir koleksiyon döndürür.
     *
     * @param T $value Eklenecek değer
     * @return static<T> Yeni koleksiyon
     */
    public function append(mixed $value): static;

    /**
     * Verilen değeri koleksiyonun başına ekler ve yeni bir koleksiyon döndürür.
     *
     * @param T $value Eklenecek değer
     * @return static<T> Yeni koleksiyon
     */
    public function prepend(mixed $value): static;

    /**
     * Farklı bir değiştirilemez koleksiyon türüne dönüştürür.
     *
     * @template R
     * @param class-string<ImmutableCollectionInterface<R>> $collectionClass Hedef koleksiyon sınıfı
     * @return ImmutableCollectionInterface<T> Dönüştürülmüş koleksiyon
     */
    public function toImmutable(string $collectionClass): ImmutableCollectionInterface;
}