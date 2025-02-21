<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Contracts;

/**
 * Query Handler arayüzü.
 *
 * Bu arayüz, tüm Query Handler'ların uygulaması gereken temel metotları tanımlar.
 * Query Handler'lar, Query'leri işleyerek sistemden veri çekerler.
 *
 * @package Framework\Core\CQRS
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T of QueryInterface
 * @template R
 */
interface QueryHandlerInterface
{
    /**
     * Query'i işler ve sonucu döndürür.
     *
     * @param QueryInterface $query İşlenecek query
     * @return mixed İşlem sonucu
     * @throws \Exception Query işleme hatası durumunda
     *
     * @phpstan-param T $query
     * @phpstan-return R
     */
    public function handle(QueryInterface $query): mixed;

    /**
     * Bu handler'ın işleyebileceği Query tipini döndürür.
     *
     * @return string Query tipi
     *
     * @phpstan-return class-string<T>
     */
    public static function getQueryType(): string;

    /**
     * Query'nin işlenebilir olup olmadığını kontrol eder.
     *
     * @param QueryInterface $query Kontrol edilecek query
     * @return bool Query işlenebilirse true
     *
     * @phpstan-param T $query
     */
    public function canHandle(QueryInterface $query): bool;
}