<?php

declare(strict_types=1);

namespace Framework\Core\Event;

use Framework\Core\Event\Contracts\EventInterface;
use Framework\Core\Event\Contracts\ListenerInterface;

/**
 * Temel Listener sınıfı.
 *
 * Bu sınıf, tüm Listener nesneleri için temel yapıyı sağlar.
 *
 * @package Framework\Core\Event
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractListener implements ListenerInterface
{
    /**
     * Dinleyicinin öncelik değeri.
     * Düşük değerler daha yüksek önceliğe sahiptir.
     *
     * @var int
     */
    protected int $priority = 0;

    /**
     * Propagasyon durumu.
     *
     * @var bool
     */
    protected bool $stopPropagation = false;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): string|array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Öncelik değerini ayarlar.
     *
     * @param int $priority Öncelik değeri
     * @return self Akıcı arayüz için
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function stopsPropagation(): bool
    {
        return $this->stopPropagation;
    }

    /**
     * Propagasyon durumunu ayarlar.
     *
     * @param bool $stop Propagasyon durumu
     * @return self Akıcı arayüz için
     */
    public function setStopPropagation(bool $stop): self
    {
        $this->stopPropagation = $stop;
        return $this;
    }

    /**
     * Olayın beklenilen tipte olup olmadığını kontrol eder.
     *
     * @param EventInterface $event Kontrol edilecek olay
     * @param string $expectedClass Beklenen sınıf adı
     * @return bool Olay beklenen tipte ise true
     */
    protected function isEventInstanceOf(EventInterface $event, string $expectedClass): bool
    {
        return $event instanceof $expectedClass;
    }

    /**
     * {@inheritdoc}
     *
     * Alt sınıflar tarafından override edilmelidir.
     */
    public function handle(EventInterface $event): void
    {
        // Alt sınıflar tarafından implemente edilecek
    }
}