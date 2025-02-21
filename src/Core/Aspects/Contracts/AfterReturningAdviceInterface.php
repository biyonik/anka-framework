<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * AfterReturningAdvice arayüzü.
 *
 * Hedef metod başarıyla tamamlandıktan sonra çalıştırılan advice tipi.
 * Metod sonucunu değiştirebilir veya ek işlemler yapabilir.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AfterReturningAdviceInterface extends AdviceInterface
{
    /**
     * Hedef metod başarıyla tamamlandıktan sonra çalıştırılır.
     *
     * @param JoinPointInterface $joinPoint Join point bilgisi
     * @param mixed $result Metod sonucu
     * @return mixed Değiştirilmiş sonuç veya aynı sonuç
     */
    public function afterReturning(JoinPointInterface $joinPoint, mixed $result): mixed;
}