# Application Katmanı

Framework'ün ana giriş noktasını ve çekirdek yapısını oluşturan, tüm bileşenleri bir araya getiren merkezi katman.

## 🌟 Özellikler

- Framework'ün bootstrap sürecini yönetme
- Dependency Injection Container entegrasyonu
- Service Provider sistemi ile modüler yapı
- Bootstrap sınıfları ile esnek başlangıç süreci
- Middleware, Routing ve HTTP katmanlarını birleştirme
- Konfigürasyon yönetimi
- Hata ve istisna yönetimi
- Çevre (environment) bazlı çalışma modu

## 📂 Dizin Yapısı

```plaintext
Application/
├── Interfaces/
│   └── ApplicationInterface.php
├── Bootstrap/
│   ├── BootstrapInterface.php
│   ├── RegisterProviders.php
│   ├── RegisterRoutes.php
│   ├── RegisterMiddleware.php
│   └── HandleExceptions.php
├── ServiceProvider/
│   ├── ServiceProviderInterface.php
│   └── AbstractServiceProvider.php
└── Application.php
```

## 🚀 Kullanım Örnekleri

### 1. Temel Uygulama Başlatma

```php
// public/index.php
require_once __DIR__ . '/../vendor/autoload.php';

// Konfigürasyon yükle
$config = require_once __DIR__ . '/../config/app.php';

// Uygulama oluştur
$app = new Framework\Core\Application\Application(
    basePath: __DIR__ . '/..',
    config: $config
);

// Uygulamayı çalıştır
$response = $app->run();

// Yanıtı gönder
$app->terminate($response);
```

### 2. Service Provider Tanımlama

```php
// app/Providers/RouteServiceProvider.php
class RouteServiceProvider extends AbstractServiceProvider
{
    public function register(ApplicationInterface $app): void
    {
        // Binding'leri kaydet
        parent::register($app);
    }
    
    public function boot(ApplicationInterface $app): void
    {
        // Router'a middleware ekle
        $router = $app->getRouter();
        $router->middleware(['web']);
        
        // Route dosyalarını yükle
        require_once $app->getBasePath() . '/routes/web.php';
    }
    
    public function dependencies(): array
    {
        // Bu provider çalışmadan önce gereken diğer provider'lar
        return [
            CoreServiceProvider::class,
        ];
    }
}
```

### 3. Bootstrap Sınıfı Oluşturma

```php
// app/Bootstrap/LoadConfiguration.php
class LoadConfiguration implements BootstrapInterface
{
    public function bootstrap(ApplicationInterface $app): void
    {
        // Konfigürasyon dosyalarını yükle
        $configPath = $app->getBasePath() . '/config';
        $files = glob($configPath . '/*.php');
        
        $config = [];
        foreach ($files as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }
        
        // Container'a config bağla
        $app->getContainer()->singleton('config', $config);
    }
    
    public function getPriority(): int
    {
        return 5; // Exception handler'dan sonra, provider'lardan önce
    }
    
    public function shouldRun(ApplicationInterface $app): bool
    {
        return true;
    }
    
    public function runsInEnvironment(string $environment): bool
    {
        return true;
    }
}
```

### 4. Custom Exception Handler Ekleme

```php
// app/Exceptions/Handler.php
class Handler extends HandleExceptions
{
    protected function renderExceptionWithoutTrace(Throwable $e)
    {
        // Özel hata sayfası render et
        if ($e instanceof HttpException) {
            return view('errors.' . $e->getStatusCode(), [
                'exception' => $e
            ]);
        }
        
        // Default
        return parent::renderExceptionWithoutTrace($e);
    }
}

// Bootstrap aşamasında kullan
$app->bootstrap(new Handler());
```

### 5. Çevre Bazlı Konfigürasyon

```php
// .env
APP_ENV=production
APP_DEBUG=false

// config/app.php
return [
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'providers' => [
        // Base providers
        CoreServiceProvider::class,
        RouterServiceProvider::class,
        
        // Environment specific providers
        $app->getEnvironment() === 'local' ? DebugServiceProvider::class : null,
    ],
];
```

## 🌊 Yaşam Döngüsü

