<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Advice;

use Framework\Core\Aspects\AbstractAdvice;
use Framework\Core\Aspects\Contracts\AfterReturningAdviceInterface;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * AfterReturningAdvice sınıfı.
 *
 * Hedef metod başarıyla tamamlandıktan sonra çalıştırılan advice tipi implementasyonu.
 *
 * @package Framework\Core\Aspects
 * @subpackage Advice
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AfterReturningAdvice extends AbstractAdvice implements AfterReturningAdviceInterface
{
    /**
     * After returning advice metodunu içeren callback.
     *
     * @var callable
     */
    protected $afterReturningCallback;

    /**
     * Constructor.
     *
     * @param AspectInterface $aspect Bu advice'ın bağlı olduğu aspect
     * @param PointcutInterface $pointcut Bu advice'ın bağlı olduğu pointcut
     * @param callable $afterReturningCallback After returning advice metodunu içeren callback
     * @param int|null $priority Advice önceliği (null ise aspect önceliği kullanılır)
     */
    public function __construct(
        AspectInterface $aspect,
        PointcutInterface $pointcut,
        callable $afterReturningCallback,
        ?int $priority = null
    ) {
        parent::__construct($aspect, $pointcut, $priority);
        $this->afterReturningCallback = $afterReturningCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'afterReturning';
    }

    /**
     * {@inheritdoc}
     */
    public function afterReturning(JoinPointInterface $joinPoint, mixed $result): mixed
    {
        return call_user_func($this->afterReturningCallback, $joinPoint, $result);
    }
}