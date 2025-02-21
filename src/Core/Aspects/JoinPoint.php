<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\JoinPointInterface;

/**
 * JoinPoint sınıfı.
 *
 * Metod çağrısı sırasında oluşturulan ve çağrı bilgilerini içeren sınıf.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class JoinPoint implements JoinPointInterface
{
    /**
     * Hedef metot yansıması.
     *
     * @var \ReflectionMethod
     */
    protected \ReflectionMethod $method;

    /**
     * Hedef nesne.
     *
     * @var object|null
     */
    protected ?object $target;

    /**
     * Metot parametreleri.
     *
     * @var array
     */
    protected array $arguments;

    /**
     * Constructor.
     *
     * @param \ReflectionMethod $method Hedef metot yansıması
     * @param object|null $target Hedef nesne (statik metotlar için null olabilir)
     * @param array $arguments Metot parametreleri
     */
    public function __construct(\ReflectionMethod $method, ?object $target, array $arguments)
    {
        $this->method = $method;
        $this->target = $target;
        $this->arguments = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): \ReflectionMethod
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget(): ?object
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function proceed(): mixed
    {
        if ($this->method->isStatic()) {
            return $this->method->invokeArgs(null, $this->arguments);
        }

        if ($this->target === null) {
            throw new \RuntimeException('Target object is required for non-static methods');
        }

        return $this->method->invokeArgs($this->target, $this->arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->method->getDeclaringClass()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodName(): string
    {
        return $this->method->getName();
    }
}