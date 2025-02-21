<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * BeforeAdvice arayüzü.
 *
 * Hedef metod çağrılmadan önce çalıştırılan advice tipi.
 * Method parametrelerini değiştirebilir veya ek işlemler yapabilir.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface BeforeAdviceInterface extends AdviceInterface
{
    /**
     * Hedef metod çağrılmadan önce çalıştırılır.
     *
     * @param JoinPointInterface $joinPoint Join point bilgisi
     * @return void
     */
    public function before(JoinPointInterface $joinPoint): void;
}