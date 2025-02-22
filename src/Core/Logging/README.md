# Core Logging Katmanı

Modern, esnek ve yüksek performanslı bir loglama sistemi. PSR-3 uyumlu, çoklu kanal desteği ve Aspect Oriented Programming entegrasyonu sunar.

## 🌟 Özellikler

- 🔄 PSR-3 uyumlu logger arayüzü
- 📊 Çoklu log kanalları ve handler desteği
- 🛠️ Attribute tabanlı otomatik loglama (AOP entegrasyonu)
- 📝 JSON, metin ve özel formatlar
- 🚀 Bağlam (context) desteği
- 📦 Processor zinciriyle log verilerini zenginleştirme
- 🔄 Log rotasyonu ve sıkıştırma desteği
- 🔒 Thread-safe loglama
- 🌐 Request ID tracking

## 📂 Dizin Yapısı

```plaintext
Logging/
├── Aspects/
│   └── LogAspect.php
├── Attributes/
│   ├── Log.php
│   └── LogExecution.php
├── Contracts/
│   ├── LoggerInterface.php
│   ├── LogFormatterInterface.php
│   ├── LogHandlerInterface.php
│   └── LogProcessorInterface.php
├── Formatters/
│   ├── AbstractFormatter.php
│   ├── JsonFormatter.php
│   └── LineFormatter.php
├── Handlers/
│   ├── AbstractHandler.php
│   ├── FileHandler.php
│   ├── RotatingFileHandler.php
│   ├── StreamHandler.php
│   └── SyslogHandler.php
├── Processors/
│   ├── IntrospectionProcessor.php
│   ├── MemoryUsageProcessor.php
│   └── WebProcessor.php
├── LogLevel.php
├── LogRecord.php
├── Logger.php
├── LogManager.php
└── LoggerServiceProvider.php
```

## 🚀 Kullanım Örnekleri

### 1. Temel Loglama

```php
// Container'dan logger al
$logger = $container->get(\Core\Logging\Contracts\LoggerInterface::class);

// Loglama
$logger->info('Kullanıcı giriş yaptı', ['user_id' => 1]);
$logger->error('Bir hata oluştu', ['exception' => $exception]);

// Helper fonksiyonu kullanımı
logger()->info('Kullanıcı giriş yaptı', ['user_id' => 1]);
logger('custom_channel')->error('Özel kanal hatası');
```

### 2. Bağlam (Context) ile Loglama

```php
// Tek seferlik bağlam
logger()->info('Sipariş oluşturuldu', [
    'order_id' => $order->id,
    'total' => $order->total,
    'products' => $order->products->count()
]);

// Sürekli bağlam
$logger = logger()->withContext([
    'request_id' => $request->id(),
    'user_id' => auth()->id()
]);

$logger->info('İşlem başladı');
$logger->warning('Stok azalıyor', ['product_id' => 123]);
$logger->error('İşlem başarısız');
// Tüm loglarda request_id ve user_id otomatik eklenecek
```

### 3. Özel Kanalları Kullanma

```php
// Özel kanala loglama
logger('json')->info('API erişimi', ['endpoint' => '/users']);

// Stack kanal (birden çok kanala yazar)
logger('stack')->error('Kritik hata');
```

### 4. Attribute ile Otomatik Loglama

```php
use Core\Logging\Attributes\Log;
use Core\Logging\Attributes\LogExecution;

class OrderService
{
    #[Log(level: 'info', message: 'Sipariş oluşturuluyor', logParams: true, logReturn: true)]
    public function createOrder(array $data): Order
    {
        // Metot giriş/çıkışında otomatik loglanır
        // Parametreler ve dönüş değeri de kaydedilir
    }
    
    #[LogExecution(level: 'critical', channel: 'alerts')]
    public function cancelOrder(int $orderId): void
    {
        // İptal işlemi alerts kanalına critical seviyesinde loglanır
    }
}
```

## ⚙️ Konfigürasyon

