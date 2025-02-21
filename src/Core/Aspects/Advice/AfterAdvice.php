<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Advice;

use Framework\Core\Aspects\AbstractAdvice;
use Framework\Core\Aspects\Contracts\AfterAdviceInterface;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * AfterAdvice sınıfı.
 *
 * Hedef metod çalıştırıldıktan sonra (başarılı veya başarısız) çalıştırılan advice tipi implementasyonu.
 *
 * @package Framework\Core\Aspects
 * @subpackage Advice
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AfterAdvice extends AbstractAdvice implements AfterAdviceInterface
{
    /**
     * After advice metodunu içeren callback.
     *
     * @var callable
     */
    protected $afterCallback;

    /**
     * Constructor.
     *
     * @param AspectInterface $aspect Bu advice'ın bağlı olduğu aspect
     * @param PointcutInterface $pointcut Bu advice'ın bağlı olduğu pointcut
     * @param callable $afterCallback After advice metodunu içeren callback
     * @param int|null $priority Advice önceliği (null ise aspect önceliği kullanılır)
     */
    public function __construct(
        AspectInterface $aspect,
        PointcutInterface $pointcut,
        callable $afterCallback,
        ?int $priority = null
    ) {
        parent::__construct($aspect, $pointcut, $priority);
        $this->afterCallback = $afterCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'after';
    }

    /**
     * {@inheritdoc}
     */
    public function after(JoinPointInterface $joinPoint): void
    {
        call_user_func($this->afterCallback, $joinPoint);
    }
}