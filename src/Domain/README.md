# Domain Katmanı

Domain Driven Design (DDD) prensiplerini uygulayan güçlü tip desteğine sahip domain model bileşenleri sunan katman.

## 🌟 Özellikler

- Entity, Value Object, Aggregate Root için temel sınıflar
- Domain Event altyapısı
- Repository ve Factory arayüzleri
- Domain Service desteği
- Immutable nesneler
- PHP 8.2+ özelliklerini kullanan güçlü tip kontrolü
- Event Sourcing desteği için altyapı

## 📂 Dizin Yapısı

```plaintext
Domain/
├── Contracts/
│   ├── AggregateRootInterface.php
│   ├── DomainEventInterface.php
│   ├── DomainRepositoryInterface.php
│   ├── DomainServiceInterface.php
│   ├── EntityInterface.php
│   ├── FactoryInterface.php
│   └── ValueObjectInterface.php
├── AbstractAggregateRoot.php
├── AbstractDomainEvent.php
├── AbstractDomainService.php
├── AbstractEntity.php
└── AbstractValueObject.php
```

## 🚀 Kullanım Örnekleri

### 1. Entity Oluşturma

```php
use Framework\Domain\AbstractEntity;

class User extends AbstractEntity
{
    private string $name;
    private string $email;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        string $email,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function changeName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
```

### 2. Value Object Oluşturma

```php
use Framework\Domain\AbstractValueObject;

class EmailAddress extends AbstractValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value
        ];
    }

    // Value object'ler immutable olduğundan değiştirme operasyonları
    // yeni instance döndürür
    public function withDomain(string $domain): self
    {
        [$name] = explode('@', $this->value);
        return new self($name . '@' . $domain);
    }
}
```

### 3. Aggregate Root Oluşturma

```php
use Framework\Domain\AbstractAggregateRoot;
use Framework\Domain\Contracts\DomainEventInterface;

class Order extends AbstractAggregateRoot
{
    private string $customerId;
    private array $items = [];
    private string $status;

    public function __construct(string $customerId)
    {
        $this->customerId = $customerId;
        $this->status = 'new';
    }

    public function addItem(string $productId, int $quantity, float $price): self
    {
        $this->items[] = [
            'productId' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ];

        // Domain event oluştur ve kaydet
        $this->recordEvent(new OrderItemAddedEvent(
            $this->getId(),
            $productId,
            $quantity,
            $price
        ));

        return $this;
    }

    public function submit(): self
    {
        if (empty($this->items)) {
            throw new \DomainException('Cannot submit an empty order');
        }

        $this->status = 'submitted';

        // Domain event oluştur ve kaydet
        $this->recordEvent(new OrderSubmittedEvent(
            $this->getId(),
            $this->customerId,
            $this->items
        ));

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    protected function applyEvent(DomainEventInterface $event): self
    {
        // Event tipine göre state'i güncelle
        if ($event instanceof OrderItemAddedEvent) {
            $data = $event->getData();
            $this->items[] = [
                'productId' => $data['productId'],
                'quantity' => $data['quantity'],
                'price' => $data['price']
            ];
        } elseif ($event instanceof OrderSubmittedEvent) {
            $this->status = 'submitted';
        }

        return $this;
    }
}
```

### 4. Domain Event Oluşturma

```php
use Framework\Domain\AbstractDomainEvent;

class OrderSubmittedEvent extends AbstractDomainEvent
{
    public function getType(): string
    {
        return 'order.submitted';
    }

    public function getAggregateType(): string
    {
        return 'order';
    }
}

class OrderItemAddedEvent extends AbstractDomainEvent
{
    public function __construct(
        $aggregateId,
        string $productId,
        int $quantity,
        float $price
    ) {
        parent::__construct($aggregateId, [
            'productId' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ]);
    }

    public function getType(): string
    {
        return 'order.item_added';
    }

    public function getAggregateType(): string
    {
        return 'order';
    }
}
```

