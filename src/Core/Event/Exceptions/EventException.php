<?php

declare(strict_types=1);

namespace Framework\Core\Event\Exceptions;

use RuntimeException;

/**
 * Olay sisteminde oluşan hataları temsil eden exception sınıfı.
 *
 * @package Framework\Core\Event
 * @subpackage Exceptions
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class EventException extends RuntimeException
{
    /**
     * Dinleyici bulunamadığında oluşturulacak exception.
     *
     * @param string $listenerClass Dinleyici sınıfı
     * @return static
     */
    public static function listenerNotFound(string $listenerClass): self
    {
        return new static(sprintf('Listener sınıfı bulunamadı: %s', $listenerClass));
    }

    /**
     * Olay bulunamadığında oluşturulacak exception.
     *
     * @param string $eventName Olay adı
     * @return static
     */
    public static function eventNotFound(string $eventName): self
    {
        return new static(sprintf('Event bulunamadı: %s', $eventName));
    }

    /**
     * Geçersiz listener tipinde oluşturulacak exception.
     *
     * @param mixed $listener Geçersiz dinleyici
     * @return static
     */
    public static function invalidListenerType(mixed $listener): self
    {
        return new static(sprintf(
            'Geçersiz listener tipi: %s. ListenerInterface implementasyonu veya callable olmalı',
            is_object($listener) ? get_class($listener) : gettype($listener)
        ));
    }
}