<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Advice;

use Framework\Core\Aspects\AbstractAdvice;
use Framework\Core\Aspects\Contracts\AfterThrowingAdviceInterface;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * AfterThrowingAdvice sınıfı.
 *
 * Hedef metod bir istisna fırlattığında çalıştırılan advice tipi implementasyonu.
 *
 * @package Framework\Core\Aspects
 * @subpackage Advice
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AfterThrowingAdvice extends AbstractAdvice implements AfterThrowingAdviceInterface
{
    /**
     * After throwing advice metodunu içeren callback.
     *
     * @var callable
     */
    protected $afterThrowingCallback;

    /**
     * Constructor.
     *
     * @param AspectInterface $aspect Bu advice'ın bağlı olduğu aspect
     * @param PointcutInterface $pointcut Bu advice'ın bağlı olduğu pointcut
     * @param callable $afterThrowingCallback After throwing advice metodunu içeren callback
     * @param int|null $priority Advice önceliği (null ise aspect önceliği kullanılır)
     */
    public function __construct(
        AspectInterface $aspect,
        PointcutInterface $pointcut,
        callable $afterThrowingCallback,
        ?int $priority = null
    ) {
        parent::__construct($aspect, $pointcut, $priority);
        $this->afterThrowingCallback = $afterThrowingCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'afterThrowing';
    }

    /**
     * {@inheritdoc}
     */
    public function afterThrowing(JoinPointInterface $joinPoint, \Throwable $exception): \Throwable
    {
        return call_user_func($this->afterThrowingCallback, $joinPoint, $exception);
    }
}