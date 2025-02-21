<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\PointcutInterface;

/**
 * Temel Aspect sınıfı.
 *
 * Tüm aspect'ler için temel fonksiyonaliteyi içeren soyut sınıf.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractAspect implements AspectInterface
{
    /**
     * Aspect'in benzersiz kimliği.
     *
     * @var string
     */
    protected string $id;

    /**
     * Aspect önceliği.
     * Daha düşük sayılar, daha yüksek önceliği gösterir.
     *
     * @var int
     */
    protected int $priority = 10;

    /**
     * Bu aspect'e bağlı pointcut'lar.
     *
     * @var PointcutInterface[]
     */
    protected array $pointcuts = [];

    /**
     * Constructor.
     *
     * @param string|null $id Aspect kimliği (null ise sınıf adı kullanılır)
     * @param int $priority Aspect önceliği
     */
    public function __construct(?string $id = null, int $priority = 10)
    {
        $this->id = $id ?? get_class($this);
        $this->priority = $priority;
        $this->init();
    }

    /**
     * Aspect'i başlatır. Alt sınıflar bu metodu override edebilir.
     *
     * @return void
     */
    protected function init(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getPointcuts(): array
    {
        return $this->pointcuts;
    }

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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Bu aspect'e bir pointcut ekler.
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
     * Aspect önceliğini ayarlar.
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