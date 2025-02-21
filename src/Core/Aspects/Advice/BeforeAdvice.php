<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Advice;

use Framework\Core\Aspects\AbstractAdvice;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\BeforeAdviceInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * BeforeAdvice sınıfı.
 *
 * Hedef metod çağrılmadan önce çalıştırılan advice tipi implementasyonu.
 *
 * @package Framework\Core\Aspects
 * @subpackage Advice
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class BeforeAdvice extends AbstractAdvice implements BeforeAdviceInterface
{
    /**
     * Before advice metodunu içeren callback.
     *
     * @var callable
     */
    protected $beforeCallback;

    /**
     * Constructor.
     *
     * @param AspectInterface $aspect Bu advice'ın bağlı olduğu aspect
     * @param PointcutInterface $pointcut Bu advice'ın bağlı olduğu pointcut
     * @param callable $beforeCallback Before advice metodunu içeren callback
     * @param int|null $priority Advice önceliği (null ise aspect önceliği kullanılır)
     */
    public function __construct(
        AspectInterface $aspect,
        PointcutInterface $pointcut,
        callable $beforeCallback,
        ?int $priority = null
    ) {
        parent::__construct($aspect, $pointcut, $priority);
        $this->beforeCallback = $beforeCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'before';
    }

    /**
     * {@inheritdoc}
     */
    public function before(JoinPointInterface $joinPoint): void
    {
        call_user_func($this->beforeCallback, $joinPoint);
    }
}