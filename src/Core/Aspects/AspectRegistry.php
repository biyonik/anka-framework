<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AdviceInterface;
use Framework\Core\Aspects\Contracts\AspectInterface;
use Framework\Core\Aspects\Contracts\AspectRegistryInterface;

/**
 * AspectRegistry sınıfı.
 *
 * Aspect'leri kaydeden, bulan ve yöneten merkezi registry.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AspectRegistry implements AspectRegistryInterface
{
    /**
     * Kayıtlı aspect'ler.
     *
     * @var array<string, AspectInterface>
     */
    protected array $aspects = [];

    /**
     * Advice tipleri.
     *
     * @var array<string>
     */
    protected const ADVICE_TYPES = ['before', 'afterReturning', 'afterThrowing', 'after', 'around'];

    /**
     * Advice önbelleği.
     * Metod bazında önbelleklenen advice'lar.
     *
     * @var array<string, array<string, array<AdviceInterface>>>
     */
    protected array $adviceCache = [];

    /**
     * {@inheritdoc}
     */
    public function register(AspectInterface $aspect): self
    {
        $this->aspects[$aspect->getId()] = $aspect;
        $this->clearAdviceCache();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAspect(string $id): ?AspectInterface
    {
        return $this->aspects[$id] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAspects(): array
    {
        return array_values($this->aspects);
    }

    /**
     * {@inheritdoc}
     */
    public function findMatchingAspects(\ReflectionMethod $method, ?object $instance = null): array
    {
        $matchingAspects = [];

        foreach ($this->aspects as $aspect) {
            foreach ($aspect->getPointcuts() as $pointcut) {
                if ($pointcut->matches($method, $instance)) {
                    $matchingAspects[] = $aspect;
                    break; // Bir aspect için bir pointcut eşleşmesi yeterli
                }
            }
        }

        return $matchingAspects;
    }

    /**
     * {@inheritdoc}
     */
    public function findMatchingAdvices(\ReflectionMethod $method, ?object $instance = null, ?string $adviceType = null): array
    {
        $cacheKey = $this->getCacheKey($method, $instance);

        // Önbellekte var mı?
        if (isset($this->adviceCache[$cacheKey])) {
            if ($adviceType !== null) {
                return $this->adviceCache[$cacheKey][$adviceType] ?? [];
            }

            // Tüm advice tipleri için birleştir
            $allAdvices = [];
            foreach (self::ADVICE_TYPES as $type) {
                if (isset($this->adviceCache[$cacheKey][$type])) {
                    $allAdvices = array_merge($allAdvices, $this->adviceCache[$cacheKey][$type]);
                }
            }

            return $allAdvices;
        }

        // Önbellekte yoksa, eşleşen advice'ları bul
        $matchingAspects = $this->findMatchingAspects($method, $instance);
        $this->adviceCache[$cacheKey] = [];

        foreach ($matchingAspects as $aspect) {
            foreach ($aspect->getPointcuts() as $pointcut) {
                if (!$pointcut->matches($method, $instance)) {
                    continue;
                }

                // Bu aspect ve pointcut için advice'ları bul
                $this->findAndCacheAdvicesForAspect($aspect, $pointcut, $cacheKey);
            }
        }

        // İstenen tipi döndür
        if ($adviceType !== null) {
            return $this->adviceCache[$cacheKey][$adviceType] ?? [];
        }

        // Tüm advice tipleri için birleştir
        $allAdvices = [];
        foreach (self::ADVICE_TYPES as $type) {
            if (isset($this->adviceCache[$cacheKey][$type])) {
                $allAdvices = array_merge($allAdvices, $this->adviceCache[$cacheKey][$type]);
            }
        }

        return $allAdvices;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAspect(string $id): bool
    {
        if (!isset($this->aspects[$id])) {
            return false;
        }

        unset($this->aspects[$id]);
        $this->clearAdviceCache();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->aspects = [];
        $this->clearAdviceCache();
    }

    /**
     * Advice önbelleğini temizler.
     *
     * @return void
     */
    protected function clearAdviceCache(): void
    {
        $this->adviceCache = [];
    }

    /**
     * Önbellek anahtarı oluşturur.
     *
     * @param \ReflectionMethod $method Metod yansıması
     * @param object|null $instance Metod sahibi örneği
     * @return string Önbellek anahtarı
     */
    protected function getCacheKey(\ReflectionMethod $method, ?object $instance): string
    {
        $className = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        if ($instance !== null) {
            // Instance hash kullanarak benzersiz anahtar oluştur
            return sprintf('%s::%s@%s', $className, $methodName, spl_object_hash($instance));
        }

        return sprintf('%s::%s', $className, $methodName);
    }

    /**
     * Belirli bir aspect ve pointcut için advice'ları bulur ve önbelleğe ekler.
     *
     * @param AspectInterface $aspect Aspect
     * @param \Framework\Core\Aspects\Contracts\PointcutInterface $pointcut Pointcut
     * @param string $cacheKey Önbellek anahtarı
     * @return void
     */
    protected function findAndCacheAdvicesForAspect(
        AspectInterface $aspect,
        \Framework\Core\Aspects\Contracts\PointcutInterface $pointcut,
        string $cacheKey
    ): void {
        // Reflection kullanarak aspect sınıfında advice metotlarını ara
        $reflClass = new \ReflectionClass($aspect);

        foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();

            // Before advice'lar
            if (str_starts_with($methodName, 'before')) {
                $this->cacheAdvice($aspect, $pointcut, 'before', $cacheKey, $method);
            }
            // AfterReturning advice'lar
            elseif (str_starts_with($methodName, 'afterReturning')) {
                $this->cacheAdvice($aspect, $pointcut, 'afterReturning', $cacheKey, $method);
            }
            // AfterThrowing advice'lar
            elseif (str_starts_with($methodName, 'afterThrowing')) {
                $this->cacheAdvice($aspect, $pointcut, 'afterThrowing', $cacheKey, $method);
            }
            // After advice'lar
            elseif (str_starts_with($methodName, 'after') && $methodName !== 'afterReturning' && $methodName !== 'afterThrowing') {
                $this->cacheAdvice($aspect, $pointcut, 'after', $cacheKey, $method);
            }
            // Around advice'lar
            elseif (str_starts_with($methodName, 'around')) {
                $this->cacheAdvice($aspect, $pointcut, 'around', $cacheKey, $method);
            }
        }
    }

    /**
     * Advice'ı oluşturur ve önbelleğe ekler.
     *
     * @param AspectInterface $aspect Aspect
     * @param \Framework\Core\Aspects\Contracts\PointcutInterface $pointcut Pointcut
     * @param string $adviceType Advice tipi
     * @param string $cacheKey Önbellek anahtarı
     * @param \ReflectionMethod $method Advice metodu
     * @return void
     */
    protected function cacheAdvice(
        AspectInterface $aspect,
        \Framework\Core\Aspects\Contracts\PointcutInterface $pointcut,
        string $adviceType,
        string $cacheKey,
        \ReflectionMethod $method
    ): void {
        // Advice sınıfı oluştur
        $adviceClass = sprintf('\\Framework\\Core\\Aspects\\Advice\\%sAdvice', ucfirst($adviceType));

        if (!class_exists($adviceClass)) {
            return;
        }

        $callback = [$aspect, $method->getName()];

        if (!is_callable($callback)) {
            return;
        }

        /** @var AdviceInterface $advice */
        $advice = new $adviceClass($aspect, $pointcut, $callback);

        // Önbelleğe ekle
        if (!isset($this->adviceCache[$cacheKey][$adviceType])) {
            $this->adviceCache[$cacheKey][$adviceType] = [];
        }

        $this->adviceCache[$cacheKey][$adviceType][] = $advice;

        // Önceliğe göre sırala
        usort($this->adviceCache[$cacheKey][$adviceType], function(AdviceInterface $a, AdviceInterface $b) {
            return $a->getPriority() <=> $b->getPriority();
        });
    }
}