```php
// config/logging.php
return [
    'default' => 'stack',
    
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'stderr'],
        ],
        
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/app.log'),
            'level' => 'debug',
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/app.log'),
            'level' => 'debug',
            'days' => 14,
            'formatter' => 'line',
            'processors' => [
                'introspection' => true,
                'web' => true,
            ],
        ],
        
        'json' => [
            'driver' => 'single',
            'path' => storage_path('logs/json.log'),
            'level' => 'debug',
            'formatter' => 'json',
            'processors' => [
                'introspection' => true,
                'web' => true,
                'memory' => true,
            ],
        ],
        
        'stderr' => [
            'driver' => 'stream',
            'url' => 'php://stderr',
            'formatter' => 'line',
            'level' => 'debug',
        ],
        
        'syslog' => [
            'driver' => 'syslog',
            'ident' => env('APP_NAME', 'app'),
            'facility' => LOG_USER,
            'formatter' => 'line',
            'level' => 'debug',
        ],
    ],
];
```

## 📊 Log Seviyeleri

- `debug`: Geliştirme sırasında faydalı detaylı bilgiler
- `info`: Genel bilgi mesajları, normal işlem akışı
- `notice`: Normal ama önemli olaylar
- `warning`: İstisnai durumlar, hata olmayan sorunlar
- `error`: Çalışma zamanı hataları, acil müdahale gerektirmeyen
- `critical`: Kritik koşullar, bileşen kullanılamaz
- `alert`: Acil eylem gerektiren durumlar
- `emergency`: Sistem kullanılamaz durumda

## 🔌 Handler ve Formatter Genişletme

### Özel Handler Oluşturma

```php
use Core\Logging\Handlers\AbstractHandler;
use Core\Logging\LogRecord;

class DatabaseHandler extends AbstractHandler
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo, LogLevel $level = LogLevel::DEBUG)
    {
        parent::__construct($level);
        $this->pdo = $pdo;
    }
    
    protected function write(LogRecord $record): bool
    {
        $formatted = $this->formatter->format($record);
        
        $stmt = $this->pdo->prepare('
            INSERT INTO logs (level, message, context, created_at, channel)
            VALUES (:level, :message, :context, :created_at, :channel)
        ');
        
        return $stmt->execute([
            'level' => $record->level->value,
            'message' => $record->message,
            'context' => json_encode($record->context),
            'created_at' => $record->datetime->format('Y-m-d H:i:s'),
            'channel' => $record->channel
        ]);
    }
}
```

### Özel Formatter Oluşturma

```php
use Core\Logging\Contracts\LogFormatterInterface;
use Core\Logging\LogRecord;

class CustomFormatter implements LogFormatterInterface
{
    public function format(LogRecord $record): string
    {
        return sprintf(
            "[%s] [%s] %s: %s %s",
            $record->datetime->format('Y-m-d H:i:s'),
            $record->channel,
            strtoupper($record->level->value),
            $record->message,
            !empty($record->context) ? json_encode($record->context) : ''
        );
    }
    
    public function formatBatch(array $records): array
    {
        return array_map([$this, 'format'], $records);
    }
}
```

## 🧩 Processor Kullanımı

Processors, log kayıtlarına otomatik olarak ekstra bilgiler ekler:

```php
// Web bilgilerini ekleyen processor
$logger->addProcessor(new WebProcessor());

// Metot çağrı bilgilerini ekleyen processor
$logger->addProcessor(new IntrospectionProcessor());

// Memory kullanım bilgilerini ekleyen processor
$logger->addProcessor(new MemoryUsageProcessor());

// Özel processor oluşturma
$logger->addProcessor(function (LogRecord $record) {
    return $record->withContext([
        'app_version' => APP_VERSION,
        'environment' => APP_ENV
    ]);
});
```

## 🔌 ServiceProvider Entegrasyonu

LoggerServiceProvider, framework'ünüze loglama sisteminizi entegre eder:

```php
// Application bootstrap sırasında
$app->registerProvider(LoggerServiceProvider::class);

// Bu provider, container'a şu servisleri kaydeder:
// - LoggerInterface
// - LogManager
// - 'logger' alias
```

## 🛠️ Log Aspect Entegrasyonu

