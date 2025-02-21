<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * Aspect Registry arayüzü.
 *
 * Aspect'leri kaydeden, bulan ve yöneten merkezi registry.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface AspectRegistryInterface
{
    /**
     * Bir aspect'i kaydeder.
     *
     * @param AspectInterface $aspect Kaydedilecek aspect
     * @return self Akıcı arayüz için
     */
    public function register(AspectInterface $aspect): self;

    /**
     * Bir aspect'i ID'sine göre bulur.
     *
     * @param string $id Aspect ID'si
     * @return AspectInterface|null Bulunan aspect veya null
     */
    public function getAspect(string $id): ?AspectInterface;

    /**
     * Tüm kayıtlı aspect'leri döndürür.
     *
     * @return AspectInterface[] Aspect listesi
     */
    public function getAllAspects(): array;

    /**
     * Belirtilen metod için uygulanabilir aspect'leri bulur.
     *
     * @param \ReflectionMethod $method Metot yansıması
     * @param object|null $instance Metot sahibi örneği
     * @return AspectInterface[] Uygulanabilir aspect listesi
     */
    public function findMatchingAspects(\ReflectionMethod $method, ?object $instance = null): array;

    /**
     * Belirli bir metot için uygulanabilir advice'ları bulur.
     *
     * @param \ReflectionMethod $method Metot yansıması
     * @param object|null $instance Metot sahibi örneği
     * @param string|null $adviceType Belirli bir advice tipi (null ise tüm tipler)
     * @return AdviceInterface[] Advice listesi
     */
    public function findMatchingAdvices(\ReflectionMethod $method, ?object $instance = null, ?string $adviceType = null): array;

    /**
     * Bir aspect'i registry'den kaldırır.
     *
     * @param string $id Aspect ID'si
     * @return bool Kaldırma başarılı ise true
     */
    public function removeAspect(string $id): bool;

    /**
     * Registry'i temizler.
     *
     * @return void
     */
    public function clear(): void;
}