# Event Katmanı

Güçlü ve esnek bir olay yayınlama ve dinleme sistemi sunan Framework'ün Event katmanı.

## 🌟 Özellikler

- Yüksek performanslı event dispatch sistemi
- Domain Events için altyapı
- Öncelik bazlı dinleyici sıralama
- Propagasyon kontrolü
- Type-safe event handling
- Event Sourcing yapısı için temel
- Generic ve özelleştirilmiş olaylar
- PHP 8 Attribute tabanlı event listener yönetimi
- Dizin bazlı otomatik listener kaydı

## 📂 Dizin Yapısı

```plaintext
Event/
├── Attributes/
│   └── Listener.php
├── Contracts/
│   ├── EventInterface.php
│   ├── ListenerInterface.php
│   └── EventDispatcherInterface.php
├── Domain/
│   ├── DomainEvent.php
│   └── Examples/
│       ├── UserCreatedEvent.php
│       └── UserCreatedListener.php
├── Exceptions/
│   └── EventException.php
├── AbstractEvent.php
├── AbstractListener.php
├── AttributeListenerManager.php
├── EventDispatcher.php
└── GenericEvent.php
```

## 🚀 Kullanım Örnekleri

### 1. Attribute Tabanlı Listener

```php
// Class ile attribute kullanımı
#[Listener('app.startup', priority: 10)]
class AppStartupListener
{
    public function __invoke(EventInterface $event): void
    {
        echo "Uygulama başladı: " . $event->getTimestamp()->format('Y-m-d H:i:s');
    }
}

// Metot üzerinde attribute kullanımı
class UserEventHandler
{
    #[Listener('user.created')]
    public function onUserCreated(EventInterface $event): void
    {
        if ($event instanceof UserCreatedEvent) {
            echo "Kullanıcı oluşturuldu: " . $event->getEmail();
        }
    }
    
    #[Listener(['user.updated', 'user.deleted'])]
    public function onUserChanged(EventInterface $event): void
    {
        echo "Kullanıcı değişikliği: " . $event->getName();
    }
}

// Attribute listenerları kaydettirme
$dispatcher = new EventDispatcher();
$manager = new AttributeListenerManager($dispatcher);

// Tek bir sınıf
$manager->registerClassListeners(AppStartupListener::class);

// Veya bir dizindeki tüm listenerlar
$manager->registerListenersFromDirectory(__DIR__ . '/Listeners');
```

### 2. Temel Kullanım

```php
// Event Dispatcher oluştur
$dispatcher = new EventDispatcher();

// Listener ekle
$dispatcher->addListener('app.started', function ($event) {
    echo "Uygulama başladı!";
    echo "Olay zamanı: " . $event->getTimestamp()->format('Y-m-d H:i:s');
    echo "Environment: " . $event->get('environment');
});

// Event oluştur ve dispatch et
$event = new GenericEvent('app.started', [
    'environment' => 'production',
    'debug' => false
]);

$dispatcher->dispatch($event);
```

### 2. Öncelik Bazlı Sıralama

```php
// Yüksek öncelikli listener (ilk çalışır)
$dispatcher->addListener('app.request', function ($event) {
    echo "[1] Request alındı: " . $event->get('path');
})->setPriority(10);

// Düşük öncelikli listener (sonra çalışır)
$dispatcher->addListener('app.request', function ($event) {
    echo "[2] Request işleniyor: " . $event->get('method') . " " . $event->get('path');
})->setPriority(20);

// Event oluştur ve dispatch et
$event = new GenericEvent('app.request', [
    'path' => '/users',
    'method' => 'GET'
]);

$dispatcher->dispatch($event);
```

### 3. Özel Event Sınıfı

```php
// UserCreatedEvent.php
class UserCreatedEvent extends AbstractEvent
{
    private const EVENT_NAME = 'user.created';
    
    public function __construct(int $userId, string $email, string $name)
    {
        parent::__construct([
            'user_id' => $userId,
            'email' => $email,
            'name' => $name
        ]);
    }
    
    public function getName(): string
    {
        return self::EVENT_NAME;
    }
    
    public function getUserId(): int
    {
        return $this->get('user_id');
    }
    
    public function getEmail(): string
    {
        return $this->get('email');
    }
    
    public function getName(): string
    {
        return $this->get('name');
    }
}

// Event oluştur ve gönder
$event = new UserCreatedEvent(123, 'john@example.com', 'John Doe');
$dispatcher->dispatch($event);
```

### 4. Özel Listener Sınıfı

```php
class UserCreatedListener extends AbstractListener
{
    private MailService $mailService;
    
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    
    public static function getSubscribedEvents(): string|array
    {
        return ['user.created'];
    }
    
    public function handle(EventInterface $event): void
    {
        if (!$event instanceof UserCreatedEvent) {
            return;
        }
        
        // Hoşgeldin e-postası gönder
        $this->mailService->sendWelcomeEmail(
            $event->getEmail(),
            $event->getName()
        );
        
        echo "Hoşgeldin e-postası gönderildi: " . $event->getEmail();
    }
}

// Listener'ı kaydettirme
$dispatcher->addSubscriber(new UserCreatedListener($mailService));
```

