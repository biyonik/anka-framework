# Container Katmanı

Framework'ün güçlü Dependency Injection Container implementasyonu. PSR-11 uyumlu, attribute tabanlı ve gelişmiş özelliklere sahip bir DI container sunar.

## 🚀 Özellikler

- PSR-11 uyumlu container implementasyonu
- Attribute tabanlı servis konfigürasyonu
- Güçlü tip desteği (PHP 8.2+)
- Otomatik constructor injection
- Service Provider sistemi
- Tag bazlı servis yönetimi
- Konfigürasyon tabanlı değer injection

## 📥 Kurulum

Container katmanı framework'ün çekirdek bileşenidir ve otomatik olarak yüklenir. Özel kullanım için:

```php
use Framework\Core\Container\Container;

$container = new Container();
```

## 🔧 Temel Kullanım

### 1. Basit Binding

```php
// Interface to Class binding
$container->bind(LoggerInterface::class, FileLogger::class);

// Instance binding
$container->bind(Logger::class, new FileLogger());

// Closure binding
$container->bind('database', function ($container) {
    return new Database($container->get('config'));
});
```

### 2. Singleton Binding

```php
// Singleton class binding
$container->singleton(DatabaseInterface::class, MySQLDatabase::class);

// Singleton instance her zaman aynı olacaktır
$db1 = $container->get(DatabaseInterface::class);
$db2 = $container->get(DatabaseInterface::class);
assert($db1 === $db2); // true
```

### 3. Attribute Kullanımı

```php
#[Service(singleton: true, binds: LoggerInterface::class)]
class FileLogger implements LoggerInterface 
{
    public function __construct(
        #[Inject('logger.path')] private string $logPath,
        #[Inject(service: Config::class)] private Config $config
    ) {}
}
```

### 4. Service Provider Kullanımı

```php
class LoggingServiceProvider extends AbstractServiceProvider
{
    protected array $singletons = [
        LoggerInterface::class => FileLogger::class
    ];

    public function register(ContainerInterface $container): void
    {
        parent::register($container);
        
        $container->bind('logger.path', fn() => storage_path('logs'));
    }

    public function boot(ContainerInterface $container): void
    {
        // Boot işlemleri
        $logger = $container->get(LoggerInterface::class);
        $logger->info('Logger boot edildi');
    }
}

// Provider'ı kaydet
$container->addProvider(LoggingServiceProvider::class);
```

### 5. Tag Sistemi

```php
// Servisleri tag'le
$container->tag([
    FileLogger::class,
    DatabaseLogger::class
], 'loggers');

// Tag'li servisleri al
$loggers = $container->tagged('loggers');
foreach ($loggers as $logger) {
    $logger->info('Test log');
}
```

## 🎯 Gelişmiş Özellikler

### Otomatik Constructor Injection

Container, constructor parametrelerini otomatik olarak çözümler:

```php
class UserService
{
    public function __construct(
        private LoggerInterface $logger,  // Otomatik inject
        private DatabaseInterface $db,    // Otomatik inject
        private string $apiKey            // Manuel inject gerekir
    ) {}
}
```

### Configuration Bazlı Injection

```php
class MailService
{
    public function __construct(
        #[Inject('mail.host')] private string $host,
        #[Inject('mail.port')] private int $port
    ) {}
}
```

### Döngüsel Bağımlılık Tespiti

Container, döngüsel bağımlılıkları otomatik tespit eder ve anlamlı hatalar üretir:

```php
// A -> B -> C -> A şeklinde bir döngü
class A { public function __construct(B $b) {} }
class B { public function __construct(C $c) {} }
class C { public function __construct(A $a) {} }

// ContainerException: Döngüsel bağımlılık tespit edildi: A -> B -> C -> A
$container->get(A::class);
```

## 🚦 Hata Yönetimi

Container, PSR-11 uyumlu iki tür exception fırlatır:

1. `ContainerException`: Genel container hataları
2. `NotFoundException`: Servis bulunamama durumu

```php
try {
    $service = $container->get('nonexistent');
} catch (NotFoundException $e) {
    // Servis bulunamadı
} catch (ContainerException $e) {
    // Diğer container hataları
}
```

## 🔍 Best Practices

1. **Interface Kullanımı**: Servisleri her zaman interface üzerinden tanımlayın
```php
$container->bind(LoggerInterface::class, FileLogger::class);
```

2. **Service Provider**: Karmaşık servis konfigürasyonları için ServiceProvider kullanın
```php
$container->addProvider(DatabaseServiceProvider::class);
```

3. **Attribute Kullanımı**: Tekrarlayan binding'ler için attribute'ları tercih edin
```php
#[Service(singleton: true)]
class SingletonService {}
```

4. **Tag Sistemi**: Benzer servisleri gruplamak için tag sistemini kullanın
```php
$container->tag([Service1::class, Service2::class], 'api');
```

## 🔌 Extend Etme

Container'ı extend etmek için:

```php
class ExtendedContainer extends Container
{
    public function resolveCustom(string $id): mixed
    {
        // Özel çözümleme mantığı
    }
}
```

## 📚 API Referansı

### Temel Metodlar

- `bind(string $abstract, mixed $concrete = null)`
- `singleton(string $abstract, mixed $concrete = null)`
- `get(string $id)`
- `has(string $id)`
- `unbind(string $abstract)`
- `flush()`

### Provider Yönetimi

- `addProvider(string|ServiceProviderInterface $provider)`
- `bootProviders()`

### Tag Sistemi

- `tag(array|string $abstracts, string $tag)`
- `tagged(string $tag)`

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-container`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-container`)
5. Pull Request oluşturun