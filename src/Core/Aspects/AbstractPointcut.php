<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * Temel Pointcut sınıfı.
 *
 * Tüm pointcut'lar için temel fonksiyonaliteyi içeren soyut sınıf.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractPointcut implements PointcutInterface
{
    /**
     * Pointcut adı.
     *
     * @var string
     */
    protected string $name;

    /**
     * Pointcut önceliği.
     * Daha düşük sayılar, daha yüksek önceliği gösterir.
     *
     * @var int
     */
    protected int $priority = 10;

    /**
     * Pointcut ifadesi.
     *
     * @var string|null
     */
    protected ?string $expression = null;

    /**
     * Constructor.
     *
     * @param string|null $name Pointcut adı (null ise sınıf adı kullanılır)
     * @param string|null $expression Pointcut ifadesi (null ise parse() kullanılmalıdır)
     * @param int $priority Pointcut önceliği
     */
    public function __construct(?string $name = null, ?string $expression = null, int $priority = 10)
    {
        $this->name = $name ?? get_class($this);
        $this->priority = $priority;

        if ($expression !== null) {
            $this->parse($expression);
        }
    }

    /**
     * {@inheritdoc}
     */
    abstract public function matches(\ReflectionMethod $method, ?object $instance = null): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function parse(string $expression): self;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Pointcut önceliğini ayarlar.
     *
     * @param int $priority Yeni öncelik değeri
     * @return self Akıcı arayüz için
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Pointcut adını ayarlar.
     *
     * @param string $name Yeni pointcut adı
     * @return self Akıcı arayüz için
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Pointcut ifadesini döndürür.
     *
     * @return string|null Pointcut ifadesi
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }
}