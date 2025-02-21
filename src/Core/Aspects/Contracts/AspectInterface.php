<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * Aspect arayüzü.
 *
 * Bir sınıfın Aspect olarak işlev görmesi için uygulaması gereken temel arayüz.
 * Aspect, "cross-cutting concerns" için tanımlanan ve metod çağrılarını dinleyen özelliktir.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AspectInterface
{
    /**
     * Bu aspect'in uygulanacağı pointcut'ları döndürür.
     *
     * @return PointcutInterface[] Pointcut listesi
     */
    public function getPointcuts(): array;

    /**
     * Bu aspect'in uygulama önceliğini döndürür.
     * Daha düşük sayılar, daha yüksek önceliği gösterir.
     *
     * @return int Öncelik değeri
     */
    public function getPriority(): int;

    /**
     * Bu aspect'in benzersiz kimliğini döndürür.
     *
     * @return string Aspect kimliği
     */
    public function getId(): string;
}