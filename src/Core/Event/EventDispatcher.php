<?php

declare(strict_types=1);

namespace Framework\Core\Event;

use Framework\Core\Event\Contracts\EventInterface;
use Framework\Core\Event\Contracts\ListenerInterface;
use Framework\Core\Event\Contracts\EventDispatcherInterface;
use Framework\Core\Event\Exceptions\EventException;

/**
 * Event Dispatcher sınıfı.
 *
 * Bu sınıf, olayların dinleyicilere dağıtımını sağlar.
 *
 * @package Framework\Core\Event
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * Dinleyiciler dizisi.
     *
     * @var array<string, array<int, array<int, ListenerInterface|callable>>>
     */
    protected array $listeners = [];

    /**
     * Dinleyiciler için sorted flag.
     *
     * @var array<string, bool>
     */
    protected array $sorted = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch(EventInterface $event): EventInterface
    {
        $eventName = $event->getName();

        if (empty($this->listeners[$eventName])) {
            return $event;
        }

        // Dinleyicileri öncelik sırasına göre sırala
        if (!isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        // Dinleyicileri çalıştır
        foreach ($this->listeners[$eventName] as $priority) {
            foreach ($priority as $listenerData) {
                [$listener, $method] = $listenerData;

                // Callback veya listener metodu çağır
                if (is_callable($listener)) {
                    $listener($event);
                } else {
                    $listener->$method($event);

                    // Propagasyonu kontrol et
                    if ($listener instanceof ListenerInterface && $listener->stopsPropagation()) {
                        return $event;
                    }
                }
            }
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener(string|array $eventName, ListenerInterface|callable $listener): self
    {
        // Birden fazla olay tipi için kayıt
        if (is_array($eventName)) {
            foreach ($eventName as $name) {
                $this->addListener($name, $listener);
            }
            return $this;
        }

        // Listener'ın önceliğini belirle
        $priority = 0;
        $method = 'handle';

        if ($listener instanceof ListenerInterface) {
            $priority = $listener->getPriority();
        }

        // Listener'ı kaydet
        $this->listeners[$eventName][$priority][] = [$listener, $method];
        $this->sorted[$eventName] = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener(string $eventName, ListenerInterface|callable $listener): self
    {
        if (empty($this->listeners[$eventName])) {
            return $this;
        }

        // Tüm öncelik düzeylerinde arama yap
        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            foreach ($listeners as $index => $listenerData) {
                [$registeredListener, ] = $listenerData;

                if ($registeredListener === $listener) {
                    unset($this->listeners[$eventName][$priority][$index]);

                    // Boş öncelik grubunu kaldır
                    if (empty($this->listeners[$eventName][$priority])) {
                        unset($this->listeners[$eventName][$priority]);
                    }

                    // Eğer olay için dinleyici kalmadıysa
                    if (empty($this->listeners[$eventName])) {
                        unset($this->listeners[$eventName], $this->sorted[$eventName]);
                    } else {
                        $this->sorted[$eventName] = false;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllListeners(?string $eventName = null): self
    {
        if ($eventName !== null) {
            if (isset($this->listeners[$eventName])) {
                unset($this->listeners[$eventName], $this->sorted[$eventName]);
            }
        } else {
            $this->listeners = [];
            $this->sorted = [];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners(?string $eventName = null): array
    {
        // Belirli bir olay için dinleyicileri getir
        if ($eventName !== null) {
            if (!isset($this->listeners[$eventName])) {
                return [];
            }

            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }

            $flattenedListeners = [];
            foreach ($this->listeners[$eventName] as $listeners) {
                foreach ($listeners as $listenerData) {
                    $flattenedListeners[] = $listenerData[0];
                }
            }

            return $flattenedListeners;
        }

        // Tüm dinleyicileri olay adlarına göre gruplanmış olarak getir
        $allListeners = [];
        foreach (array_keys($this->listeners) as $eventName) {
            $allListeners[$eventName] = $this->getListeners($eventName);
        }

        return $allListeners;
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners(string $eventName): bool
    {
        return !empty($this->listeners[$eventName]);
    }

    /**
     * Olay adını ve sınıf adını alarak dinleyici ekler.
     *
     * @param string $eventName Olay adı
     * @param string $listenerClass Dinleyici sınıf adı
     * @return self Akıcı arayüz için
     * @throws EventException Dinleyici bulunamazsa
     */
    public function addListenerByClass(string $eventName, string $listenerClass): self
    {
        if (!class_exists($listenerClass)) {
            throw new EventException(sprintf('Listener sınıfı bulunamadı: %s', $listenerClass));
        }

        if (!is_subclass_of($listenerClass, ListenerInterface::class)) {
            throw new EventException(
                sprintf('Listener sınıfı %s arayüzünü implement etmelidir', ListenerInterface::class)
            );
        }

        $listener = new $listenerClass();
        return $this->addListener($eventName, $listener);
    }

    /**
     * Bir olay için bir kez çalışacak dinleyici ekler.
     *
     * @param string $eventName Olay adı
     * @param callable $listener Dinleyici
     * @param int $priority Öncelik değeri
     * @return self Akıcı arayüz için
     */
    public function once(string $eventName, callable $listener, int $priority = 0): self
    {
        $onceListener = function (EventInterface $event) use (&$onceListener, $eventName, $listener) {
            $this->removeListener($eventName, $onceListener);
            $listener($event);
        };

        // Önceliği ayarlamak için özel bir dinleyici oluştur
        $wrapper = new class($onceListener, $priority) extends AbstractListener {
            private $callback;

            public function __construct(callable $callback, int $priority)
            {
                $this->callback = $callback;
                $this->priority = $priority;
            }

            public function handle(EventInterface $event): void
            {
                ($this->callback)($event);
            }
        };

        return $this->addListener($eventName, $wrapper);
    }

    /**
     * Belirli bir olayın dinleyicilerini önceliğe göre sıralar.
     *
     * @param string $eventName Olay adı
     * @return void
     */
    protected function sortListeners(string $eventName): void
    {
        if (empty($this->listeners[$eventName])) {
            return;
        }

        // Öncelik anahtarlarını küçükten büyüğe sırala (düşük değer = yüksek öncelik)
        ksort($this->listeners[$eventName]);
        $this->sorted[$eventName] = true;
    }

    /**
     * Bir listener sınıfını kaydeder.
     * Listener, kendi getSubscribedEvents() metoduna göre olaylara kaydedilir.
     *
     * @param ListenerInterface $listener Kaydedilecek dinleyici
     * @return self Akıcı arayüz için
     */
    public function addSubscriber(ListenerInterface $listener): self
    {
        $events = $listener::getSubscribedEvents();

        if (is_string($events)) {
            $this->addListener($events, $listener);
        } elseif (is_array($events)) {
            foreach ($events as $eventName) {
                $this->addListener($eventName, $listener);
            }
        }

        return $this;
    }
}