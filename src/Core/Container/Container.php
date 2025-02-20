<?php

declare(strict_types=1);

namespace Framework\Core\Container;

use Framework\Core\Container\Contracts\{ContainerInterface, ServiceProviderInterface};
use Framework\Core\Container\Exceptions\{ContainerException, NotFoundException};
use Framework\Core\Container\Attributes\{Service, Inject};
use Framework\Core\Configuration\Contracts\ConfigurationInterface;
use ReflectionClass;
use ReflectionParameter;
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
 * - Service tagging sistemi
 * 
 * @package Framework\Core\Container
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 */
class Container implements ContainerInterface
{
    /**
     * Servis binding'lerini tutan array.
     * @var array<string,Closure|string|object|null>
     */
    private array $bindings = [];

    /**
     * Singleton instance'ları tutan array.
     * @var array<string,object>
     */
    private array $singletons = [];

    /**
     * Servis parametrelerini tutan array.
     * @var array<string,array<string,mixed>>
     */
    private array $parameters = [];

    /**
     * Çözümleme sırasında oluşan bağımlılık zincirini tutan array.
     * @var array<string>
     */
    private array $resolutionStack = [];

    /**
     * Tag'lenmiş servisleri tutan array.
     * @var array<string,array<string>>
     */
    private array $tagged = [];

    /**
     * Register edilmiş provider'ları tutan array.
     * @var array<class-string,ServiceProviderInterface>
     */
    private array $providers = [];

    /**
     * Boot edilmiş provider'ları tutan array.
     * @var array<class-string,bool>
     */
    private array $bootedProviders = [];

    /**
     * {@inheritdoc}
     */
    public function bind(string $abstract, string|object|callable|null $concrete = null, array $parameters = []): void
    {
        // Concrete tip belirtilmemişse abstract'ı kullan
        $concrete = $concrete ?? $abstract;

        // Eğer concrete bir instance ise, her zaman aynı instance'ı döndüren bir closure oluştur
        if (is_object($concrete) && !$concrete instanceof Closure) {
            $concrete = fn() => $concrete;
        }

        // Eğer concrete bir Closure değilse, instance oluşturan bir closure oluştur
        if (!$concrete instanceof Closure && is_string($concrete)) {
            $concrete = fn($container, $params = []) => $container->build($concrete, array_merge($parameters, $params));
        }

        // Binding'i kaydet
        $this->bindings[$abstract] = $concrete;

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
        // Önce normal binding oluştur
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
     * Servisleri tag'ler ile işaretler.
     * 
     * @param string|array<string> $abstracts Tag'lenecek servisler
     * @param string $tag Tag adı
     * @return void
     */
    public function tag(string|array $abstracts, string $tag): void
    {
        $abstracts = is_array($abstracts) ? $abstracts : [$abstracts];

        foreach ($abstracts as $abstract) {
            $this->tagged[$tag][] = $abstract;
        }
    }

    /**
     * Belirli bir tag ile işaretlenmiş tüm servisleri döndürür.
     * 
     * @param string $tag Tag adı
     * @return array<object> Tag'li servisler
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tagged[$tag])) {
            return [];
        }

        return array_map(
            fn($abstract) => $this->get($abstract),
            $this->tagged[$tag]
        );
    }

    /**
     * Bir ServiceProvider'ı container'a kaydeder ve başlatır.
     * 
     * @param class-string|ServiceProviderInterface $provider Provider sınıfı veya instance'ı
     * @param bool $boot Provider'ı hemen boot etmek için true
     * @return void
     */
    public function addProvider(string|ServiceProviderInterface $provider, bool $boot = true): void
    {
        // Provider instance'ını al
        $provider = is_string($provider) ? new $provider() : $provider;
        
        // Provider class adını al
        $providerClass = get_class($provider);
        
        // Zaten kayıtlı mı kontrol et
        if (isset($this->providers[$providerClass])) {
            return;
        }
        
        // Bağımlılıkları kaydet
        foreach ($provider->dependencies() as $dependency) {
            $this->addProvider($dependency, false);
        }
        
        // Provider'ı kaydet
        $this->providers[$providerClass] = $provider;
        
        // Register
        $provider->register($this);
        
        // Boot
        if ($boot) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Kayıtlı tüm provider'ları boot eder.
     * 
     * @return void
     */
    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            $this->bootProvider($provider);
        }
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
            // Daha önce oluşturulmuş singleton var mı?
            if (isset($this->singletons[$abstract]) && isset($this->bindings[$abstract])) {
                return $this->bindings[$abstract];
            }

            // Binding var mı?
            if (!isset($this->bindings[$abstract])) {
                if (!class_exists($abstract)) {
                    throw NotFoundException::serviceNotFound($abstract);
                }
                // Otomatik binding oluştur
                $this->bind($abstract);
            }

            // Instance oluştur
            $concrete = $this->bindings[$abstract];
            $instance = $concrete instanceof Closure
                ? $concrete($this, $parameters)
                : $this->build($concrete, $parameters);

            // Singleton ise kaydet
            if (isset($this->singletons[$abstract])) {
                $this->bindings[$abstract] = $instance;
            }

            return $instance;
        } finally {
            // Çözümleme stack'inden çıkar
            array_pop($this->resolutionStack);
        }
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

            // Service attribute kontrolü
            $serviceAttribute = $this->getServiceAttribute($reflector);
            if ($serviceAttribute !== null) {
                $parameters = array_merge($serviceAttribute->getParameters(), $parameters);
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
     * Sınıf üzerindeki Service attribute'unu alır.
     * 
     * @param ReflectionClass $reflector Kontrol edilecek sınıf
     * @return Service|null Bulunan attribute veya null
     */
    protected function getServiceAttribute(ReflectionClass $reflector): ?Service
    {
        $attributes = $reflector->getAttributes(Service::class);
        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
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
     * 
     * @throws ContainerException Çözümleme hatası
     */
    protected function resolveById(string $id, ReflectionParameter $parameter): mixed
    {
        // Konfigürasyon servisini al
        $config = $this->get(ConfigurationInterface::class);
        
        // Konfigürasyon değerini çek
        $value = $config->get($id);
        
        // Değer bulunamadıysa ve parametre zorunlu değilse default değeri kullan
        if ($value === null && $parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        
        // Değer bulunamadıysa ve parametre zorunluysa hata fırlat
        if ($value === null) {
            throw ContainerException::autowireFailed(
                $parameter->getDeclaringClass()->getName(),
                $parameter->getName()
            );
        }
        
        // Tip dönüşümü yap
        $type = $parameter->getType();
        if ($type !== null && $type->isBuiltin()) {
            settype($value, $type->getName());
        }
        
        return $value;
    }

    /**
     * Bir ServiceProvider'ı boot eder.
     * 
     * @param ServiceProviderInterface $provider Boot edilecek provider
     * @return void
     */
    protected function bootProvider(ServiceProviderInterface $provider): void
    {
        $providerClass = get_class($provider);
        
        // Zaten boot edilmiş mi kontrol et
        if (isset($this->bootedProviders[$providerClass])) {
            return;
        }
        
        // Bağımlılıkları boot et
        foreach ($provider->dependencies() as $dependency) {
            if (isset($this->providers[$dependency])) {
                $this->bootProvider($this->providers[$dependency]);
            }
        }
        
        // Provider'ı boot et
        $provider->boot($this);
        
        // Boot edildi olarak işaretle
        $this->bootedProviders[$providerClass] = true;
    }
}