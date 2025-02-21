<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Contracts;

/**
 * Query arayüzü.
 *
 * Bu arayüz, tüm Query nesnelerinin uygulaması gereken temel metotları tanımlar.
 * Query nesneleri, sistemden veri çekmek için kullanılır (okuma işlemleri).
 *
 * @package Framework\Core\CQRS
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface QueryInterface
{
    /**
     * Query tipini döndürür.
     * Bu, Query'nin benzersiz tanımlayıcısıdır.
     *
     * @return string Query tipi
     */
    public function getType(): string;

    /**
     * Query'nin parametrelerini döndürür.
     *
     * @return array<string, mixed> Query parametreleri
     */
    public function getParameters(): array;

    /**
     * Query parametrelerinin validation kurallarını döndürür.
     *
     * @return array<string, string|array> Validation kuralları
     */
    public function validationRules(): array;
}