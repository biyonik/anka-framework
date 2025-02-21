# Aspect Oriented Programming (AOP) Katmanı

Framework'ün Aspect Oriented Programming desteği sağlayan, çapraz kesim problemlerini (cross-cutting concerns) modüler bir şekilde yönetmeyi sağlayan güçlü katmanı.

## 🌟 Özellikler

- PHP 8.2+ Attribute tabanlı aspect tanımlama
- Farklı advice tipleri (Before, After, AfterReturning, AfterThrowing, Around)
- Esnek pointcut ifade sistemi (Method eşleştirme, annotation tabanlı eşleştirme)
- Runtime method interception
- Proxy sınıf oluşturma ve önbellekleme
- Öncelik bazlı aspect sıralama
- Container entegrasyonu
- Düşük overhead ve yüksek performans
- Hazır kullanım için özel attribute'lar (Transactional, Cacheable, LogExecution)

## 📂 Dizin Yapısı

```plaintext
Aspects/
├── Advice/
│   ├── AfterAdvice.php
│   ├── AfterReturningAdvice.php
│   ├── AfterThrowingAdvice.php
│   ├── AroundAdvice.php
│   └── BeforeAdvice.php
├── Attributes/
│   ├── After.php
│   ├── AfterReturning.php
│   ├── AfterThrowing.php
│   ├── Around.php
│   ├── Aspect.php
│   ├── Before.php
│   ├── Cacheable.php
│   ├── LogExecution.php
│   ├── Pointcut.php
│   └── Transactional.php
├── Contracts/
│   ├── AdviceInterface.php
│   ├── AfterAdviceInterface.php
│   ├── AfterReturningAdviceInterface.php
│   ├── AfterThrowingAdviceInterface.php
│   ├── AroundAdviceInterface.php
│   ├── AspectInterface.php
│   ├── AspectRegistryInterface.php
│   ├── BeforeAdviceInterface.php
│   ├── JoinPointInterface.php
│   └── PointcutInterface.php
├── Pointcut/
│   ├── AnnotationPointcut.php
│   ├── CompositePointcut.php
│   └── MethodPointcut.php
├── Providers/
│   └── AspectServiceProvider.php
├── AbstractAdvice.php
├── AbstractAspect.php
├── AbstractPointcut.php
├── AdviceChain.php
├── AspectRegistry.php
├── AttributeListenerManager.php
├── DynamicAspect.php
├── JoinPoint.php
├── MethodInvoker.php
└── ProxyFactory.php
```

## 🚀 Kullanım Örnekleri

### 1. Attribute ile Aspect Tanımlama

```php
use Framework\Core\Aspects\Attributes\Aspect;
use Framework\Core\Aspects\Attributes\Before;
use Framework\Core\Aspects\Attributes\AfterReturning;
use Framework\Core\Aspects\Contracts\JoinPointInterface;

#[Aspect]
class LoggingAspect
{
    #[Before('execution(App\Service\*.*(..))')]
    public function logMethodEntry(JoinPointInterface $joinPoint): void
    {
        $method = $joinPoint->getMethodName();
        $class = $joinPoint->getClassName();
        
        echo "LOG: Entering {$class}::{$method}" . PHP_EOL;
    }
    
    #[AfterReturning('execution(App\Service\*.*(..))')]
    public function logMethodExit(JoinPointInterface $joinPoint, mixed $result): mixed
    {
        $method = $joinPoint->getMethodName();
        $class = $joinPoint->getClassName();
        
        echo "LOG: Exiting {$class}::{$method} with result: " . json_encode($result) . PHP_EOL;
        
        return $result;
    }
}
```

### 2. Özel Aspect Oluşturma

```php
use Framework\Core\Aspects\AbstractAspect;
use Framework\Core\Aspects\Pointcut\MethodPointcut;
use Framework\Core\Aspects\Contracts\JoinPointInterface;

class PerformanceAspect extends AbstractAspect
{
    public function __construct()
    {
        parent::__construct('performance.aspect', 20);
        
        // Pointcut tanımla
        $pointcut = new MethodPointcut('*Service');
        $this->addPointcut($pointcut);
    }
    
    public function before(JoinPointInterface $joinPoint): void
    {
        $joinPoint->getTarget()->startTime = microtime(true);
    }
    
    public function afterReturning(JoinPointInterface $joinPoint, mixed $result): mixed
    {
        $target = $joinPoint->getTarget();
        $executionTime = microtime(true) - ($target->startTime ?? 0);
        
        echo sprintf(
            'Method %s::%s executed in %.4f seconds',
            $joinPoint->getClassName(),
            $joinPoint->getMethodName(),
            $executionTime
        );
        
        return $result;
    }
}

// Aspect'i kaydettirme
$aspectRegistry->register(new PerformanceAspect());
```

### 3. Annotation Tabanlı Pointcut

