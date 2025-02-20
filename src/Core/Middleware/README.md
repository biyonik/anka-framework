# Middleware Katmanı

PSR-15 uyumlu, güçlü ve esnek middleware sistemi. Request/Response döngüsünün yönetimi, HTTP işlemlerinin filtrelenmesi ve manipülasyonu için kapsamlı bir altyapı sunar.

## 🌟 Özellikler

- PSR-15 uyumlu middleware implementasyonu
- Öncelik bazlı middleware sıralama
- Grup bazlı middleware yönetimi
- Error handling desteği
- İmmutable yapı
- Closure middleware desteği
- Conditional middleware çalıştırma

## 📂 Dizin Yapısı

```plaintext
Middleware/
├── Interfaces/
│   ├── MiddlewareInterface.php
│   ├── RequestHandlerInterface.php
│   └── MiddlewareStackInterface.php
├── Handlers/
│   └── RequestHandler.php
├── MiddlewareStack.php
└── MiddlewareDispatcher.php
```

## 🚀 Kullanım Örnekleri

### 1. Basit Middleware Oluşturma

```php
class LoggerMiddleware implements MiddlewareInterface
{
    private Logger $logger;
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Request'i logla
        $this->logger->info('Request başladı: ' . $request->getUri());
        
        // Request'i işle
        $response = $handler->handle($request);
        
        // Response'u logla
        $this->logger->info('Response tamamlandı: ' . $response->getStatusCode());
        
        return $response;
    }
    
    public function shouldRun(ServerRequestInterface $request): bool
    {
        return true; // Her zaman çalış
    }
    
    public function getPriority(): int
    {
        return 100; // Yüksek öncelik
    }
}
```

### 2. Conditional Middleware

```php
class ApiAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('Authorization');
        
        if (empty($token)) {
            return new Response(401, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Unauthorized'])
            );
        }
        
        // Token doğrulama işlemleri...
        
        return $handler->handle($request);
    }
    
    public function shouldRun(ServerRequestInterface $request): bool
    {
        // Sadece /api ile başlayan route'larda çalış
        return str_starts_with($request->getUri()->getPath(), '/api');
    }
    
    public function getPriority(): int
    {
        return 1000; // En yüksek öncelik
    }
}
```

### 3. Middleware Stack Kullanımı

```php
// Stack oluştur
$stack = new MiddlewareStack();

// Middleware'leri ekle
$stack->add(new LoggerMiddleware($logger));
$stack->add(new ApiAuthMiddleware());
$stack->add(new CorsMiddleware());

// Grup oluştur
$stack->group('api', [
    new RateLimitMiddleware(),
    new ApiVersionMiddleware()
]);
```

### 4. Dispatcher Kullanımı

```php
// Dispatcher oluştur
$dispatcher = new MiddlewareDispatcher($stack);

// Error handler ekle
$dispatcher->setErrorHandler(function (\Throwable $e, ServerRequestInterface $request) {
    return new Response(500, ['Content-Type' => 'application/json'], 
        json_encode(['error' => $e->getMessage()])
    );
});

// Fallback handler ekle
$dispatcher->setFallbackHandler(function () {
    return new Response(404, ['Content-Type' => 'application/json'],
        json_encode(['error' => 'Route not found'])
    );
});

// Request'i işle
$response = $dispatcher->handle($request);
```

### 5. Closure Middleware

```php
$dispatcher->addClosure(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('X-Response-Time', microtime(true) - START_TIME);
}, 500);
```

### 6. Grup Bazlı İşlem

```php
// API middleware grubu
$dispatcher->group('api', [
    new ApiAuthMiddleware(),
    new RateLimitMiddleware(),
    new ApiVersionMiddleware()
]);

// Admin middleware grubu
$dispatcher->group('admin', [
    new AdminAuthMiddleware(),
    new AuditLogMiddleware(),
    new CsrfMiddleware()
]);

// Sadece API middleware'lerini çalıştır
$response = $dispatcher->handleGroup('api', $request);
```

## 🔧 Best Practices

1. **Öncelik Yönetimi**
   ```php
   // Yüksek öncelikli middleware'ler
   const PRIORITY_HIGH = 1000;   // Auth, Security
   const PRIORITY_MID = 500;     // Logging, Monitoring
   const PRIORITY_LOW = 100;     // Response manipulation
   ```

2. **Error Handling**
   ```php
   $dispatcher->setErrorHandler(function (\Throwable $e, ServerRequestInterface $request) {
       $context = [
           'url' => (string) $request->getUri(),
           'method' => $request->getMethod(),
           'error' => $e->getMessage()
       ];
       
       // Log error
       $logger->error('Middleware error', $context);
       
       // Return error response
       return new Response(500, ['Content-Type' => 'application/json'],
           json_encode(['error' => 'Internal Server Error'])
       );
   });
   ```

3. **Performance İzleme**
   ```php
   class PerformanceMiddleware implements MiddlewareInterface
   {
       public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
       {
           $start = microtime(true);
           
           $response = $handler->handle($request);
           
           $duration = microtime(true) - $start;
           
           return $response->withHeader('X-Response-Time', sprintf('%.4f', $duration));
       }
   }
   ```

4. **Request Enrichment**
   ```php
   class UserContextMiddleware implements MiddlewareInterface
   {
       public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
       {
           $user = $this->auth->getUser();
           
           // Request'e user context ekle
           $request = $request->withAttribute('user', $user);
           
           return $handler->handle($request);
       }
   }
   ```

## 🔍 Debug İpuçları

1. Stack'teki middleware'leri listele:
```php
foreach ($dispatcher->getStack()->getAll() as $middleware) {
    echo sprintf(
        "Middleware: %s, Priority: %d\n",
        get_class($middleware),
        $middleware->getPriority()
    );
}
```

2. Middleware çalışma durumunu kontrol et:
```php
$debugMiddleware = new class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        echo sprintf("Processing request: %s\n", $request->getUri());
        $response = $handler->handle($request);
        echo sprintf("Response status: %d\n", $response->getStatusCode());
        return $response;
    }
};
```

## 📝 Önemli Notlar

1. Middleware'ler immutable olmalıdır
2. Her middleware tek bir sorumluluk almalıdır
3. Öncelik değerleri anlamlı olmalıdır
4. Error handling her zaman yapılmalıdır
5. Response manipülasyonu dikkatli yapılmalıdır

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-middleware`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add rate limiting middleware'`)
4. Branch'inizi push edin (`git push origin feature/amazing-middleware`)
5. Pull Request oluşturun