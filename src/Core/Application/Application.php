<?php

declare(strict_types=1);

namespace Framework\Core\Application;

use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Application\Bootstrap\BootstrapInterface;
use Framework\Core\Application\ServiceProvider\ServiceProviderInterface;
use Framework\Core\Container\Interfaces\ContainerInterface;
use Framework\Core\Container\Container;
use Framework\Core\Http\Request\Request;
use Framework\Core\Http\Response\Response;
use Framework\Core\Routing\Interfaces\RouterInterface;
use Framework\Core\Routing\Router;
use Framework\Core\Middleware\MiddlewareDispatcher;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

/**
 * Framework'ün ana uygulama sınıfı.
 * 
 * Bu sınıf, framework'ün ana giriş noktasıdır ve tüm bileşenleri bir araya getirir.
 * Container, router, middleware ve config yönetimini sağlar. Request/response
 * döngüsünü başlatır ve yönetir.
 * 
 * @package Framework\Core\Application
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Application implements ApplicationInterface
{
    /**
     * Framework versiyonu.
     */
    protected const VERSION = '1.0.0';

    /**
     * Uygulama base path'i.
     */
    protected string $basePath;

    /**
     * Container instance'ı.
     */
    protected ContainerInterface $container;

    /**
     * Router instance'ı.
     */
    protected RouterInterface $router;

    /**
     * Middleware dispatcher.
     */
    protected MiddlewareDispatcher $middlewareDispatcher;

    /**
     * Uygulama konfigürasyonu.
     * 
     * @var array<string,mixed>
     */
    protected array $config = [];

    /**
     * Servis provider'lar.
     * 
     * @var array<class-string,ServiceProviderInterface>
     */
    protected array $providers = [];

    /**
     * Boot edilmiş provider'lar.
     * 
     * @var array<class-string,bool>
     */
    protected array $bootedProviders = [];

    /**
     * Bootstrap sınıfları.
     * 
     * @var array<BootstrapInterface>
     */
    protected array $bootstrappers = [];

    /**
     * Uygulama çevresi.
     */
    protected string $environment = 'production';

    /**
     * Debug modu.
     */
    protected bool $debug = false;

    /**
     * Boot edildi mi?
     */
    protected bool $booted = false;

    /**
     * Constructor.
     * 
     * @param string $basePath Uygulama base path'i
     * @param array<string,mixed> $config Uygulama konfigürasyonu
     */
    public function __construct(string $basePath = '', array $config = [])
    {
        $this->basePath = $basePath;
        $this->config = $config;
        
        // Çevre ve debug ayarlarını al
        $this->environment = $config['app']['env'] ?? 'production';
        $this->debug = $config['app']['debug'] ?? false;
        
        // Temel bileşenleri oluştur
        $this->registerBaseBindings();
        $this->registerBaseProviders();
        $this->registerCoreAliases();
        
        // Bootstrap sınıflarını yükle
        $this->registerBaseBootstrappers();
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewareDispatcher(): MiddlewareDispatcher
    {
        return $this->middlewareDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function register(string|object $provider): static
    {
        // Provider instance'ı oluştur
        $provider = is_string($provider) ? new $provider() : $provider;
        
        // Zaten kayıtlı mı kontrol et
        $providerClass = get_class($provider);
        if (isset($this->providers[$providerClass])) {
            return $this;
        }
        
        // Bağımlılıkları kaydet
        if ($provider instanceof ServiceProviderInterface) {
            foreach ($provider->dependencies() as $dependency) {
                $this->register($dependency);
            }
        }
        
        // Provider'ı kaydet
        $this->providers[$providerClass] = $provider;
        
        // Register metodunu çağır
        if ($provider instanceof ServiceProviderInterface) {
            $provider->register($this);
        }
        
        // Defer edilmemişse ve uygulama boot edildiyse boot et
        if ($this->booted && $provider instanceof ServiceProviderInterface && !$provider->isDeferred()) {
            $this->bootProvider($provider);
        }
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function middleware(string|array $middleware): static
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        
        foreach ($middleware as $m) {
            $this->middlewareDispatcher->add($m);
        }
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): static
    {
        if ($this->booted) {
            return $this;
        }
        
        // Bootstrap sınıflarını çalıştır
        $this->bootstrapApplication();
        
        // Provider'ları boot et
        $this->bootProviders();
        
        $this->booted = true;
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface
    {
        // Uygulama boot et
        if (!$this->booted) {
            $this->boot();
        }
        
        // Request oluştur
        $request = $request ?? Request::fromGlobals();
        
        // Router ile eşleştir ve yanıt al
        $response = $this->router->dispatch($request);
        
        // Yanıtı döndür
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(ResponseInterface $response): void
    {
        // Response sınıfı Response interface'ini implement ediyorsa send metodunu çağır
        if (method_exists($response, 'send')) {
            $response->send();
        } else {
            // PSR-7 ResponseInterface'i için header ve body gönder
            
            // Status line
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
            
            // Headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
            
            // Body
            echo $response->getBody();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $value = $this->config;
        
        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return $default;
            }
            
            $value = $value[$part];
        }
        
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap(string|object $bootstrapper): static
    {
        $bootstrapper = is_string($bootstrapper) ? new $bootstrapper() : $bootstrapper;
        
        if (!$bootstrapper instanceof BootstrapInterface) {
            throw new \InvalidArgumentException('Bootstrapper must implement BootstrapInterface');
        }
        
        $this->bootstrappers[] = $bootstrapper;
        
        return $this;
    }

    /**
     * Temel binding'leri kaydeder.
     * 
     * @return void
     */
    protected function registerBaseBindings(): void
    {
        // Container oluştur
        $this->container = new Container();
        
        // Router oluştur
        $this->router = new Router();
        
        // Middleware dispatcher oluştur
        $this->middlewareDispatcher = new MiddlewareDispatcher();
        
        // Router'a middleware dispatcher'ı ekle
        $this->router->setMiddlewareDispatcher($this->middlewareDispatcher);
        
        // Container'a temel bileşenleri kaydet
        $this->container->singleton(ContainerInterface::class, $this->container);
        $this->container->singleton(RouterInterface::class, $this->router);
        $this->container->singleton(MiddlewareDispatcher::class, $this->middlewareDispatcher);
        $this->container->singleton(ApplicationInterface::class, $this);
        $this->container->singleton(self::class, $this);
    }

    /**
     * Temel provider'ları kaydeder.
     * 
     * @return void
     */
    protected function registerBaseProviders(): void
    {
        // TODO: Base provider implementation
    }

    /**
     * Temel alias'ları kaydeder.
     * 
     * @return void
     */
    protected function registerCoreAliases(): void
    {
        $aliases = [
            'app' => [self::class, ApplicationInterface::class],
            'container' => [Container::class, ContainerInterface::class],
            'router' => [Router::class, RouterInterface::class],
            'middleware' => [MiddlewareDispatcher::class],
        ];
        
        foreach ($aliases as $key => $aliasGroup) {
            foreach ((array) $aliasGroup as $alias) {
                $this->container->singleton($alias, fn() => $this->container->get($key));
            }
        }
    }

    /**
     * Temel bootstrap sınıflarını kaydeder.
     * 
     * @return void
     */
    protected function registerBaseBootstrappers(): void
    {
        $this->bootstrap(new Bootstrap\HandleExceptions());
        $this->bootstrap(new Bootstrap\RegisterProviders());
        $this->bootstrap(new Bootstrap\RegisterRoutes());
        $this->bootstrap(new Bootstrap\RegisterMiddleware());
    }

    /**
     * Bootstrap sınıflarını çalıştırır.
     * 
     * @return void
     */
    protected function bootstrapApplication(): void
    {
        // Bootstrap sınıflarını önceliğe göre sırala
        usort($this->bootstrappers, function (BootstrapInterface $a, BootstrapInterface $b) {
            return $a->getPriority() <=> $b->getPriority();
        });
        
        // Her bootstrap sınıfını çalıştır
        foreach ($this->bootstrappers as $bootstrapper) {
            // Çalışması gerekiyorsa ve doğru çevredeyse çalıştır
            if ($bootstrapper->shouldRun($this) && $bootstrapper->runsInEnvironment($this->environment)) {
                $bootstrapper->bootstrap($this);
            }
        }
    }

    /**
     * Provider'ları boot eder.
     * 
     * @return void
     */
    protected function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            if ($provider instanceof ServiceProviderInterface && !$provider->isDeferred()) {
                $this->bootProvider($provider);
            }
        }
    }

    /**
     * Tek bir provider'ı boot eder.
     * 
     * @param ServiceProviderInterface $provider Boot edilecek provider
     * @return void
     */
    protected function bootProvider(ServiceProviderInterface $provider): void
    {
        $providerClass = get_class($provider);
        
        // Zaten boot edildiyse geç
        if (isset($this->bootedProviders[$providerClass])) {
            return;
        }
        
        // Çevre kontrolü
        $environments = $provider->environments();
        if (!empty($environments) && !in_array($this->environment, $environments)) {
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