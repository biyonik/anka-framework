# CQRS Katmanı

Command Query Responsibility Segregation (CQRS) pattern'i uygulayan, okuma ve yazma işlemlerini ayrıştıran, esnek ve ölçeklenebilir bir mimari sağlayan katman.

## 🌟 Özellikler

- Okuma (Query) ve yazma (Command) işlemlerinin tam ayrımı
- Type-safe command ve query işleme
- Middleware desteği ile genişletilebilir yapı
- Domain event entegrasyonu
- PHP 8.2+ özelliklerini kullanan güçlü tip kontrolü
- Validasyon kuralları ile girdi doğrulama
- Idempotent command desteği
- Event sourcing ile uyumlu
- Domain Driven Design prensiplerini destekleyen tasarım

## 📂 Dizin Yapısı

```plaintext
CQRS/
├── Contracts/
│   ├── CommandInterface.php
│   ├── CommandHandlerInterface.php
│   ├── QueryInterface.php
│   ├── QueryHandlerInterface.php
│   ├── CommandBusInterface.php
│   └── QueryBusInterface.php
├── Exceptions/
│   ├── CommandHandlerNotFoundException.php
│   ├── CommandValidationException.php
│   ├── QueryHandlerNotFoundException.php
│   └── QueryValidationException.php
├── AbstractCommand.php
├── AbstractQuery.php
├── CommandBus.php
└── QueryBus.php
```

## 🚀 Kullanım Örnekleri

### 1. Command Oluşturma

```php
// CreateUserCommand.php
class CreateUserCommand extends AbstractCommand
{
    public function __construct(
        protected string $email,
        protected string $name,
        protected string $password,
        protected ?string $role = 'user'
    ) {
        $this->initialize();
    }
    
    // Getter metodları...
    
    public function validationRules(): array
    {
        return [
            'email' => 'required|email',
            'name' => 'required|min:3',
            'password' => 'required|min:8',
            'role' => 'in:user,admin,editor'
        ];
    }
}
```

### 2. Command Handler Oluşturma

```php
// CreateUserCommandHandler.php
class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected EventDispatcherInterface $eventDispatcher
    ) {}
    
    public function handle(CommandInterface $command): mixed
    {
        // Command tipini kontrol et
        if (!$command instanceof CreateUserCommand) {
            throw new \InvalidArgumentException('Invalid command type');
        }
        
        // Kullanıcı oluştur
        $user = new User(
            email: $command->getEmail(),
            name: $command->getName(),
            password: password_hash($command->getPassword(), PASSWORD_BCRYPT),
            role: $command->getRole() ?? 'user'
        );
        
        // Kullanıcıyı kaydet
        $savedUser = $this->userRepository->save($user);
        
        // Domain event yayınla
        $this->eventDispatcher->dispatch(
            new UserCreatedEvent($savedUser->getId(), $savedUser->getEmail(), $savedUser->getName())
        );
        
        return $savedUser;
    }
    
    public static function getCommandType(): string
    {
        return CreateUserCommand::class;
    }
    
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof CreateUserCommand;
    }
}
```

### 3. Query Oluşturma

```php
// GetUserQuery.php
class GetUserQuery extends AbstractQuery
{
    public function __construct(
        protected int|string|null $id = null,
        protected ?string $email = null
    ) {
        if ($id === null && $email === null) {
            throw new \InvalidArgumentException('Either id or email must be provided');
        }
    }
    
    // Getter metodları...
    
    public function validationRules(): array
    {
        return [
            'id' => 'numeric|nullable',
            'email' => 'email|nullable'
        ];
    }
}
```

### 4. Query Handler Oluşturma

```php
// GetUserQueryHandler.php
class GetUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}
    
    public function handle(QueryInterface $query): ?User
    {
        if (!$query instanceof GetUserQuery) {
            throw new \InvalidArgumentException('Invalid query type');
        }
        
        if ($query->getId() !== null) {
            return $this->userRepository->findById($query->getId());
        }
        
        if ($query->getEmail() !== null) {
            return $this->userRepository->findByEmail($query->getEmail());
        }
        
        return null;
    }
    
    public static function getQueryType(): string
    {
        return GetUserQuery::class;
    }
    
    public function canHandle(QueryInterface $query): bool
    {
        return $query instanceof GetUserQuery;
    }
}
```

