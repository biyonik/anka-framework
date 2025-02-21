<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Domain Service arayüzü.
 *
 * Domain service'lerin uygulaması gereken temel arayüz.
 * Domain service'ler, birden fazla entity/aggregate ile çalışan veya entity'lere ait olmayan domain mantığını içerir.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface DomainServiceInterface
{
    /**
     * Service'in domain'ini döndürür.
     *
     * @return string Domain adı
     */
    public function getDomain(): string;
}