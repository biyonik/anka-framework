<?php

declare(strict_types=1);

namespace Framework\Core\Container;

use Framework\Core\Container\Contracts\ContainerInterface;
use Framework\Core\Container\Exceptions\{ContainerException, NotFoundException};
use Framework\Core\Container\Attributes\{Service, Inject};
use ReflectionClass;
use ReflectionParameter;
use ReflectionAttribute;
use Closure;

/**
 * Framework'ün merkezi Dependency Injection Container implementasyonu.
 * 
 * Bu sınıf, uygulama genelinde servislerin yaşam döngüsünü ve bağımlılıkların
 * çözümlenmesini yönetir. PSR-11 uyumlu container implementasyonu sunar ve
 * gelişmiş özellikler içerir.
 * 
 * Özellikler:
 * - PSR-11 uyumluluğu
 * - Attribute tabanlı servis konfigürasyonu
 * - Otomatik constructor injection
 * - Singleton instance yönetimi
 * - Döngüsel bağımlılık tespiti
 * - Service tagging desteği
 * 
 * @package Framework\Core\Container
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Container implements ContainerInterface
{
    /**
     * Servis binding'lerini tutan array.
     * 
     * @var array<string,Closure|string|object|null>
     */
    private array $bindings = [];

    /**
     * Singleton instance'ları tutan array.
     * 
     * @var array<string,object>
     */
    private array $singletons = [];

    /**
     * Servis parametrelerini tutan array.
     * 
     * @var array<string,array<string,mixed>>
     */
    private array $parameters = [];

    /**
     * Çözümleme sırasında oluşan bağımlılık zincirini tutan array.
     * Döngüsel bağımlılıkları tespit etmek için kullanılır.
     * 
     * @var array<string>
     */
    private array $resolutionStack = [];

    /**
     * Tag'lenmiş servisleri tutan array.
     * 
     * @var array<string,array<string>>
     */
    private array $tagged = [];

    /**
     * {@inheritdoc}
     */
    public function bind(string $abstract, string|object|callable|null $concrete = null, array $parameters = []): void
    {
        // Concrete tip belirtilmemişse abstract'ı kullan
        $concrete = $concrete ?? $abstract;

        // Closure'a çevir
        $this->bindings[$abstract] = $this->getClosure($abstract, $concrete);
        
        // Parametreleri kaydet
        if (!empty($parameters)) {
            $this->parameters[$abstract] = $parameters;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function singleton(string $abstract, string|object|callable|null $concrete = null, array $parameters = []): void
    {
        // Servis attribute'unu kontrol et
        if (is_string($concrete) && class_exists($concrete)) {
            $reflector = new ReflectionClass($concrete);
            $attributes = $reflector->getAttributes(Service::class);
            
            if (!empty($attributes)) {
                /** @var Service */
                $serviceAttribute = $attributes[0]->newInstance();
                $parameters = array_merge($serviceAttribute->getParameters(), $parameters);
            }
        }

        // Binding'i oluştur
        $this->bind($abstract, $concrete, $parameters);
        
        // Singleton olarak işaretle
        $this->singletons[$abstract] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id, array $parameters = []): mixed
    {
        try {
            return $this->resolve($id, $parameters);
        } catch (ContainerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ContainerException("Servis çözümleme hatası: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->singletons[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function unbind(string $abstract): void
    {
        unset(
            $this->bindings[$abstract],
            $this->singletons[$abstract],
            $this->parameters[$abstract]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->singletons = [];
        $this->parameters = [];
        $this->tagged = [];
        $this->resolutionStack = [];
    }

    /**
     * Bir servise ait instance'ı çözümler.
     * 
     * @param string $abstract Çözümlenecek servis
     * @param array<string,mixed> $parameters Override edilecek parametreler 
     * @return mixed Çözümlenen instance
     * 
     * @throws ContainerException Çözümleme hatası
     * @throws NotFoundException Servis bulunamadığında
     */
    protected function resolve(string $abstract, array $parameters = []): mixed
    {
        // Döngüsel bağımlılık kontrolü
        if (in_array($abstract, $this->resolutionStack)) {
            throw ContainerException::circularDependency(
                array_merge($this->resolutionStack, [$abstract])
            );
        }

        // Çözümleme stack'ine ekle
        $this->resolutionStack[] = $abstract;

        try {
            // Singleton kontrolü
            if (isset($this->singletons[$abstract])) {
                return $this->singletons[$abstract];
            }

            // Binding kontrolü
            if (!isset($this->bindings[$abstract])) {
                if (!class_exists($abstract)) {
                    throw NotFoundException::serviceNotFound($abstract);
                }
                $this->bind($abstract);
            }

            // Instance oluştur
            $concrete = $this->bindings[$abstract];
            $instance = $concrete instanceof Closure
                ? $concrete($this, $parameters)
                : $this->build($concrete, $parameters);

            // Singleton ise kaydet
            if (isset($this->singletons[$abstract])) {
                $this->singletons[$abstract] = $instance;
            }

            return $instance;
        } finally {
            // Çözümleme stack'inden çıkar
            array_pop($this->resolutionStack);
        }
    }

    /**
     * Verilen concrete tip için bir Closure oluşturur.
     * 
     * @param string $abstract Abstract tip
     * @param string|object|callable $concrete Concrete tip
     * @return Closure Instance oluşturacak closure
     */
    protected function getClosure(string $abstract, string|object|callable $concrete): Closure
    {
        // Concrete closure ise direkt döndür
        if ($concrete instanceof Closure) {
            return $concrete;
        }

        // Concrete instance ise onu döndüren closure oluştur
        if (is_object($concrete)) {
            return fn() => $concrete;
        }

        // String ise build eden closure oluştur
        return fn(Container $container, array $parameters = []) =>
            $container->build($concrete, $parameters);
    }

    /**
     * Verilen class için bir instance oluşturur.
     * 
     * @param string $concrete Oluşturulacak class
     * @param array<string,mixed> $parameters Constructor parametreleri
     * @return object Oluşturulan instance
     * 
     * @throws ContainerException Oluşturma hatası
     * @throws NotFoundException Bulunamadı hatası
     */
    protected function build(string $concrete, array $parameters = []): object
    {
        try {
            $reflector = new ReflectionClass($concrete);

            // Abstract class kontrolü
            if (!$reflector->isInstantiable()) {
                throw NotFoundException::concreteNotFound($concrete);
            }

            // Constructor parametrelerini çözümle
            $constructor = $reflector->getConstructor();
            if (is_null($constructor)) {
                return new $concrete();
            }

            $dependencies = $this->resolveDependencies(
                $constructor->getParameters(),
                $parameters
            );

            return $reflector->newInstanceArgs($dependencies);
        } catch (\ReflectionException $e) {
            throw new ContainerException(
                "Reflection hatası: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Constructor parametrelerini çözümler.
     * 
     * @param array<ReflectionParameter> $parameters Çözümlenecek parametreler
     * @param array<string,mixed> $primitives Override edilecek değerler
     * @return array<mixed> Çözümlenen değerler
     * 
     * @throws ContainerException Çözümleme hatası
     */
    protected function resolveDependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            // Parametre adını al
            $name = $parameter->getName();

            // Override kontrolü
            if (array_key_exists($name, $primitives)) {
                $dependencies[] = $primitives[$name];
                continue;
            }

            // Inject attribute kontrolü
            $injectAttribute = $this->getInjectAttribute($parameter);
            if ($injectAttribute !== null) {
                $dependencies[] = $this->resolveInjectAttribute($injectAttribute, $parameter);
                continue;
            }

            // Tip kontrolü
            $type = $parameter->getType();
            if ($type === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw ContainerException::autowireFailed(
                    $parameter->getDeclaringClass()->getName(),
                    $name
                );
            }

            // Class dependency ise çözümle
            if (!$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            // Default değer varsa kullan
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw ContainerException::autowireFailed(
                $parameter->getDeclaringClass()->getName(),
                $name
            );
        }

        return $dependencies;
    }

    /**
     * Parametre üzerindeki Inject attribute'unu alır.
     * 
     * @param ReflectionParameter $parameter Kontrol edilecek parametre
     * @return Inject|null Bulunan attribute veya null
     */
    protected function getInjectAttribute(ReflectionParameter $parameter): ?Inject
    {
        $attributes = $parameter->getAttributes(Inject::class);
        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Inject attribute'unu çözümler.
     * 
     * @param Inject $inject Çözümlenecek attribute
     * @param ReflectionParameter $parameter İlgili parametre
     * @return mixed Çözümlenen değer
     * 
     * @throws ContainerException Çözümleme hatası
     */
    protected function resolveInjectAttribute(Inject $inject, ReflectionParameter $parameter): mixed
    {
        // ID bazlı injection
        if ($inject->getId() !== null) {
            return $this->resolveById($inject->getId(), $parameter);
        }

        // Servis bazlı injection
        if ($inject->getService() !== null) {
            return $this->get($inject->getService());
        }

        // Değer bazlı injection
        if ($inject->getValue() !== null) {
            return $inject->getValue();
        }

        // Required kontrolü
        if (!$inject->isRequired() && $parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw ContainerException::autowireFailed(
            $parameter->getDeclaringClass()->getName(),
            $parameter->getName()
        );
    }

    /**
     * ID bazlı injection çözümler.
     * 
     * @param string $id Çözümlenecek ID
     * @param ReflectionParameter $parameter İlgili parametre
     * @return mixed Çözümlenen değer
     */
    protected function resolveById(string $id, ReflectionParameter $parameter): mixed
    {
        // TODO: Implement configuration based injection
        throw new ContainerException('ID bazlı injection henüz implement edilmedi.');
    }
}