Application sınıfı, uygulama yaşam döngüsünü şu adımlarla yönetir:

1. **Başlatma (Constructor):**
   - Temel bağımlılıkları oluşturma (Container, Router, Middleware)
   - Konfigürasyon yükleme
   - Çevre (environment) ve debug ayarlarını tanımlama

2. **Bootstrap Süreci:**
   - İstisna yönetimini kurma
   - Service Provider'ları kaydetme
   - Route'ları kaydetme
   - Middleware'leri kaydetme
   - Konfigürasyona bağlı diğer bootstrap adımlarını çalıştırma

3. **Service Provider Yönetimi:**
   - Bağımlılıkları otomatik kaydetme
   - Çevre bazlı provider çalıştırma 
   - Defer edilmiş provider'ları lazy loading ile yükleme

4. **Çalıştırma:**
   - Request oluşturma/alma
   - Routing işlemlerini gerçekleştirme
   - Middleware zincirini işletme
   - Controller/handler çalıştırma
   - Response oluşturma

5. **Sonlandırma:**
   - Response gönderme (header, body)
   - Kaynakları serbest bırakma

## 🏗️ Service Provider Sistemi

Service Provider sistemi, framework'e modüller eklemek için temiz ve esnek bir yol sağlar:

### Provider Tipleri

1. **Genel Provider'lar:**
   - Temel servisleri kaydeder
   - Örnek: DatabaseServiceProvider, ValidationServiceProvider

2. **Deferred Provider'lar:**
   - Sadece ihtiyaç duyulduğunda yüklenir
   - Performans optimizasyonu sağlar
   - Örnek: MailServiceProvider, QueueServiceProvider

3. **Environment Provider'lar:**
   - Sadece belirli çevrelerde çalışır
   - Örnek: DebugServiceProvider, TestingServiceProvider

### Provider Yaşam Döngüsü

1. **Kayıt (Register):** 
   - Container binding'leri tanımlanır
   - Henüz instance'lar oluşturulmaz

2. **Boot:**
   - Provider'lar arasındaki bağımlılık sırası gözetilir
   - Servisler başlatılır ve konfigüre edilir
   - Core yapısına hook edilir

## 🔭 Bootstrap Sistemi

Bootstrap sınıfları, uygulama başlangıcındaki adımları modüler hale getirir:

### Bootstrap Süreci

1. **Öncelik Sıralaması:**
   - Her bootstrap sınıfı bir öncelik değerine sahiptir
   - Düşük sayılar önce çalışır (örn: exceptions → configs → providers → routes)

2. **Koşullu Çalıştırma:**
   - Çevre bazlı bootstrap
   - Koşullu bootstrap (shouldRun metodu)

3. **Genişletilebilirlik:**
   - Custom bootstrap sınıfları eklenebilir
   - Var olanlar extend edilebilir

## 📝 Best Practices

1. **Service Provider İşlem Dağılımı**

   Büyük provider'lar yerine, tek bir sorumluluğa odaklanan küçük provider'lar oluşturun:

   ```php
   // Önerilen:
   class RouteServiceProvider { /* ... */ }
   class AuthServiceProvider { /* ... */ }
   
   // Önerilmeyen:
   class AppServiceProvider { /* herşey burada */ }
   ```

2. **Defer When Possible**

   Hemen kullanılmayacak servisleri defer edin:

   ```php
   public function isDeferred(): bool
   {
       return true;
   }
   
   public function provides(): array
   {
       return [
           MailService::class,
           Mailer::class
       ];
   }
   ```

3. **Bağımlılık Yönetimi**

   Provider'lar arası bağımlılıkları açıkça belirtin:

   ```php
   public function dependencies(): array
   {
       return [
           CoreServiceProvider::class,
           DatabaseServiceProvider::class
       ];
   }
   ```

4. **Bootstrap Öncelik Mantığı**

   ```
   0-10:  Sistem Kritik (Exception handling, error reporting)
   11-20: Konfigürasyon (Config dosyaları, environment)
   21-30: Servis Kaydı (Service Providers)
   31-40: Middleware/Route kaydı
   41+:   Uygulama Özel (View, template, cache warming)
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-app`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-app`)
5. Pull Request oluşturun