LogAspect, AOP katmanı ile entegre çalışarak metotlara otomatik loglama yeteneği katar:

```php
// Service provider boot metodu içinde
public function boot(ApplicationInterface $app): void
{
    // Aspect registry mevcut ise LogAspect'i kaydet
    if ($container->has(AspectRegistryInterface::class)) {
        $aspectRegistry = $container->get(AspectRegistryInterface::class);
        $logAspect = new LogAspect($container->get(LoggerInterface::class));
        $aspectRegistry->register($logAspect);
    }
}
```

## 🔍 Best Practices

1. **Uygun Log Seviyelerini Kullanma**

   ```php
   // DEBUG: Geliştirme aşamasında yardımcı olacak detaylar
   logger()->debug('DB sorgusu çalıştırılıyor', ['query' => $sql]);
   
   // INFO: Normal bilgi mesajları
   logger()->info('Kullanıcı giriş yaptı', ['id' => $userId]);
   
   // WARNING: Hata olmayan sorunlar
   logger()->warning('Stok az', ['product_id' => $id, 'qty' => $qty]);
   
   // ERROR: Çalışma zamanı hataları
   logger()->error('Ödeme başarısız', ['exception' => $e->getMessage()]);
   
   // CRITICAL: Kritik hatalar
   logger()->critical('Veritabanı bağlantısı kurulamadı');
   ```

2. **Yapılandırılmış Loglama**

   ```php
   // Kötü: Sadece metin
   logger()->error('Kullanıcı 1234 sipariş 5678 oluşturamadı: Stok yetersiz');
   
   // İyi: Yapılandırılmış context
   logger()->error('Sipariş oluşturulamadı', [
       'user_id' => 1234,
       'order_id' => 5678,
       'reason' => 'Stok yetersiz',
       'available_stock' => 5,
       'requested_qty' => 10
   ]);
   ```

3. **Dağıtık Sistemlerde Trace ID Kullanımı**

   ```php
   // Tüm servisler arasında izlenebilirlik
   $traceId = $request->header('X-Trace-Id') ?? generateTraceId();
   $logger = logger()->withContext(['trace_id' => $traceId]);
   
   // Tüm loglarda trace_id olacak
   $logger->info('İşlem başladı');
   ```

4. **Çevre Bazlı Loglama**

   ```php
   // config/logging.php
   return [
       'channels' => [
           'production' => [
               'driver' => 'daily',
               'level' => 'warning', // Sadece warning ve üstü
           ],
           'development' => [
               'driver' => 'single',
               'level' => 'debug', // Tüm loglar
           ],
       ],
       'default' => env('APP_ENV') === 'production' ? 'production' : 'development',
   ];
   ```

5. **Hassas Verileri Maskeleme**

   ```php
   $logger->info('Kullanıcı bilgileri güncellendi', [
       'user_id' => $user->id,
       'email' => maskEmail($user->email), // a***@example.com
       'credit_card' => maskCreditCard($user->cc), // ****-****-****-1234
   ]);
   ```

## 🚀 Performans İpuçları

1. **Gereksiz Log Mesajlarından Kaçınma**
    - Sıcak kod yollarında `logger()->isEnabled('debug')` kontrolü yapın
    - Üretim ortamında debug loglarını kapatın

2. **Lazy Context Değerlendirmesi**
   ```php
   // İyi: Context sadece gerekliyse oluşturuluyor
   logger()->error('Hata oluştu', $this->isDebug() ? ['stack' => $e->getTraceAsString()] : []);
   
   // Daha iyi: Callback kullanımı
   logger()->error('Hata oluştu', function() use ($e) {
       return ['stack' => $e->getTraceAsString()];
   });
   ```

3. **Batch Logging Kullanımı**
    - Çok sayıda log üretilen durumlarda batch/bulk kayıt kullanın
    - Log buffering ile performans kaybını azaltın

4. **Asenkron Logging**
    - Kritik olmayan loglarda asenkron handler kullanın
    - Queue veya async worker ile loglama işlemlerini arka plana alın

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-logging`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-logging`)
5. Pull Request oluşturun