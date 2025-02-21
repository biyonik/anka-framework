<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * Advice arayüzü.
 *
 * Advice, bir aspect'in bir join point'te nasıl uygulanacağını tanımlar.
 * Before, After, Around gibi çeşitli advice tipleri vardır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AdviceInterface
{
    /**
     * Advice tipini döndürür.
     *
     * @return string Advice tipi
     */
    public function getType(): string;

    /**
     * Advice'ın önceliğini döndürür.
     * Daha düşük sayılar, daha yüksek önceliği gösterir.
     *
     * @return int Öncelik değeri
     */
    public function getPriority(): int;

    /**
     * Bu advice'ın hangi pointcut ile ilişkili olduğunu döndürür.
     *
     * @return PointcutInterface Pointcut
     */
    public function getPointcut(): PointcutInterface;

    /**
     * Bu advice'ın hangi aspect ile ilişkili olduğunu döndürür.
     *
     * @return AspectInterface Aspect
     */
    public function getAspect(): AspectInterface;
}