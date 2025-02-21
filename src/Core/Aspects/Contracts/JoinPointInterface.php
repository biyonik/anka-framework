<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Contracts;

/**
 * JoinPoint arayüzü.
 *
 * Join point, aspect'lerin uygulanabileceği program yürütme noktasıdır.
 * Metot çağrısı, istisna fırlatma gibi noktaları temsil eder.
 * Aspect'ler tarafından öncesi, sonrası veya sırasında işlem yapılabilir.
 *
 * @package Framework\Core\Aspects
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface JoinPointInterface
{
    /**
     * Hedef metodun yansımasını (reflection) döndürür.
     *
     * @return \ReflectionMethod Metot yansıması
     */
    public function getMethod(): \ReflectionMethod;

    /**
     * Hedef metot sahibinin örneğini döndürür.
     *
     * @return object|null Metot sahibi örneği (null olabilir - statik metot durumunda)
     */
    public function getTarget(): ?object;

    /**
     * Hedef metodun parametrelerini döndürür.
     *
     * @return array Parametre listesi
     */
    public function getArguments(): array;

    /**
     * Hedef metodun parametrelerini ayarlar.
     *
     * @param array $arguments Yeni parametreler
     * @return self Akıcı arayüz için
     */
    public function setArguments(array $arguments): self;

    /**
     * Hedef metodu çağırır ve sonucunu döndürür.
     *
     * @return mixed Metot sonucu
     * @throws \Throwable Metot çağrısı sırasında oluşan istisna
     */
    public function proceed(): mixed;

    /**
     * Bu join point'in sınıf adını döndürür.
     *
     * @return string Sınıf adı
     */
    public function getClassName(): string;

    /**
     * Bu join point'in metot adını döndürür.
     *
     * @return string Metot adı
     */
    public function getMethodName(): string;
}