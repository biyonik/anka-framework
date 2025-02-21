<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * Pointcut arayüzü.
 *
 * Pointcut, aspect'lerin uygulanacağı kod noktalarını (metotlar, sınıflar vs.) tanımlar.
 * Method çağrıları, belirli sınıflar veya attribute'lar gibi kriterlere göre eşleştirme yapar.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @version 1.0.0
 * @since 1.0.0
 */
interface PointcutInterface
{
    /**
     * Belirtilen metot için pointcut'ın eşleşip eşleşmediğini kontrol eder.
     *
     * @param \ReflectionMethod $method Kontrol edilecek metot
     * @param object|null $instance Metod sahibi örnek (null olabilir - statik metod durumunda)
     * @return bool Eşleşme durumu
     */
    public function matches(\ReflectionMethod $method, ?object $instance = null): bool;

    /**
     * Bir pointcut ifadesini parse eder ve bu pointcut'a uygular.
     *
     * @param string $expression Pointcut ifadesi
     * @return self Akıcı arayüz için
     */
    public function parse(string $expression): self;

    /**
     * Bu pointcut'ın önceliğini döndürür.
     * Daha düşük sayılar, daha yüksek önceliği gösterir.
     *
     * @return int Öncelik değeri
     */
    public function getPriority(): int;

    /**
     * Pointcut'ın adını döndürür.
     *
     * @return string Pointcut adı
     */
    public function getName(): string;
}