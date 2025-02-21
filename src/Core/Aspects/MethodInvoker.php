<?php

declare(strict_types=1);

namespace Framework\Core\Aspects;

use Framework\Core\Aspects\Contracts\AfterAdviceInterface;
use Framework\Core\Aspects\Contracts\AfterReturningAdviceInterface;
use Framework\Core\Aspects\Contracts\AfterThrowingAdviceInterface;
use Framework\Core\Aspects\Contracts\AspectRegistryInterface;
use Framework\Core\Aspects\Contracts\BeforeAdviceInterface;
use Framework\Core\Aspects\Contracts\JoinPointInterface;

/**
 * MethodInvoker sınıfı.
 *
 * Method çağrılarını yakalayıp aspect'leri uygulama mekanizması.
 *
 * @package Framework\Core\Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class MethodInvoker
{
    /**
     * Aspect registry.
     *
     * @var AspectRegistryInterface
     */
    protected AspectRegistryInterface $registry;

    /**
     * Constructor.
     *
     * @param AspectRegistryInterface $registry Aspect registry
     */
    public function __construct(AspectRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Metodu çağırır ve aspect'leri uygular.
     *
     * @param object|string $target Hedef nesne veya sınıf adı (statik metot için)
     * @param string $method Metot adı
     * @param array $args Metot parametreleri
     * @return mixed Metot sonucu
     * @throws \Throwable Metot çağrısı sırasında oluşan istisna
     */
    public function invoke(object|string $target, string $method, array $args = []): mixed
    {
        $className = is_string($target) ? $target : get_class($target);
        $instance = is_string($target) ? null : $target;

        // Metot yansıması oluştur
        try {
            $reflMethod = new \ReflectionMethod($className, $method);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(sprintf('Metot bulunamadı: %s::%s', $className, $method), 0, $e);
        }

        // JoinPoint oluştur
        $joinPoint = new JoinPoint($reflMethod, $instance, $args);

        // Aspect'leri uygula
        return $this->invokeWithAspects($joinPoint);
    }

    /**
     * JoinPoint'i çağırır ve aspect'leri uygular.
     *
     * @param JoinPointInterface $joinPoint Join point
     * @return mixed Metot sonucu
     * @throws \Throwable Metot çağrısı sırasında oluşan istisna
     */
    public function invokeWithAspects(JoinPointInterface $joinPoint): mixed
    {
        $method = $joinPoint->getMethod();
        $instance = $joinPoint->getTarget();

        // Around advice'ları bul
        $aroundAdvices = $this->registry->findMatchingAdvices($method, $instance, 'around');

        if (!empty($aroundAdvices)) {
            // Around advice'ları AdviceChain ile çağır
            $chain = new AdviceChain($aroundAdvices, $joinPoint);
            return $chain->proceed();
        }

        // Before advice'ları bul ve çağır
        $beforeAdvices = $this->registry->findMatchingAdvices($method, $instance, 'before');

        foreach ($beforeAdvices as $advice) {
            if ($advice instanceof BeforeAdviceInterface) {
                $advice->before($joinPoint);
            }
        }

        // Metodu çağır
        try {
            $result = $joinPoint->proceed();

            // AfterReturning advice'ları bul ve çağır
            $afterReturningAdvices = $this->registry->findMatchingAdvices($method, $instance, 'afterReturning');

            foreach ($afterReturningAdvices as $advice) {
                if ($advice instanceof AfterReturningAdviceInterface) {
                    $result = $advice->afterReturning($joinPoint, $result);
                }
            }

            return $result;
        } catch (\Throwable $exception) {
            // AfterThrowing advice'ları bul ve çağır
            $afterThrowingAdvices = $this->registry->findMatchingAdvices($method, $instance, 'afterThrowing');

            foreach ($afterThrowingAdvices as $advice) {
                if ($advice instanceof AfterThrowingAdviceInterface) {
                    $exception = $advice->afterThrowing($joinPoint, $exception);
                }
            }

            throw $exception;
        } finally {
            // After advice'ları bul ve çağır
            $afterAdvices = $this->registry->findMatchingAdvices($method, $instance, 'after');

            foreach ($afterAdvices as $advice) {
                if ($advice instanceof AfterAdviceInterface) {
                    $advice->after($joinPoint);
                }
            }
        }
    }
}