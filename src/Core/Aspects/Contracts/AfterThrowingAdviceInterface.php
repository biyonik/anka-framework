<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * AfterThrowingAdvice arayüzü.
 *
 * Hedef metod bir istisna fırlattığında çalıştırılan advice tipi.
 * İstisna işleme, loglama veya istisnayı değiştirme işlemleri yapabilir.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AfterThrowingAdviceInterface extends AdviceInterface
{
    /**
     * Hedef metod bir istisna fırlattığında çalıştırılır.
     *
     * @param JoinPointInterface $joinPoint Join point bilgisi
     * @param \Throwable $exception Fırlatılan istisna
     * @return \Throwable Değiştirilmiş veya aynı istisna
     */
    public function afterThrowing(JoinPointInterface $joinPoint, \Throwable $exception): \Throwable;
}