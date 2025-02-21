<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Pointcut;

use Framework\Core\Aspects\AbstractPointcut;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * CompositePointcut sınıfı.
 *
 * Birden fazla pointcut'ı birleştiren pointcut.
 *
 * @package Framework\Core\Aspects
 * @subpackage Pointcut
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class CompositePointcut extends AbstractPointcut
{
    /**
     * Birleştirilmiş pointcut'lar.
     *
     * @var PointcutInterface[]
     */
    protected array $pointcuts = [];

    /**
     * AND operatörü kullanılsın mı?
     * true: Tüm pointcut'lar eşleşmeli (AND)
     * false: Herhangi bir pointcut eşleşmeli (OR)
     *
     * @var bool
     */
    protected bool $useAnd = true;

    /**
     * Constructor.
     *
     * @param array $pointcuts Birleştirilecek pointcut'lar
     * @param bool $useAnd AND operatörü kullanılsın mı?
     * @param string|null $name Pointcut adı
     * @param int $priority Pointcut önceliği
     */
    public function __construct(array $pointcuts = [], bool $useAnd = true, ?string $name = null, int $priority = 10)
    {
        parent::__construct($name, null, $priority);
        $this->pointcuts = $pointcuts;
        $this->useAnd = $useAnd;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(\ReflectionMethod $method, ?object $instance = null): bool
    {
        if (empty($this->pointcuts)) {
            return false;
        }

        if ($this->useAnd) {
            // AND: Tüm pointcut'lar eşleşmeli
            foreach ($this->pointcuts as $pointcut) {
                if (!$pointcut->matches($method, $instance)) {
                    return false;
                }
            }
            return true;
        } else {
            // OR: Herhangi bir pointcut eşleşmeli
            foreach ($this->pointcuts as $pointcut) {
                if ($pointcut->matches($method, $instance)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $expression): self
    {
        // Bu pointcut için parse işlemi desteklenmiyor
        $this->expression = $expression;
        return $this;
    }

    /**
     * Bir pointcut ekler.
     *
     * @param PointcutInterface $pointcut Eklenecek pointcut
     * @return self Akıcı arayüz için
     */
    public function addPointcut(PointcutInterface $pointcut): self
    {
        $this->pointcuts[] = $pointcut;
        return $this;
    }

    /**
     * Birden fazla pointcut ekler.
     *
     * @param array $pointcuts Eklenecek pointcut'lar
     * @return self Akıcı arayüz için
     */
    public function addPointcuts(array $pointcuts): self
    {
        foreach ($pointcuts as $pointcut) {
            if ($pointcut instanceof PointcutInterface) {
                $this->pointcuts[] = $pointcut;
            }
        }
        return $this;
    }

    /**
     * Tüm pointcut'ları döndürür.
     *
     * @return PointcutInterface[] Pointcut listesi
     */
    public function getPointcuts(): array
    {
        return $this->pointcuts;
    }

    /**
     * Birleştirme türünü ayarlar (AND veya OR).
     *
     * @param bool $useAnd true: AND, false: OR
     * @return self Akıcı arayüz için
     */
    public function setUseAnd(bool $useAnd): self
    {
        $this->useAnd = $useAnd;
        return $this;
    }

    /**
     * Birleştirme türünü döndürür.
     *
     * @return bool true: AND, false: OR
     */
    public function isUseAnd(): bool
    {
        return $this->useAnd;
    }
}