<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Advice;

use Framework\Core\Aspects\AbstractAdvice;
use Framework\Core\Aspects\Contracts\AroundAdviceInterface;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * AroundAdvice sınıfı.
 *
 * Hedef metodu tamamen saran ve kontrol eden advice tipi implementasyonu.
 *
 * @package Framework\Core\Aspects
 * @subpackage Advice
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AroundAdvice extends AbstractAdvice implements AroundAdviceInterface
{
    /**
     * Around advice metodunu içeren callback.
     *
     * @var callable
     */
    protected $aroundCallback;

    /**
     * Constructor.
     *
     * @param AspectInterface $aspect Bu advice'ın bağlı olduğu aspect
     * @param PointcutInterface $pointcut Bu advice'ın bağlı olduğu pointcut
     * @param callable $aroundCallback Around advice metodunu içeren callback
     * @param int|null $priority Advice önceliği (null ise aspect önceliği kullanılır)
     */
    public function __construct(
        AspectInterface $aspect,
        PointcutInterface $pointcut,
        callable $aroundCallback,
        ?int $priority = null
    ) {
        parent::__construct($aspect, $pointcut, $priority);
        $this->aroundCallback = $aroundCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'around';
    }

    /**
     * {@inheritdoc}
     */
    public function around(JoinPointInterface $joinPoint): mixed
    {
        return call_user_func($this->aroundCallback, $joinPoint);
    }
}