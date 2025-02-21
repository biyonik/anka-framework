<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * AfterAdvice arayüzü.
 *
 * Hedef metod çalıştırıldıktan sonra (başarılı veya başarısız) çalıştırılan advice tipi.
 * Metod tamamlandıktan sonra yapılması gereken temizleme gibi işlemler için kullanılır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AfterAdviceInterface extends AdviceInterface
{
    /**
     * Hedef metod çalıştırıldıktan sonra (başarılı veya başarısız) çalıştırılır.
     *
     * @param JoinPointInterface $joinPoint Join point bilgisi
     * @return void
     */
    public function after(JoinPointInterface $joinPoint): void;
}