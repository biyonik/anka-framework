<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * AroundAdvice arayüzü.
 *
 * Hedef metodu tamamen saran ve kontrol eden advice tipi.
 * Metodun çağrılıp çağrılmayacağını kontrol edebilir, öncesinde ve sonrasında işlemler yapabilir.
 * En güçlü advice tipidir, diğer tüm advice tiplerinin yerine kullanılabilir.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AroundAdviceInterface extends AdviceInterface
{
    /**
     * Hedef metodu sararak çalıştırır.
     *
     * @param JoinPointInterface $joinPoint Join point bilgisi
     * @return mixed Değiştirilmiş veya orijinal metod sonucu
     * @throws \Throwable Metod çağrısı sırasında oluşan istisna
     */
    public function around(JoinPointInterface $joinPoint): mixed;
}