### 5. Command Bus Kullanımı

```php
// Command Bus oluştur
$commandBus = new CommandBus($eventDispatcher);

// Command Handler'ı kaydet
$commandBus->registerHandler(
    CreateUserCommand::class,
    new CreateUserCommandHandler($userRepository, $eventDispatcher)
);

// Alternatif olarak sınıf adı ile kaydet
$commandBus->registerHandlerClass(CreateUserCommandHandler::class);

// Command oluştur
$createUserCommand = new CreateUserCommand(
    email: 'john@example.com',
    name: 'John Doe',
    password: 'secure-password',
    role: 'user'
);

// Command'i işle
try {
    $user = $commandBus->dispatch($createUserCommand);
    echo "Kullanıcı oluşturuldu: " . $user->getName();
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage();
}
```

### 6. Query Bus Kullanımı

```php
// Query Bus oluştur
$queryBus = new QueryBus($eventDispatcher);

// Query Handler'ı kaydet
$queryBus->registerHandler(
    GetUserQuery::class,
    new GetUserQueryHandler($userRepository)
);

// Query oluştur
$getUserQuery = new GetUserQuery(email: 'john@example.com');

// Query'i işle
try {
    $user = $queryBus->dispatch($getUserQuery);
    
    if ($user) {
        echo "Kullanıcı bulundu: " . $user->getName();
    } else {
        echo "Kullanıcı bulunamadı.";
    }
} catch (\Exception $e) {
    echo "Hata: " . $e->getMessage();
}
```

### 7. Middleware Kullanımı

```php
// Loglama middleware'i ekle
$commandBus->addMiddleware(function ($command, callable $next) use ($logger) {
    $logger->info("Command başladı: " . $command->getType());
    $startTime = microtime(true);
    
    try {
        $result = $next($command);
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        $logger->info("Command tamamlandı. Süre: {$executionTime}ms");
        
        return $result;
    } catch (\Exception $e) {
        $logger->error("Command hatası: " . $e->getMessage());
        throw $e;
    }
});

// Cache middleware'i ekle
$cache = [];
$queryBus->addMiddleware(function ($query, callable $next) use (&$cache) {
    $cacheKey = $query->getType() . '_' . md5(json_encode($query->getParameters()));
    
    // Cache'de varsa, direkt döndür
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
    // Yoksa işle ve cache'e ekle
    $result = $next($query);
    $cache[$cacheKey] = $result;
    
    return $result;
});
```

## 🔄 CQRS Mimarisi

CQRS, sistem içindeki okuma (query) ve yazma (command) işlemlerini ayrıştırarak, mimariyi daha esnek ve ölçeklenebilir hale getirir.

### Command Flow

1. **Command Oluşturma:**
    - İstemci bir Command nesnesi oluşturur
    - Command, sistemde bir değişiklik yapmak için gereken verileri içerir

2. **Command Validation:**
    - Command geçerlilik kuralları kontrol edilir
    - Geçersiz Command'ler Command Validation Exception ile reddedilir

3. **Command Bus:**
    - Command, Command Bus'a gönderilir
    - Command Bus, Command için uygun Handler'ı bulur

4. **Command Handler:**
    - Handler, Command verilerini işler
    - Domain nesneleri üzerinde gerekli değişiklikleri yapar
    - Repository aracılığıyla veritabanı güncellemeleri yapılır

5. **Event Dispatch:**
    - İşlem sonucunda Domain Event'ler oluşturulur ve yayınlanır
    - Event'ler sistemin diğer parçalarına değişiklikleri bildirir

### Query Flow

1. **Query Oluşturma:**
    - İstemci bir Query nesnesi oluşturur
    - Query, sistemden veri almak için gereken parametreleri içerir

2. **Query Validation:**
    - Query parametreleri doğrulanır
    - Geçersiz Query'ler Query Validation Exception ile reddedilir

3. **Query Bus:**
    - Query, Query Bus'a gönderilir
    - Query Bus, Query için uygun Handler'ı bulur

4. **Query Handler:**
    - Handler, Query parametrelerine göre veri sorgular
    - Repository aracılığıyla veritabanı sorgulaması yapılır
    - Sonuçlar döndürülür

## 📐 Domain Event Entegrasyonu

