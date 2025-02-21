<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AdviceInterface;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * Temel Advice sınıfı.
 *
 * Tüm advice'lar için temel fonksiyonaliteyi içeren soyut sınıf.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractAdvice implements AdviceInterface
{
    /**
     * Bu advice'ın bağlı olduğu aspect.
     *
     * @var AspectInterface
     */
    protected AspectInterface $aspect;

    /**
     * Bu advice'ın bağlı olduğu pointcut.
     *
     * @var PointcutInterface
     */
    protected PointcutInterface $pointcut;

    /**
     * Advice önceliği.
     * Daha düşük sayılar, daha yüksek önceliği gösterir.
     *
     * @var int
     */
    protected int $priority = 10;

    /**
     * Constructor.
     *
     * @param AspectInterface $aspect Bu advice'ın bağlı olduğu aspect
     * @param PointcutInterface $pointcut Bu advice'ın bağlı olduğu pointcut
     * @param int|null $priority Advice önceliği (null ise aspect önceliği kullanılır)
     */
    public function __construct(AspectInterface $aspect, PointcutInterface $pointcut, ?int $priority = null)
    {
        $this->aspect = $aspect;
        $this->pointcut = $pointcut;
        $this->priority = $priority ?? $aspect->getPriority();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getType(): string;

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPointcut(): PointcutInterface
    {
        return $this->pointcut;
    }

    /**
     * {@inheritdoc}
     */
    public function getAspect(): AspectInterface
    {
        return $this->aspect;
    }

    /**
     * Advice önceliğini ayarlar.
     *
     * @param int $priority Yeni öncelik değeri
     * @return self Akıcı arayüz için
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }
}