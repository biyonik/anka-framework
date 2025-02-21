<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence;

use Framework\Infrastructure\Persistence\Contracts\ConnectionManagerInterface;
use Framework\Infrastructure\Persistence\Contracts\QueryBuilderInterface;

/**
 * QueryBuilder fabrika sınıfı.
 *
 * Bu sınıf, QueryBuilder nesnelerini oluşturmak için kullanılır.
 * Factory pattern uygulaması ile QueryBuilder'ların yaratılmasını soyutlar.
 *
 * @package Framework\Infrastructure\Persistence
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class QueryBuilderFactory
{
    /**
     * Yeni bir QueryBuilder nesnesi oluşturur.
     *
     * @param ConnectionManagerInterface $connectionManager Veritabanı bağlantı yöneticisi
     * @return QueryBuilderInterface Oluşturulan QueryBuilder nesnesi
     */
    public function create(ConnectionManagerInterface $connectionManager): QueryBuilderInterface
    {
        return new QueryBuilder($connectionManager);
    }
}