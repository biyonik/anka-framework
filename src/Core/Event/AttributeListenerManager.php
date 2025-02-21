<?php

declare(strict_types=1);

namespace Framework\Core\Event;

use Framework\Core\Event\Attributes\Listener as ListenerAttribute;
use Framework\Core\Event\Contracts\EventDispatcherInterface;
use Framework\Core\Event\Contracts\EventInterface;
use Framework\Core\Event\Contracts\ListenerInterface;
use Framework\Core\Event\Exceptions\EventException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;

/**
 * Attribute tabanlı listener yönetim sınıfı.
 *
 * Bu sınıf, Attribute kullanarak tanımlanmış event listener'ları yönetir.
 *
 * @package Framework\Core\Event
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
readonly class AttributeListenerManager
{
    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Verilen sınıf ve metotlarındaki Listener attribute'larını bulur ve kaydeder.
     *
     * @param object|string $class Sınıf instance'ı veya sınıf adı
     * @return void
     * @throws EventException Listener attribute bulunamazsa veya geçersiz ise
     */
    public function registerClassListeners(object|string $class): void
    {
        $instance = is_string($class) ? new $class() : $class;
        $reflection = new ReflectionClass($instance);

        // Sınıf seviyesindeki dinleyicileri ekle
        $this->registerClassLevelListeners($reflection, $instance);

        // Metot seviyesindeki dinleyicileri ekle
        $this->registerMethodLevelListeners($reflection, $instance);
    }

    /**
     * Belirtilen dizindeki tüm sınıflardaki Listener attribute'larını bulur ve kaydeder.
     *
     * @param string $directory Taranacak dizin
     * @param string $namespace Sınıf namespace'i
     * @return array<string> Kayıtlı sınıflar
     */
    public function registerListenersFromDirectory(string $directory, string $namespace = ''): array
    {
        $registeredClasses = [];

        if (!is_dir($directory)) {
            return $registeredClasses;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname(), $namespace);

            if ($className && class_exists($className)) {
                try {
                    $this->registerClassListeners($className);
                    $registeredClasses[] = $className;
                } catch (EventException $e) {
                    // Burada log tutulabilir
                    continue;
                }
            }
        }

        return $registeredClasses;
    }

    /**
     * Sınıf seviyesindeki listener attribute'larını kaydeder.
     *
     * @param ReflectionClass $reflection Sınıf reflection'ı
     * @param object $instance Sınıf instance'ı
     * @return void
     */
    private function registerClassLevelListeners(ReflectionClass $reflection, object $instance): void
    {
        $attributes = $reflection->getAttributes(
            ListenerAttribute::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            /** @var ListenerAttribute $listenerAttribute */
            $listenerAttribute = $attribute->newInstance();

            $events = is_array($listenerAttribute->event)
                ? $listenerAttribute->event
                : [$listenerAttribute->event];

            // ListenerInterface için
            if ($instance instanceof ListenerInterface) {
                foreach ($events as $event) {
                    $this->eventDispatcher->addListener($event, $instance);

                    if ($listenerAttribute->priority !== 0) {
                        $instance->setPriority($listenerAttribute->priority);
                    }

                    if ($listenerAttribute->stopPropagation) {
                        $instance->setStopPropagation(true);
                    }
                }
            }
            // Callable (invoke metodu) için
            elseif (method_exists($instance, '__invoke')) {
                foreach ($events as $event) {
                    $this->eventDispatcher->addListener($event, $instance);
                }
            }
        }
    }

    /**
     * Metot seviyesindeki listener attribute'larını kaydeder.
     *
     * @param ReflectionClass $reflection Sınıf reflection'ı
     * @param object $instance Sınıf instance'ı
     * @return void
     */
    private function registerMethodLevelListeners(ReflectionClass $reflection, object $instance): void
    {
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(
                ListenerAttribute::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            foreach ($attributes as $attribute) {
                /** @var ListenerAttribute $listenerAttribute */
                $listenerAttribute = $attribute->newInstance();

                $events = is_array($listenerAttribute->event)
                    ? $listenerAttribute->event
                    : [$listenerAttribute->event];

                $methodName = $method->getName();

                foreach ($events as $event) {
                    // Metodu çağıran anonim fonksiyon oluştur
                    $listener = function (EventInterface $eventObj) use ($instance, $methodName) {
                        $instance->$methodName($eventObj);
                    };

                    // Listener wrapper oluştur
                    $wrapper = new class($listener, $listenerAttribute->priority, $listenerAttribute->stopPropagation) extends AbstractListener {
                        private $callback;

                        public function __construct(callable $callback, int $priority, bool $stopPropagation)
                        {
                            $this->callback = $callback;
                            $this->priority = $priority;
                            $this->stopPropagation = $stopPropagation;
                        }

                        public function handle(EventInterface $event): void
                        {
                            ($this->callback)($event);
                        }
                    };

                    $this->eventDispatcher->addListener($event, $wrapper);
                }
            }
        }
    }

    /**
     * Dosya yolundan sınıf adını çıkarır.
     *
     * @param string $filePath Dosya yolu
     * @param string $namespace Namespace
     * @return string|null Sınıf adı
     */
    private function getClassNameFromFile(string $filePath, string $namespace): ?string
    {
        $content = file_get_contents($filePath);

        if (preg_match('/namespace\s+([^;]+)/i', $content, $matches)) {
            $fileNamespace = $matches[1];
        } else {
            $fileNamespace = $namespace;
        }

        if (preg_match('/class\s+([^\s{]+)/i', $content, $matches)) {
            $className = $matches[1];
            return $fileNamespace ? $fileNamespace . '\\' . $className : $className;
        }

        return null;
    }
}