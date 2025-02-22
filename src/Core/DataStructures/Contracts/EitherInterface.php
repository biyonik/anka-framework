<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures\Contracts;

/**
 * Either arayüzü.
 *
 * İki değerden birini tutan Either monad arayüzü.
 * Left genellikle hata durumunu, Right ise başarı durumunu temsil eder.
 *
 * @package Framework\Core\DataStructures
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template L
 * @template R
 */
interface EitherInterface
{
    /**
     * Left değerini döndürür.
     *
     * @return L|null Left değeri
     */
    public function getLeft(): mixed;

    /**
     * Right değerini döndürür.
     *
     * @return R|null Right değeri
     */
    public function getRight(): mixed;

    /**
     * Left mi kontrolü yapar.
     *
     * @return bool Left ise true
     */
    public function isLeft(): bool;

    /**
     * Right mı kontrolü yapar.
     *
     * @return bool Right ise true
     */
    public function isRight(): bool;

    /**
     * Left ise belirtilen fonksiyonu çalıştırır ve yeni bir Either döndürür.
     *
     * @template U
     * @param callable(L): U $callback Left ise çalışacak fonksiyon
     * @return EitherInterface<U, R> Yeni Either
     */
    public function mapLeft(callable $callback): EitherInterface;

    /**
     * Right ise belirtilen fonksiyonu çalıştırır ve yeni bir Either döndürür.
     *
     * @template U
     * @param callable(R): U $callback Right ise çalışacak fonksiyon
     * @return EitherInterface<L, U> Yeni Either
     */
    public function mapRight(callable $callback): EitherInterface;

    /**
     * Either durumuna göre uygun callback'i çalıştırır ve sonucunu döndürür.
     *
     * @template T
     * @param callable(L): T $leftCallback Left ise çalışacak fonksiyon
     * @param callable(R): T $rightCallback Right ise çalışacak fonksiyon
     * @return T Callback sonucu
     */
    public function fold(callable $leftCallback, callable $rightCallback): mixed;

    /**
     * Right ise belirtilen fonksiyonu çalıştırır, Left ise mevcut Either'ı döndürür.
     *
     * @template U
     * @param callable(R): EitherInterface<L, U> $callback Right ise çalışacak fonksiyon
     * @return EitherInterface<L, U> Yeni Either
     */
    public function flatMapRight(callable $callback): EitherInterface;

    /**
     * Left ise belirtilen fonksiyonu çalıştırır, Right ise mevcut Either'ı döndürür.
     *
     * @template U
     * @param callable(L): EitherInterface<U, R> $callback Left ise çalışacak fonksiyon
     * @return EitherInterface<U, R> Yeni Either
     */
    public function flatMapLeft(callable $callback): EitherInterface;

    /**
     * Bir değeri sağ Either'a dönüştürür.
     *
     * @template T
     * @param T $value Sağ değer
     * @return EitherInterface<mixed, T> Yeni Either
     */
    public static function right(mixed $value): EitherInterface;

    /**
     * Bir değeri sol Either'a dönüştürür.
     *
     * @template T
     * @param T $value Sol değer
     * @return EitherInterface<T, mixed> Yeni Either
     */
    public static function left(mixed $value): EitherInterface;

    /**
     * Bir fonksiyonu çalıştırır ve oluşabilecek istisnaları sol Either'a dönüştürür.
     *
     * @template T
     * @param callable(): T $callback Çalıştırılacak fonksiyon
     * @return EitherInterface<\Throwable, T> Yeni Either
     */
    public static function try(callable $callback): EitherInterface;
}