```php
use Framework\Core\Aspects\Attributes\Aspect;
use Framework\Core\Aspects\Attributes\Before;
use Framework\Core\Aspects\Attributes\After;
use Framework\Core\Aspects\Contracts\JoinPointInterface;

#[Aspect]
class SecurityAspect
{
    #[Before('@App\Attributes\Secured')]
    public function checkSecurity(JoinPointInterface $joinPoint): void
    {
        $user = Auth::getUser();
        
        if (!$user || !$user->hasPermission('admin')) {
            throw new SecurityException('Access denied');
        }
    }
}

// Secured attribute kullanımı
class AdminController
{
    #[Secured]
    public function manageUsers(): void
    {
        // ...
    }
}
```

### 4. Around Advice Kullanımı

```php
use Framework\Core\Aspects\Attributes\Aspect;
use Framework\Core\Aspects\Attributes\Around;
use Framework\Core\Aspects\Contracts\JoinPointInterface;

#[Aspect]
class TransactionAspect
{
    #[Around('@App\Attributes\Transactional')]
    public function wrapInTransaction(JoinPointInterface $joinPoint): mixed
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            $result = $joinPoint->proceed(); // Metodu çağır
            
            $db->commit();
            return $result;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}

// Controller'da kullanım
class OrderController
{
    #[Transactional]
    public function createOrder(array $data): Order
    {
        $order = new Order($data);
        $order->save();
        
        foreach ($data['items'] as $item) {
            $orderItem = new OrderItem($order->id, $item);
            $orderItem->save();
        }
        
        return $order;
    }
}
```

### 5. Container ile Kullanım

```php
// Service Provider kaydı
$container->addProvider(AspectServiceProvider::class);

// AttributeListenerManager ve aspect dizini yapılandırması
$config->set('aspects.paths', [
    __DIR__ . '/app/Aspects' => 'App\\Aspects'
]);

// Servisleri al
$registry = $container->get(AspectRegistryInterface::class);
$proxyFactory = $container->get(ProxyFactory::class);

// Proxy oluştur
$userService = $proxyFactory->createProxyInstance(UserService::class);

// Proxy'yi kullan (otomatik olarak aspect'ler uygulanır)
$user = $userService->findById(123);
```

## 🔧 Pointcut İfadeleri

AOP katmanı, farklı pointcut ifadelerini destekler:

### Method Pointcut

```php
// Belirli bir metod
'execution(App\Service\UserService.findById)'

// Joker karakter ile eşleştirme
'execution(App\Service\*.find*)'

// Tüm metodlar
'execution(App\Service\UserService.*)'

// Tüm service sınıfları
'execution(*Service.*)'
```

### Annotation Pointcut

```php
// Belirli bir attribute ile işaretlenmiş metod/sınıflar
'@App\Attributes\Secured'

// Framework'ün hazır attribute'ları
'@Framework\Core\Aspects\Attributes\Transactional'
'@Framework\Core\Aspects\Attributes\Cacheable'
'@Framework\Core\Aspects\Attributes\LogExecution'
```

### Composite Pointcut

```php
// PointcutInterface'i implemente eden sınıfları birleştirme
$composite = new CompositePointcut([$pointcut1, $pointcut2], false); // OR kullan
$composite = new CompositePointcut([$pointcut1, $pointcut2], true);  // AND kullan (varsayılan)
```

## 🏗️ Aspect Lifecycle

1. **Tanımlama**:
    - Attribute veya extend ile Aspect tanımlama
    - Pointcut'ların belirlenmesi
    - Advice metodlarının tanımlanması

2. **Kayıt**:
    - AspectRegistry'ye kaydedilme
    - Attribute'lu aspect'lerin taranması

3. **Uygulama**:
    - ProxyFactory ile proxy sınıflar oluşturulması
    - MethodInvoker ile metod çağrılarının yakalanması
    - JoinPoint oluşturulması

4. **Advice Çalıştırma**:
    - Önceliğe göre sıralanmış advice'ların çalıştırılması
    - Before → Around → After/AfterReturning/AfterThrowing sırası

## 🚦 Hazır Kullanım Aspect'leri

### 1. Transaction Yönetimi

```php
// Servis sınıfında kullanım
use Framework\Core\Aspects\Attributes\Transactional;

class OrderService
{
    #[Transactional]
    public function createOrder(array $data): Order
    {
        // İşlemler otomatik olarak transaction içinde çalışır
        // Hata olursa rollback yapılır
    }
    
    #[Transactional(readOnly: true)]
    public function getOrderStats(): array
    {
        // Salt okunur transaction
    }
}
```

### 2. Cache Yönetimi

```php
// Servis sınıfında kullanım
use Framework\Core\Aspects\Attributes\Cacheable;

class ProductService
{
    #[Cacheable(ttl: 3600)]
    public function getProduct(int $id): Product
    {
        // Bu metodun sonucu 1 saat önbelleğe alınır
        // Aynı id ile çağrıldığında DB'ye gitmeden önbellekten döner
    }
    
    #[Cacheable(key: "'products.list.'.{#category}")]
    public function getProductsByCategory(string $category): array
    {
        // Kategori bazında önbellekleme
        // Her kategori için farklı önbellek anahtarı oluşturulur
    }
    
    #[Cacheable(unless: ["#result.isEmpty()"])]
    public function searchProducts(string $query): array
    {
        // Sadece sonuç boş değilse önbellekle
    }
}
```

