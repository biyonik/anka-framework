<?php

declare(strict_types = 1);

namespace Framework\Core\Container\Exceptions;


use Psr\Container\NotFoundExceptionInterface;

/**
 * Container'da bulunamayan servisleri temsil eden exception sınıfı.
 * 
 * Bu sınıf, container'dan talep edilen bir servisin bulunamadığı durumları
 * yönetmek için kullanılır. PSR-11 NotFoundExceptionInterface'ini implemente eder.
 * 
 * Kullanım Durumları:
 * - Binding'i olmayan servis talebi
 * - Abstract class/interface için concrete implementation bulunamama durumu
 * - Autowiring ile çözümlenemeyen bağımlılıklar
 * 
 * @package Framework\Core\Container
 * @subpackage Exceptions
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * throw new NotFoundException(
 *     "UserRepository interface'i için concrete implementation bulunamadı"
 * );
 * ```
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    /**
     * Bulunamayan servis için özel bir exception oluşturur.
     * 
     * @param string $id Bulunamayan servisin identifier'ı
     * @return static
     */
    public static function serviceNotFound(string $id): static
    {
        return new static(sprintf(
            "'%s' servisi container'da bulunamadı.",
            $id
        ));
    }

    /**
     * Bulunamayan concrete implementation için özel bir exception oluşturur.
     * 
     * @param string $abstract Concrete implementation'ı bulunamayan abstract/interface
     * @return static
     */
    public static function concreteNotFound(string $abstract): static
    {
        return new static(sprintf(
            "'%s' için concrete implementation bulunamadı.",
            $abstract
        ));
    }
}