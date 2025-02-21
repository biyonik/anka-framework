<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Domain Repository arayüzü.
 *
 * Repository'lerin uygulaması gereken temel arayüz.
 * Repository'ler, aggregate root'ları kalıcı depodan yükleme ve kaydetme işlemlerini yönetir.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T of AggregateRootInterface
 */
interface DomainRepositoryInterface
{
    /**
     * Aggregate root'u ID'sine göre yükler.
     *
     * @param mixed $id Aggregate ID
     * @return T|null Bulunan aggregate root veya null
     */
    public function findById(mixed $id): ?AggregateRootInterface;

    /**
     * Aggregate root'u kaydeder.
     *
     * @param T $aggregate Kaydedilecek aggregate root
     * @return self Akıcı arayüz için
     */
    public function save(AggregateRootInterface $aggregate): self;

    /**
     * Aggregate root'u siler.
     *
     * @param T $aggregate Silinecek aggregate root
     * @return self Akıcı arayüz için
     */
    public function delete(AggregateRootInterface $aggregate): self;

    /**
     * Repository için uygun ID üretir.
     *
     * @return mixed Yeni ID
     */
    public function nextIdentity(): mixed;
}