### 3. Loglama

```php
// Controller veya servis sınıfında kullanım
use Framework\Core\Aspects\Attributes\LogExecution;

#[LogExecution(level: 'INFO', logParams: true, logResult: true)]
class ApiController
{
    // Tüm sınıf metodları loglanır
    
    #[LogExecution(level: 'DEBUG', logExecutionTime: true)]
    public function getUsers(): array
    {
        // Bu metod DEBUG seviyesinde ve yürütme süresi ile loglanır
    }
}
```

## 🔍 Best Practices

1. **Aspect Önceliklerini Doğru Ayarlama**

   ```php
   // Önce güvenlik kontrolleri yapılmalı
   #[Aspect(priority: 10)]
   class SecurityAspect {}
   
   // Sonra transaction başlatılmalı
   #[Aspect(priority: 20)]
   class TransactionAspect {}
   
   // En son loglama yapılmalı
   #[Aspect(priority: 100)]
   class LoggingAspect {}
   ```

2. **Modüler Aspect Tasarımı**

   Her aspect tek bir sorumluluk almalıdır:

   ```php
   // İyi: Her aspect tek bir iş yapıyor
   class LoggingAspect {}
   class TransactionAspect {}
   class CacheAspect {}
   
   // Kaçınılmalı: Tek bir aspect birçok iş yapıyor
   class SuperAspect {} // Loglama, cache, transaction, güvenlik...
   ```

3. **JoinPoint İçinde Argüman Manipülasyonu**

   ```php
   #[Before('execution(UserService.create(..))')]
   public function sanitizeUserData(JoinPointInterface $joinPoint): void
   {
       $args = $joinPoint->getArguments();
       
       // E-posta adresini küçük harfe çevir
       if (isset($args[0]['email'])) {
           $args[0]['email'] = strtolower($args[0]['email']);
       }
       
       // Argümanları güncelle
       $joinPoint->setArguments($args);
   }
   ```

4. **Stackable Attribute Kullanımı**

   ```php
   class UserService
   {
       #[Transactional]
       #[LogExecution]
       #[Cacheable(ttl: 3600)]
       public function getUserProfile(int $userId): array
       {
           // Bu metod transaction içinde çalışır, loglanır ve önbelleğe alınır
       }
   }
   ```

5. **Around Advice'ı Dikkatli Kullanma**

   Around advice en güçlü ama aynı zamanda en riskli advice tipidir:

   ```php
   #[Around('execution(*.*(..))')]
   public function aroundAdvice(JoinPointInterface $joinPoint): mixed
   {
       // Orijinal metodu çağırmazsa çalışmaz!
       return $joinPoint->proceed();
       
       // Metodun çalışıp çalışmayacağına karar verebilir
       if (shouldProceed()) {
           return $joinPoint->proceed();
       }
       
       return null;
   }
   ```

## 🔧 Container Entegrasyonu

AOP katmanı, DI container'a entegre edilebilir:

```php
// AspectServiceProvider'ı kaydet
$app->registerProvider(AspectServiceProvider::class);

// Bu kayıt, container'a şu servisleri ekler:
// - AspectRegistryInterface
// - MethodInvoker
// - ProxyFactory
// - AttributeListenerManager

// ProxyFactory kullanarak proxy oluşturma
$proxyFactory = $app->container()->get(ProxyFactory::class);
$userService = $proxyFactory->createProxyInstance(UserService::class, [$dependency1, $dependency2]);

// Veya container proxy factory hook'u ile otomatik olarak proxy oluşturma
// Her resolve işleminde aspect'leri uygular
```

## 🔥 Performans Optimizasyonları

1. **Proxy Sınıf Önbellekleme**
    - Üretilen proxy sınıfları disk'e kaydedilir ve tekrar kullanılır
    - Her çalıştırmada yeniden oluşturulmaz

2. **Advice Önbellekleme**
    - AspectRegistry, metod eşleşmelerini önbelleğe alır
    - Her metod çağrısında tüm aspect'ler tekrar kontrol edilmez

3. **Seçici Aspect Uygulaması**
    - Sadece gerekli metodlara proxy oluşturulur
    - Eşleşmeyen metodlar için overhead oluşmaz

## 🚀 Genişletme

AOP katmanı, yeni pointcut ve advice tipleri eklenerek genişletilebilir:

```php
// Özel pointcut tipi oluşturma
class ParameterTypePointcut extends AbstractPointcut
{
    public function matches(\ReflectionMethod $method, ?object $instance = null): bool
    {
        foreach ($method->getParameters() as $param) {
            if ($param->hasType() && $param->getType()->getName() === $this->pattern) {
                return true;
            }
        }
        
        return false;
    }
}

// Özel advice tipi oluşturma
class LoggingAdvice extends AbstractAdvice
{
    public function getType(): string
    {
        return 'logging';
    }
    
    public function log(JoinPointInterface $joinPoint): void
    {
        // Loglama işlemleri
    }
}
```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-aspect`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-aspect`)
5. Pull Request oluşturun