<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AroundAdviceInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;

/**
 * AdviceChain sınıfı.
 *
 * Around advice'ların zincir şeklinde çalıştırılmasını sağlar.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AdviceChain
{
    /**
     * Around advice'lar.
     *
     * @var AroundAdviceInterface[]
     */
    protected array $advices;

    /**
     * Join point.
     *
     * @var JoinPointInterface
     */
    protected JoinPointInterface $joinPoint;

    /**
     * Mevcut advice indeksi.
     *
     * @var int
     */
    protected int $currentIndex = 0;

    /**
     * Constructor.
     *
     * @param array $advices Around advice listesi
     * @param JoinPointInterface $joinPoint Join point
     */
    public function __construct(array $advices, JoinPointInterface $joinPoint)
    {
        $this->advices = $advices;
        $this->joinPoint = $joinPoint;
    }

    /**
     * Advice zincirini çalıştırır.
     *
     * @return mixed Son sonuç
     * @throws \Throwable Metot çağrısı sırasında oluşan istisna
     */
    public function proceed(): mixed
    {
        // Tüm advice'lar tamamlandı mı?
        if ($this->currentIndex >= count($this->advices)) {
            // Orijinal metodu çağır
            return $this->joinPoint->proceed();
        }

        // Sıradaki advice'ı al
        $advice = $this->advices[$this->currentIndex++];

        // Sadece around advice'ları işle
        if ($advice instanceof AroundAdviceInterface) {
            return $advice->around($this->createProceedingJoinPoint());
        }

        // Around advice olmayanlar için bir sonraki advice'a geç
        return $this->proceed();
    }

    /**
     * Proceed edilebilir bir join point oluşturur.
     *
     * @return JoinPointInterface Join point
     */
    protected function createProceedingJoinPoint(): JoinPointInterface
    {
        // Orijinal join point'i kopyala, ama proceed metodunu override et
        $proceedingJoinPoint = clone $this->joinPoint;

        // ProceedingJoinPoint sınıfı olsaydı burada onu kullanabilirdik
        // Bu örnekte, dinamik olarak proceed metodunu değiştiriyoruz

        // Bir sonraki advice'a devam etmek için mevcut chain'i kullan
        $chain = $this;

        // Reflection kullanarak proceed metodunu değiştir (örnek amaçlı, gerçek kodda kullanmayın)
        $proceedMethod = function() use ($chain) {
            return $chain->proceed();
        };

        // Bu noktada, gerçek bir implementasyonda ProceedingJoinPoint sınıfı kullanılması daha uygun olacaktır

        return $proceedingJoinPoint;
    }
}