<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Factory arayüzü.
 *
 * Factory'lerin uygulaması gereken temel arayüz.
 * Factory'ler, karmaşık domain nesnelerinin oluşturulması için kullanılır.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 */
interface FactoryInterface
{
    /**
     * Yeni bir domain nesnesi oluşturur.
     *
     * @param array<string, mixed> $data Nesne verileri
     * @return T Oluşturulan nesne
     */
    public function create(array $data = []): mixed;

    /**
     * Mevcut bir domain nesnesini yeniden oluşturur.
     *
     * @param array<string, mixed> $data Nesne verileri
     * @return T Oluşturulan nesne
     */
    public function reconstitute(array $data = []): mixed;
}