CQRS katmanı, Domain Event'ler ile entegre şekilde çalışır:

```php
class UserCommandHandler implements CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed
    {
        // Command işleme...
        
        // Domain Event oluştur ve yayınla
        $event = new UserCreatedEvent($userId, $email, $name);
        $this->eventDispatcher->dispatch($event);
        
        return $result;
    }
}
```

## 🧩 Container Entegrasyonu

Dependency Injection Container entegrasyonu:

```php
// ServiceProvider
class CQRSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(CommandBusInterface::class, function ($container) {
            $commandBus = new CommandBus(
                $container->get(EventDispatcherInterface::class)
            );
            
            // Handler'ları otomatik kaydet
            $this->registerCommandHandlers($commandBus);
            
            return $commandBus;
        });
        
        $this->container->singleton(QueryBusInterface::class, function ($container) {
            $queryBus = new QueryBus(
                $container->get(EventDispatcherInterface::class)
            );
            
            // Handler'ları otomatik kaydet
            $this->registerQueryHandlers($queryBus);
            
            return $queryBus;
        });
    }
    
    private function registerCommandHandlers(CommandBusInterface $commandBus): void
    {
        $handlers = [
            CreateUserCommandHandler::class,
            UpdateUserCommandHandler::class,
            DeleteUserCommandHandler::class,
            // Diğer handler'lar...
        ];
        
        foreach ($handlers as $handlerClass) {
            $commandBus->registerHandlerClass($handlerClass);
        }
    }
    
    private function registerQueryHandlers(QueryBusInterface $queryBus): void
    {
        $handlers = [
            GetUserQueryHandler::class,
            ListUsersQueryHandler::class,
            // Diğer handler'lar...
        ];
        
        foreach ($handlers as $handlerClass) {
            $queryBus->registerHandlerClass($handlerClass);
        }
    }
}
```

## 📝 Best Practices

1. **Command ve Query Ayrımı**

   Command ve Query'leri aynı endpoint'te karıştırmaktan kaçının:

   ```php
   // İyi
   $user = $queryBus->dispatch(new GetUserQuery($id));
   $commandBus->dispatch(new UpdateUserCommand($id, $data));
   
   // Kaçının
   $user = $repository->findAndUpdate($id, $data);
   ```

2. **İmmutable Command ve Query'ler**

   Command ve Query nesneleri immutable olmalıdır:

   ```php
   // İyi - Readonly property'ler
   class CreateUserCommand extends AbstractCommand
   {
       public function __construct(
           public readonly string $email,
           public readonly string $name,
           public readonly string $password
       ) {
           $this->initialize();
       }
   }
   ```

3. **Validation Kurallarını Tanımlama**

   Her Command ve Query için validation kuralları tanımlayın:

   ```php
   public function validationRules(): array
   {
       return [
           'email' => 'required|email',
           'password' => 'required|min:8'
       ];
   }
   ```

4. **Tek Sorumluluk İlkesi**

   Her Command ve Query Handler sadece bir işlem yapmalıdır:

   ```php
   // İyi: Tek sorumluluk
   class CreateUserCommandHandler { /* Sadece kullanıcı oluşturma */ }
   class SendWelcomeEmailCommandHandler { /* Sadece e-posta gönderme */ }
   
   // Kaçının: Çoklu sorumluluk
   class UserCommandHandler { /* Kullanıcı oluşturma, güncelleme, silme... */ }
   ```

5. **Domain Event'leri Kullanma**

   İlgili sistemlere bildirim için Domain Event'leri kullanın:

   ```php
   // Command Handler içinde
   $user = $this->userRepository->save($user);
   
   // Domain Event oluştur ve yayınla
   $this->eventDispatcher->dispatch(
       new UserCreatedEvent($user->getId(), $user->getEmail())
   );
   ```

6. **Query Performans Optimize Etme**

   Query'leri Cache middleware ile optimize edin:

   ```php
   $queryBus->addMiddleware(function ($query, callable $next) use ($cache) {
       $key = 'query_' . md5(serialize($query));
       
       if ($cache->has($key)) {
           return $cache->get($key);
       }
       
       $result = $next($query);
       $cache->set($key, $result, 3600);
       
       return $result;
   });
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-cqrs`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-cqrs`)
5. Pull Request oluştu