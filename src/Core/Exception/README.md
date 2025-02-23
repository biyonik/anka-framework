# Exception Katmanı

Framework'ün merkezi hata yönetim sistemi. Farklı ortamlar (HTTP, Console) için özelleştirilmiş exception handling, loglama entegrasyonu ve esnek hata raporlama özellikleri sunar.

## 🌟 Özellikler

- Merkezi exception handling sistemi
- PSR-3 uyumlu loglama entegrasyonu
- HTTP ve Console ortamları için özel handler'lar
- Production ve development modları için farklı davranışlar
- Özelleştirilebilir hata raporlama
- Kolay genişletilebilir yapı
- Attribute tabanlı exception handling desteği

## 📂 Dizin Yapısı

```plaintext
Exception/
├── Contracts/
│   ├── ExceptionHandlerInterface.php
│   ├── ReportableExceptionInterface.php
│   └── RenderableExceptionInterface.php
├── Handlers/
│   ├── AbstractExceptionHandler.php
│   ├── GlobalExceptionHandler.php
│   ├── HttpExceptionHandler.php
│   └── ConsoleExceptionHandler.php
├── AuthenticationException.php
├── AuthorizationException.php
├── BaseException.php
├── DatabaseException.php
├── HttpException.php
├── NotFoundHttpException.php
├── ValidationException.php
└── ExceptionServiceProvider.php
```

## 🚀 Kullanım Örnekleri

### 1. Temel Exception Kullanımı

```php
use Framework\Core\Exception\BaseException;

class CustomException extends BaseException
{
    protected string $defaultMessage = 'Bir hata oluştu';
    protected int $defaultCode = 1000;
    
    protected function getLogLevel(): string
    {
        return 'error';
    }
}

// Kullanım
throw new CustomException('İşlem başarısız', 1001, null, [
    'user_id' => 1,
    'action' => 'create'
]);
```

### 2. HTTP Exception Kullanımı

```php
use Framework\Core\Exception\HttpException;
use Framework\Core\Exception\NotFoundHttpException;

// Genel HTTP exception
throw new HttpException(
    statusCode: 403,
    message: 'Bu işlem için yetkiniz yok',
    headers: ['X-Error-Type' => 'authorization']
);

// Özel HTTP exception
throw new NotFoundHttpException('Kullanıcı bulunamadı');
```

### 3. Validation Exception

```php
use Framework\Core\Exception\ValidationException;

// Form validation hatası
throw new ValidationException([
    'email' => ['Geçerli bir e-posta adresi giriniz'],
    'password' => ['Şifre en az 8 karakter olmalıdır']
]);
```

### 4. Database Exception

```php
use Framework\Core\Exception\DatabaseException;

try {
    // Veritabanı işlemleri
} catch (\PDOException $e) {
    throw new DatabaseException(
        'Veritabanı hatası',
        previous: $e,
        context: [
            'query' => $query,
            'bindings' => $bindings
        ]
    );
}
```

### 5. Özel Handler Oluşturma

```php
use Framework\Core\Exception\Handlers\AbstractExceptionHandler;

class ApiExceptionHandler extends AbstractExceptionHandler
{
    protected function getCurrentRequest(): mixed
    {
        return $this->requestFactory->createFromGlobals();
    }
    
    public function render(mixed $request, \Throwable $e): mixed
    {
        $data = [
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];
        
        if (!$this->config->getEnvironment()->is('production')) {
            $data['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
        
        return $this->responseFactory->createJsonResponse(
            $data,
            $this->getHttpStatusCode($e)
        );
    }
}
```

## 🔄 ServiceProvider Entegrasyonu

```php
// config/app.php
return [
    'providers' => [
        \Framework\Core\Exception\ExceptionServiceProvider::class
    ]
];
```

## 📝 Best Practices

1. **Exception Tipleri**

   Amaca uygun exception sınıfını kullanın:

   ```php
   // İyi
   throw new NotFoundHttpException('Kullanıcı bulunamadı');
   
   // Kaçının
   throw new \Exception('404 - Kullanıcı bulunamadı');
   ```

2. **Context Bilgisi**

   Hata ayıklama için yeterli context ekleyin:

   ```php
   throw new CustomException('Hata', context: [
       'user_id' => $userId,
       'action' => $action,
       'params' => $params
   ]);
   ```

3. **Loglama Seviyesi**

   Doğru log seviyesi kullanın:

   ```php
   protected function getLogLevel(): string
   {
       return match($this->getCode()) {
           404 => 'notice',
           403 => 'warning',
           500 => 'error',
           default => 'info'
       };
   }
   ```

4. **Production Güvenliği**

   Production'da hassas bilgileri gizleyin:

   ```php
   if ($this->config->getEnvironment()->is('production')) {
       return 'Internal Server Error';
   }
   
   return $exception->getMessage();
   ```

## 🔧 Özelleştirme

### 1. Özel Exception Sınıfı

```php
class PaymentException extends BaseException
{
    protected string $defaultMessage = 'Ödeme işlemi başarısız';
    
    protected function getLogLevel(): string
    {
        return 'critical';
    }
    
    public function render(mixed $request): mixed
    {
        return response()->json([
            'error' => 'payment_failed',
            'message' => $this->getMessage()
        ], 402);
    }
}
```

### 2. Özel Handler

```php
class ApiExceptionHandler extends AbstractExceptionHandler
{
    protected array $dontReport = [
        ValidationException::class,
        AuthenticationException::class
    ];
    
    protected function renderApiResponse(\Throwable $e): mixed
    {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        if ($e instanceof ValidationException) {
            $response['errors'] = $e->getErrors();
        }
        
        return $this->responseFactory->createJsonResponse(
            $response,
            $this->getStatusCode($e)
        );
    }
}
```

## 🔍 Handler Seçimi

Framework, ortama göre uygun handler'ı otomatik seçer:

- HTTP istekleri → HttpExceptionHandler
- Console komutları → ConsoleExceptionHandler
- Özel durumlar → GlobalExceptionHandler

## 🎯 HTTP Status Kodları

- 400 Bad Request
- 401 Unauthorized (AuthenticationException)
- 403 Forbidden (AuthorizationException)
- 404 Not Found (NotFoundHttpException)
- 422 Unprocessable Entity (ValidationException)
- 500 Internal Server Error (Genel hatalar)

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-exception`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-exception`)
5. Pull Request oluşturun