### 5. Domain Repository Uygulaması

```php
use Framework\Domain\Contracts\DomainRepositoryInterface;
use Framework\Domain\Contracts\AggregateRootInterface;

class OrderRepository implements DomainRepositoryInterface
{
    private DatabaseInterface $database;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DatabaseInterface $database,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->database = $database;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function findById(mixed $id): ?AggregateRootInterface
    {
        $data = $this->database->findById('orders', $id);

        if (!$data) {
            return null;
        }

        // Order nesnesini oluştur
        $order = new Order($data['customerId']);
        $order->setId($id);
        $order->setVersion($data['version']);

        // Stored event'leri yükle ve uygula
        $events = $this->database->findEvents('orders', $id);
        $order->applyEvents($events);

        return $order;
    }

    public function save(AggregateRootInterface $aggregate): self
    {
        // Önce aggregate'i kaydet
        $this->database->save('orders', [
            'id' => $aggregate->getId(),
            'customerId' => $aggregate->getCustomerId(),
            'status' => $aggregate->getStatus(),
            'version' => $aggregate->getVersion() + 1
        ]);

        // Sonra event'leri kaydet ve yayınla
        $events = $aggregate->releaseEvents();
        
        foreach ($events as $event) {
            $this->database->saveEvent('orders', $aggregate->getId(), $event);
            $this->eventDispatcher->dispatch($event);
        }

        return $this;
    }

    public function delete(AggregateRootInterface $aggregate): self
    {
        $this->database->delete('orders', $aggregate->getId());
        return $this;
    }

    public function nextIdentity(): mixed
    {
        return uniqid('order_', true);
    }
}
```

### 6. Domain Service Kullanımı

```php
use Framework\Domain\AbstractDomainService;

class OrderPricingService extends AbstractDomainService
{
    private ProductRepositoryInterface $productRepository;
    private DiscountServiceInterface $discountService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DiscountServiceInterface $discountService
    ) {
        $this->productRepository = $productRepository;
        $this->discountService = $discountService;
        $this->setDomain('Order');
    }

    public function calculateOrderTotal(Order $order): Money
    {
        $total = new Money(0);
        
        foreach ($order->getItems() as $item) {
            $product = $this->productRepository->findById($item['productId']);
            $price = $product->getPrice()->multiply($item['quantity']);
            $total = $total->add($price);
        }

        // İndirim uygula
        if ($this->discountService->hasEligibleDiscount($order)) {
            $discount = $this->discountService->calculateDiscount($order, $total);
            $total = $total->subtract($discount);
        }

        return $total;
    }
}
```

### 7. Factory Kullanımı

```php
use Framework\Domain\Contracts\FactoryInterface;

class OrderFactory implements FactoryInterface
{
    private OrderRepositoryInterface $orderRepository;
    
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function create(array $data = []): Order
    {
        $order = new Order(
            $data['customerId'] ?? throw new \InvalidArgumentException('Customer ID is required')
        );
        
        // Yeni ID ata
        $order->setId($this->orderRepository->nextIdentity());
        
        // Varsa item'ları ekle
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $order->addItem($item['productId'], $item['quantity'], $item['price']);
            }
        }
        
        return $order;
    }
    
    public function reconstitute(array $data = []): Order
    {
        $order = new Order($data['customerId']);
        $order->setId($data['id']);
        $order->setVersion($data['version'] ?? 0);
        
        // Event'leri uygula
        if (isset($data['events']) && is_array($data['events'])) {
            $order->applyEvents($data['events']);
        }
        
        return $order;
    }
}
```

## 🔄 Domain Event Flow

Domain event'lerin akışı şu adımları içerir:

1. **Oluşturma:**
    - Domain nesnesi bir değişiklik yaptığında, ilgili event'i oluşturur
    - Event, `recordEvent()` metodu ile aggregate root'a kaydedilir

2. **Toplama:**
    - Repository, aggregate root'u kaydettiğinde, `releaseEvents()` ile event'leri toplar

