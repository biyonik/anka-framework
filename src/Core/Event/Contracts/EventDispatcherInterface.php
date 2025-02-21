<?php

declare(strict_types=1);

namespace Framework\Core\Event\Contracts;

/**
 * Event Dispatcher arayüzü.
 *
 * Bu arayüz, olay gönderici bileşenlerin uygulaması gereken metotları tanımlar.
 *
 * @package Framework\Core\Event
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface EventDispatcherInterface
{
    /**
     * Bir olayı dinleyicilere dağıtır.
     *
     * @param EventInterface $event Dağıtılacak olay
     * @return EventInterface İşlenmiş olay
     */
    public function dispatch(EventInterface $event): EventInterface;

    /**
     * Belirtilen olay tipine bir dinleyici ekler.
     *
     * @param string|array<string> $eventName Olay adı veya adları
     * @param ListenerInterface|callable $listener Dinleyici
     * @return self Akıcı arayüz için
     */
    public function addListener(string|array $eventName, ListenerInterface|callable $listener): self;

    /**
     * Belirtilen olay tipinden bir dinleyiciyi kaldırır.
     *
     * @param string $eventName Olay adı
     * @param ListenerInterface|callable $listener Kaldırılacak dinleyici
     * @return self Akıcı arayüz için
     */
    public function removeListener(string $eventName, ListenerInterface|callable $listener): self;

    /**
     * Belirtilen olay tipi için tüm dinleyicileri kaldırır.
     *
     * @param string|null $eventName Olay adı, null ise tüm olaylar
     * @return self Akıcı arayüz için
     */
    public function removeAllListeners(?string $eventName = null): self;

    /**
     * Belirtilen olay tipi için tüm dinleyicileri döndürür.
     *
     * @param string|null $eventName Olay adı, null ise tüm dinleyiciler
     * @return array<ListenerInterface|callable> Dinleyiciler
     */
    public function getListeners(?string $eventName = null): array;

    /**
     * Belirtilen olay tipi için dinleyici olup olmadığını kontrol eder.
     *
     * @param string $eventName Olay adı
     * @return bool Dinleyici varsa true
     */
    public function hasListeners(string $eventName): bool;
}