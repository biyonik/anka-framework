<?php

declare(strict_types=1);

namespace Framework\Core\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Container işlemleri sırasında oluşabilecek genel hataları temsil eden exception sınıfı.
 * 
 * Bu sınıf, DI Container'ın çalışması sırasında oluşabilecek çeşitli hata durumlarını
 * yönetmek için kullanılır. PSR-11 ContainerExceptionInterface'ini implemente eder.
 * 
 * Kullanım Durumları:
 * - Döngüsel bağımlılık tespiti
 * - Geçersiz binding tanımlamaları
 * - Servis oluşturma hataları
 * - Autowiring hataları
 * - Reflection hataları
 * 
 * @package Framework\Core\Container
 * @subpackage Exceptions
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * throw new ContainerException(
 *     "Döngüsel bağımlılık tespit edildi: Service1 -> Service2 -> Service1"
 * );
 * ```
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    /**
     * Döngüsel bağımlılık hatası için özel bir exception oluşturur.
     * 
     * @param string[] $cycle Döngüsel bağımlılığı oluşturan sınıfların listesi
     * @return static
     */
    public static function circularDependency(array $cycle): static
    {
        return new static(sprintf(
            'Döngüsel bağımlılık tespit edildi: %s',
            implode(' -> ', $cycle)
        ));
    }

    /**
     * Geçersiz binding tanımı için özel bir exception oluşturur.
     * 
     * @param string $abstract Interface veya abstract class adı
     * @param string $concrete Concrete class adı
     * @return static
     */
    public static function invalidBinding(string $abstract, string $concrete): static
    {
        return new static(sprintf(
            "'%s' sınıfı '%s' interface/abstract'ını implemente etmiyor.",
            $concrete,
            $abstract
        ));
    }

    /**
     * Autowiring hatası için özel bir exception oluşturur.
     * 
     * @param string $class Çözümlenemeyen sınıf adı
     * @param string $parameter Çözümlenemeyen parametre adı
     * @return static
     */
    public static function autowireFailed(string $class, string $parameter): static
    {
        return new static(sprintf(
            "'%s' sınıfının '%s' parametresi autowiring ile çözümlenemedi.",
            $class,
            $parameter
        ));
    }
}