3. **Yayınlama:**
    - Repository, toplanan event'leri event dispatcher'a yayınlar
    - Event dinleyicileri, event'i alıp işlerler

4. **Saklama:**
    - Event'ler, event store'da saklanabilir (Event Sourcing için)
    - Daha sonra aggregate root'u yeniden oluşturmak için kullanılabilir

## 📌 Event Sourcing

Event Sourcing, uygulama durumunu bir dizi event olarak saklama ve bu event'leri kullanarak durumu yeniden oluşturma tekniğidir:

```php
// Event'leri saklama
public function save(AggregateRootInterface $aggregate): self
{
    // Yeni event'leri al
    $events = $aggregate->releaseEvents();
    
    // Event'leri sakla
    foreach ($events as $event) {
        $this->eventStore->append(
            $event->getAggregateType(),
            $event->getAggregateId(),
            $event
        );
        
        // Event'i yayınla
        $this->eventDispatcher->dispatch($event);
    }
    
    return $this;
}

// Event'lerden aggregate'i yeniden oluşturma
public function findById(mixed $id): ?AggregateRootInterface
{
    // Event'leri yükle
    $events = $this->eventStore->getEvents(
        $this->getAggregateType(),
        $id
    );
    
    if (empty($events)) {
        return null;
    }
    
    // İlk event özel bir oluşturma event'i olmalı
    $firstEvent = $events[0];
    
    // Boş bir aggregate oluştur
    $aggregate = $this->createEmptyAggregate($id);
    
    // Tüm event'leri sırayla uygula
    $aggregate->applyEvents($events);
    
    return $aggregate;
}
```

## 🏗️ Domain Modeling Best Practices

1. **Ubiquitous Language Kullanımı**

   Domain modelinizde, gerçek dünya kavramlarını yansıtan bir terminoloji kullanın:

   ```php
   // İyi: Alan uzmanları ile aynı dili konuşur
   class Customer { /* ... */ }
   class Order { /* ... */ }
   class Product { /* ... */ }
   
   // Kaçının: Teknik terminoloji kullanımı
   class CustomerRecord { /* ... */ }
   class OrderData { /* ... */ }
   class ProductEntity { /* ... */ }
   ```

2. **Rich Domain Model**

   Anemic domain model yerine, davranışları içeren zengin domain model kullanın:

   ```php
   // İyi: Davranışları entity içinde tanımlama
   $order->addItem($product, 2);
   $order->submit();
   
   // Kaçının: Anemic model (davranışsız)
   $orderService->addItem($order, $product, 2);
   $orderService->submit($order);
   ```

3. **Value Objects için Immutability**

   Value object'ler değiştirilemez olmalıdır:

   ```php
   // İyi: Yeni bir instance döndürür
   $newEmail = $email->withDomain('example.com');
   
   // Kaçının: Mevcut instance'ı değiştirir
   $email->setDomain('example.com');
   ```

4. **Specification Pattern**

   Karmaşık business kurallarını specification pattern ile ifade edin:

   ```php
   class EligibleForPremiumDiscount implements SpecificationInterface
   {
       public function isSatisfiedBy(Customer $customer): bool
       {
           return $customer->getOrderCount() > 10 &&
                  $customer->getTotalSpent() > 1000 &&
                  $customer->getAccountAge()->days > 365;
       }
   }
   
   // Kullanım
   if ($premiumDiscountSpec->isSatisfiedBy($customer)) {
       // ...
   }
   ```

5. **Domain Service vs. Entity/Aggregate Methodları**

   Domain service'leri doğru konumda kullanın:

   ```php
   // Entity içinde tanımlanabilecek davranış
   $order->cancel();
   
   // Domain service gerektiren durum (birden fazla aggregate)
   $orderTransferService->transferOrderItems($sourceOrder, $targetOrder);
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-domain`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-domain`)
5. Pull Request oluşturun