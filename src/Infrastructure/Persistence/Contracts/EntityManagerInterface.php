<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence\Contracts;

/**
 * Entity Manager arayüzü.
 *
 * Bu arayüz, veritabanı işlemleri için bir Unit of Work pattern implementasyonu sağlar.
 * Repository'leri yönetir ve transaction işlemlerini koordine eder.
 *
 * @package Framework\Infrastructure\Persistence
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface EntityManagerInterface
{
    /**
     * Belirtilen repository tipine ait bir instance döndürür.
     *
     * @template T
     * @param class-string<T> $repositoryClass Repository sınıfı
     * @return T Repository instance'ı
     */
    public function getRepository(string $repositoryClass): mixed;

    /**
     * Entity'yi persist etmek için kaydeder.
     *
     * @param object $entity Kaydedilecek entity
     * @return void
     */
    public function persist(object $entity): void;

    /**
     * Entity'yi silmek için işaretler.
     *
     * @param object $entity Silinecek entity
     * @return void
     */
    public function remove(object $entity): void;

    /**
     * Bekleme listesindeki tüm değişiklikleri veritabanına uygular.
     *
     * @return int Etkilenen kayıt sayısı
     */
    public function flush(): int;

    /**
     * Yeni bir transaction başlatır.
     *
     * @return bool İşlem başarılı ise true
     */
    public function beginTransaction(): bool;

    /**
     * Aktif transaction'ı onaylar.
     *
     * @return bool İşlem başarılı ise true
     */
    public function commit(): bool;

    /**
     * Aktif transaction'ı geri alır.
     *
     * @return bool İşlem başarılı ise true
     */
    public function rollback(): bool;

    /**
     * Bir transaction içinde olup olmadığını kontrol eder.
     *
     * @return bool Transaction içinde ise true
     */
    public function inTransaction(): bool;

    /**
     * Bir fonksiyonu transaction içinde çalıştırır.
     *
     * @param callable $callback Çalıştırılacak fonksiyon
     * @return mixed Fonksiyonun döndürdüğü değer
     */
    public function transactional(callable $callback): mixed;

    /**
     * Entity Manager'ı temizler, tüm bekleyen değişiklikleri iptal eder.
     *
     * @return void
     */
    public function clear(): void;
}