### 5. Domain Events

```php
// UserEntity.php
class User
{
    private int $id;
    private string $email;
    private string $name;
    private array $domainEvents = [];
    
    public function create(string $email, string $name): void
    {
        $this->email = $email;
        $this->name = $name;
        
        // Domain event ekle
        $this->recordEvent(new UserCreatedEvent(
            $this->id,
            $this->email,
            $this->name
        ));
    }
    
    public function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
    
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}

// UserService.php
class UserService
{
    private EventDispatcherInterface $eventDispatcher;
    
    public function createUser(string $email, string $name): User
    {
        $user = new User();
        $user->create($email, $name);
        
        // Domain events'i dispatch et
        foreach ($user->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
        
        return $user;
    }
}
```

### 6. Bir Kerelik Listener

```php
// Sadece bir kez çalışan listener
$dispatcher->once('app.shutdown', function ($event) {
    echo "Uygulama kapanıyor!";
});

// İki kez dispatch edilse bile sadece bir kez çalışır
$event = new GenericEvent('app.shutdown');
$dispatcher->dispatch($event);
$dispatcher->dispatch($event); // İkinci çağrıda listener çalışmaz
```

## 🔄 Event Propagasyonu

```php
// Propagasyonu durduran listener
$dispatcher->addListener('app.request', function ($event) {
    echo "İlk listener çalıştı";
    return false; // Propagasyonu durdur
})->setPriority(10);

// Bu listener çalışmaz
$dispatcher->addListener('app.request', function ($event) {
    echo "Bu asla çalışmayacak";
})->setPriority(20);

$event = new GenericEvent('app.request');
$dispatcher->dispatch($event);
```

## 📝 Event Sourcing Taslağı

```php
// AggregateRoot
abstract class AggregateRoot
{
    protected array $uncommittedEvents = [];
    protected int $version = 0;
    
    public function recordEvent(DomainEvent $event): void
    {
        $this->uncommittedEvents[] = $event;
    }
    
    public function getUncommittedEvents(): array
    {
        return $this->uncommittedEvents;
    }
    
    public function clearUncommittedEvents(): void
    {
        $this->uncommittedEvents = [];
    }
    
    public function applyEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->applyEvent($event);
            $this->version++;
        }
    }
    
    abstract protected function applyEvent(DomainEvent $event): void;
}

// OrderAggregate
class OrderAggregate extends AggregateRoot
{
    private string $id;
    private array $items = [];
    private string $status;
    
    public static function create(string $id, array $items): self
    {
        $order = new self();
        $order->recordEvent(new OrderCreatedEvent($id, $items));
        return $order;
    }
    
    protected function applyEvent(DomainEvent $event): void
    {
        if ($event instanceof OrderCreatedEvent) {
            $this->id = $event->getAggregateId();
            $this->items = $event->get('items');
            $this->status = 'created';
        } elseif ($event instanceof OrderShippedEvent) {
            $this->status = 'shipped';
        }
    }
}
```

## 🏗️ Best Practices

1. **Immutable Events**

   Event nesneleri immutable olmalıdır. Bir kez oluşturulduktan sonra değiştirilmemelidir.

   ```php
   // İyi
   $event = new UserCreatedEvent(123, 'john@example.com', 'John Doe');
   
   // Kaçının
   $event->userId = 456; // Mutable property kullanma
   ```

2. **Ayrık Event Sınıfları**

   Her olay tipi için ayrı bir event sınıfı oluşturun:

   ```php
   // UserCreatedEvent, UserUpdatedEvent, UserDeletedEvent gibi ayrı sınıflar
   ```

3. **Domain Events**

   Domain event'leri entity/aggregate içinde oluşturun ve service layer'da dispatch edin:

   ```php
   // Entity'de event oluştur
   $user->recordEvent(new UserCreatedEvent($user->id, $user->email, $user->name));
   
   // Service'de dispatch et
   foreach ($user->releaseEvents() as $event) {
       $dispatcher->dispatch($event);
   }
   ```

4. **Single Responsibility Listeners**

   Bir listener, sadece bir işle ilgilenmelidir:

   ```php
   // İyi örnek
   class SendWelcomeEmailListener extends AbstractListener { /* ... */ }
   class NotifyAdminListener extends AbstractListener { /* ... */ }
   
   // Kaçının
   class DoEverythingListener extends AbstractListener { /* ... */ }
   ```

5. **Listener Önceliği**

   Kritik işlemlerin önceliğini yüksek tutun:

   ```php
   // Yüksek öncelik (düşük sayı)
   $auditListener->setPriority(10);  // Önce log
   
   // Düşük öncelik (yüksek sayı)
   $emailListener->setPriority(100); // Sonra email gönder
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-event`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-event`)
5. Pull Request oluşturun