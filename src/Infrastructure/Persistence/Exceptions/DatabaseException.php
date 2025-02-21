<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence\Exceptions;

use RuntimeException;

/**
 * Veritabanı işlemleri sırasında oluşan hataları temsil eden exception sınıfı.
 * 
 * Bu sınıf, veritabanı bağlantı hataları, sorgu hataları, 
 * transaction hataları ve diğer veritabanı ilişkili 
 * hataları temsil etmek için kullanılır.
 * 
 * @package Framework\Infrastructure\Persistence
 * @subpackage Exceptions
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class DatabaseException extends RuntimeException
{
    /**
     * SQL sorgusunu döndürür.
     * 
     * @var string|null
     */
    protected ?string $query = null;

    /**
     * Sorgu parametrelerini döndürür.
     * 
     * @var array<mixed>|null
     */
    protected ?array $parameters = null;

    /**
     * SQL sorgusunu ayarlar.
     * 
     * @param string $query SQL sorgusu
     * @return static
     */
    public function setQuery(string $query): static
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Sorgu parametrelerini ayarlar.
     * 
     * @param array<mixed> $parameters Sorgu parametreleri
     * @return static
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * SQL sorgusunu döndürür.
     * 
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * Sorgu parametrelerini döndürür.
     * 
     * @return array<mixed>|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * Bağlantı hatası oluşturur.
     * 
     * @param string $message Hata mesajı
     * @param int $code Hata kodu
     * @param \Throwable|null $previous Önceki hata
     * @return static
     */
    public static function connectionError(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null
    ): static {
        return new static(
            sprintf('Veritabanı bağlantı hatası: %s', $message),
            $code,
            $previous
        );
    }

    /**
     * Sorgu hatası oluşturur.
     * 
     * @param string $message Hata mesajı
     * @param string $query SQL sorgusu
     * @param array<mixed> $parameters Sorgu parametreleri
     * @param int $code Hata kodu
     * @param \Throwable|null $previous Önceki hata
     * @return static
     */
    public static function queryError(
        string $message,
        string $query,
        array $parameters = [],
        int $code = 0,
        ?\Throwable $previous = null
    ): static {
        $exception = new static(
            sprintf('Veritabanı sorgu hatası: %s', $message),
            $code,
            $previous
        );

        return $exception
            ->setQuery($query)
            ->setParameters($parameters);
    }

    /**
     * Transaction hatası oluşturur.
     * 
     * @param string $message Hata mesajı
     * @param int $code Hata kodu
     * @param \Throwable|null $previous Önceki hata
     * @return static
     */
    public static function transactionError(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null
    ): static {
        return new static(
            sprintf('Veritabanı transaction hatası: %s', $message),
            $code,
            $previous
        );
    }
}