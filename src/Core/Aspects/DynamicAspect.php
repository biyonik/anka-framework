<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AdviceInterface;

/**
 * DynamicAspect sınıfı.
 *
 * Attribute tabanlı aspect oluşturmak için kullanılan dinamik aspect sınıfı.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class DynamicAspect extends AbstractAspect
{
    /**
     * Bu aspect'in advice'ları.
     *
     * @var array<AdviceInterface>
     */
    protected array $advices = [];

    /**
     * Yeni bir advice ekler.
     *
     * @param AdviceInterface $advice Advice
     * @return self Akıcı arayüz için
     */
    public function addAdvice(AdviceInterface $advice): self
    {
        $this->advices[] = $advice;

        // Advice'ın pointcut'ını da aspect'e ekle
        $this->addPointcut($advice->getPointcut());

        return $this;
    }

    /**
     * Aspect ID'sini ayarlar.
     *
     * @param string $id Yeni ID
     * @return self Akıcı arayüz için
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Bu aspect'in advice'larını döndürür.
     *
     * @return array<AdviceInterface> Advice listesi
     */
    public function getAdvices(): array
    {
        return $this->advices;
    }
}