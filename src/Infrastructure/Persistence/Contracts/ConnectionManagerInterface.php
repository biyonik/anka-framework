<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence\Contracts;

use PDO;

/**
 * Veritabanı bağlantı yöneticisi arayüzü.
 * 
 * Bu arayüz, veritabanı bağlantılarını yönetmek için gerekli metodları tanımlar.
 * Bağlantı oluşturma, kapatma ve transaction yönetimi gibi temel veritabanı
 * işlemleri için kullanılır.
 * 
 * @package Framework\Infrastructure\Persistence
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ConnectionManagerInterface
{
    /**
     * Veritabanı bağlantısını döndürür.
     * 
     * Eğer bağlantı yoksa yeni bağlantı oluşturur.
     * 
     * @return PDO Aktif veritabanı bağlantısı
     */
    public function getConnection(): PDO;

    /**
     * Yeni bir bağlantı oluşturur.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @return PDO Oluşturulan bağlantı
     */
    public function connect(array $config): PDO;

    /**
     * Aktif bağlantıyı kapatır.
     * 
     * @return void
     */
    public function disconnect(): void;

    /**
     * Transaction başlatır.
     * 
     * @return bool İşlem başarılı ise true
     */
    public function beginTransaction(): bool;

    /**
     * Transaction'ı onaylar.
     * 
     * @return bool İşlem başarılı ise true
     */
    public function commit(): bool;

    /**
     * Transaction'ı geri alır.
     * 
     * @return bool İşlem başarılı ise true
     */
    public function rollback(): bool;

    /**
     * Transaction içinde olup olmadığını kontrol eder.
     * 
     * @return bool Transaction içinde ise true
     */
    public function inTransaction(): bool;

    /**
     * En son insert edilen satırın ID'sini döndürür.
     * 
     * @param string|null $name Sequence name (sadece PostgreSQL için)
     * @return string Son eklenen ID
     */
    public function lastInsertId(?string $name = null): string;

    /**
     * Bağlantının durumunu kontrol eder.
     * 
     * @return bool Bağlantı aktif ise true
     */
    public function isConnected(): bool;

    /**
     * Sorgu ifadesi oluşturur.
     * 
     * @param string $query SQL sorgusu
     * @return \PDOStatement
     */
    public function prepare(string $query): \PDOStatement;
}