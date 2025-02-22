# DataStructures Katmanı

Modern, immutable ve fonksiyonel programlama paradigmalarını destekleyen veri yapıları koleksiyonu.

## 🌟 Özellikler

- Immutable (değiştirilemez) koleksiyonlar
- Güçlü tipli (PHP 8.2+ generics)
- Fonksiyonel programlama yapıları
- Lazy (tembel) değerlendirme koleksiyonları
- Akıcı (fluent) API desteği
- Iterator ve Traversable implementasyonları
- Map, Set, Collection gibi temel veri yapıları

## 📂 Dizin Yapısı

```plaintext
DataStructures/
├── Contracts/
│   ├── CollectionInterface.php
│   ├── EitherInterface.php
│   ├── ImmutableCollectionInterface.php
│   ├── ImmutableMapInterface.php
│   ├── ImmutableSetInterface.php
│   ├── LazyCollectionInterface.php
│   ├── MapInterface.php
│   └── SetInterface.php
├── AbstractCollection.php
├── AbstractImmutableCollection.php
├── AbstractImmutableMap.php
├── AbstractImmutableSet.php
├── AbstractMap.php
├── AbstractSet.php
├── Collection.php
├── Either.php
├── ImmutableCollection.php
├── ImmutableMap.php
├── ImmutableSet.php
├── LazyCollection.php
├── Map.php
└── Set.php
```

## 🚀 Kullanım Örnekleri

### 1. Collection Kullanımı

```php
// Mutable Collection
$users = new Collection([
    ['id' => 1, 'name' => 'John'],
    ['id' => 2, 'name' => 'Jane'],
    ['id' => 3, 'name' => 'Bob']
]);

// Filtreleme ve dönüştürme
$filteredNames = $users
    ->filter(fn($user) => $user['id'] > 1)
    ->map(fn($user) => $user['name'])
    ->toArray(); // ['Jane', 'Bob']

// Immutable Collection
$immutable = new ImmutableCollection([1, 2, 3, 4, 5]);

// Yeni bir koleksiyon döndürür, orijinal değişmez
$newCollection = $immutable
    ->filter(fn($n) => $n % 2 === 0)
    ->map(fn($n) => $n * 2); // [2 => 4, 4 => 8]
```

### 2. Map Kullanımı

```php
// Mutable Map
$config = new Map([
    'app' => [
        'name' => 'MyApp',
        'version' => '1.0.0'
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306
    ]
]);

// Değerlere erişim
$appName = $config->get('app.name'); // 'MyApp'
$dbConfig = $config->get('database'); // ['host' => 'localhost', 'port' => 3306]

// Değer değiştirme
$config->put('app.version', '1.0.1');

// Immutable Map
$immutableConfig = new ImmutableMap($config->toArray());

// Yeni bir map döndürür, orijinal değişmez
$newConfig = $immutableConfig->with('app.version', '1.0.2');
```

### 3. Set Kullanımı

```php
// Mutable Set
$tags = new Set(['php', 'javascript', 'html', 'css']);

// Değer ekleme ve çıkarma
$tags->addValue('typescript');
$tags->removeValue('html');

// Set operasyonları
$frontendTags = new Set(['javascript', 'html', 'css', 'typescript']);
$backendTags = new Set(['php', 'python', 'java', 'nodejs']);

$allTags = $tags->union($backendTags); // Birleşim
$commonTags = $tags->intersect($frontendTags); // Kesişim
$uniqueTags = $tags->diff($frontendTags); // Fark

// Immutable Set
$immutableTags = new ImmutableSet(['php', 'javascript', 'html', 'css']);

// Yeni bir set döndürür, orijinal değişmez
$newTags = $immutableTags
    ->add('typescript')
    ->remove('html');
```

### 4. Lazy Collection Kullanımı

```php
// Generator ile sonsuz koleksiyon oluşturma
$fibonacci = new LazyCollection(function() {
    $a = 0;
    $b = 1;
    
    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
});

// İlk 10 Fibonacci sayısını al
$first10 = $fibonacci->take(10)->toArray();
// [0, 1, 1, 2, 3, 5, 8, 13, 21, 34]

// Dosya satırlarını tembel değerlendirme ile işleme
$lines = new LazyCollection(function() {
    $handle = fopen('large_file.txt', 'r');
    
    while (($line = fgets($handle)) !== false) {
        yield $line;
    }
    
    fclose($handle);
});

// Dosyayı satır satır işle
$lines
    ->filter(fn($line) => strpos($line, 'ERROR') !== false)
    ->each(function($line) {
        // Her satır için bir işlem yap
        logger()->error($line);
    });
```

### 5. Either Monads Kullanımı

```php
// Güvenli bölünme işlemi
$divide = function($a, $b) {
    if ($b === 0) {
        return Either::left(new \DivisionByZeroError('Division by zero'));
    }
    
    return Either::try(fn() => $a / $b);
};

// Hata kontrolü ile çalıştırma
$result = $divide(10, 2)
    ->mapRight(fn($value) => $value * 2)
    ->getOrElse(0); // 10

$result = $divide(10, 0)
    ->mapRight(fn($value) => $value * 2)
    ->getOrElse(0); // 0

// Try-catch bloğu yerine
$result = Either::try(function() {
    // Potansiyel olarak istisna fırlatan kod
    return json_decode(file_get_contents('config.json'), true, 512, JSON_THROW_ON_ERROR);
})
->mapRight(fn($config) => $config['database'])
->mapLeft(fn($error) => [
    'error' => $error->getMessage(),
    'fallback' => true
])
->fold(
    fn($error) => new DatabaseConfig(['fallback' => true]),
    fn($dbConfig) => new DatabaseConfig($dbConfig)
);
```

## 🌐 Framework İçinde Kullanım Örnekleri

### 1. Controller'da Collection Kullanımı

```php
class UserController extends Controller
{
    public function index()
    {
        // UserRepository'den kullanıcıları al
        $users = $this->service('user.repository')->getAll();
        
        // Collection'a dönüştür
        $collection = new Collection($users);
        
        // Aktif kullanıcıları filtrele ve dönüştür
        $activeUsers = $collection
            ->filter(fn($user) => $user->isActive())
            ->map(fn($user) => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()->getName()
            ]);
        
        // JSON yanıtı döndür
        return $this->json([
            'success' => true,
            'data' => $activeUsers->toArray(),
            'count' => $activeUsers->count()
        ]);
    }
    
    public function store()
    {
        // Validasyon
        $validation = $this->validate($this->request()->post(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'role_ids' => 'required|array'
        ]);
        
        if ($validation !== true) {
            return $this->redirectToRoute('users.create')
                ->flash('errors', $validation);
        }
        
        // Role ID'lerini ImmutableSet olarak al
        $roleIds = new ImmutableSet($this->request()->post('role_ids'));
        
        // Set operasyonlarını kullanarak yetki kontrolü
        $allowedRoles = new Set(['editor', 'author', 'subscriber']);
        $adminRoles = new Set(['admin', 'super-admin']);
        
        // Kullanıcı admin rol istemiş mi?
        if (!$roleIds->isDisjointWith($adminRoles)) {
            // Admin yetkisi gerekiyor
            if (!$this->service('auth')->hasPermission('admin.create')) {
                return $this->redirectToRoute('users.create')
                    ->flash('error', 'Admin rolü atamak için yetkiniz yok');
            }
        }
        
        // Rolleri filtrele (sadece izin verilenleri kullan)
        $validRoles = $roleIds->intersect($allowedRoles->union($adminRoles));
        
        // Kullanıcıyı oluştur
        $user = $this->service('user.repository')->create([
            'name' => $this->request()->post('name'),
            'email' => $this->request()->post('email'),
            'role_ids' => $validRoles->toArray()
        ]);
        
        return $this->redirectToRoute('users.show', ['id' => $user->id])
            ->flash('success', 'Kullanıcı oluşturuldu');
    }
}
```

### 2. Servis Katmanında LazyCollection Kullanımı

```php
class ReportService implements ReportServiceInterface
{
    private LogRepositoryInterface $logRepository;
    private UserRepositoryInterface $userRepository;
    
    public function __construct(
        LogRepositoryInterface $logRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->logRepository = $logRepository;
        $this->userRepository = $userRepository;
    }
    
    public function generateActivityReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        // Büyük log dosyasını LazyCollection ile işle
        $logs = new LazyCollection(function() use ($startDate, $endDate) {
            $cursor = $this->logRepository->getLogCursor($startDate, $endDate);
            
            while ($log = $cursor->next()) {
                yield $log;
            }
            
            $cursor->close();
        });
        
        // Kullanıcı ID'lerini topla
        $userIds = $logs
            ->filter(fn($log) => $log->getAction() === 'login')
            ->map(fn($log) => $log->getUserId())
            ->unique()
            ->toArray();
        
        // Kullanıcıları yükle
        $users = $this->userRepository->findByIds($userIds);
        $userMap = new Map();
        
        foreach ($users as $user) {
            $userMap->put($user->getId(), $user);
        }
        
        // Raporu hazırla - Tembel değerlendirme sayesinde bellek verimli çalışır
        $report = $logs
            ->filter(fn($log) => in_array($log->getAction(), ['login', 'logout', 'view', 'edit']))
            ->map(function($log) use ($userMap) {
                $userId = $log->getUserId();
                $user = $userMap->get($userId, null);
                
                return [
                    'timestamp' => $log->getTimestamp()->format('Y-m-d H:i:s'),
                    'action' => $log->getAction(),
                    'user_id' => $userId,
                    'user_name' => $user ? $user->getName() : 'Unknown',
                    'resource' => $log->getResource(),
                    'ip_address' => $log->getIpAddress()
                ];
            })
            ->chunk(1000) // 1000'lik gruplar halinde işle
            ->toArray();
        
        return $report;
    }
}
```

### 3. Repository'de Either Kullanımı

```php
class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function findById(int $id): Either
    {
        return Either::try(function() use ($id) {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new UserNotFoundException("User with ID {$id} not found");
            }
            
            return new User(
                $user['id'],
                $user['name'],
                $user['email'],
                $user['role']
            );
        });
    }
    
    public function create(array $data): Either
    {
        return Either::try(function() use ($data) {
            // Önce e-posta adresinin mevcut olup olmadığını kontrol et
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                throw new DuplicateEmailException("Email already exists: {$data['email']}");
            }
            
            // Yeni kullanıcı oluştur
            $stmt = $this->pdo->prepare('
                INSERT INTO users (name, email, role, created_at) 
                VALUES (:name, :email, :role, :created_at)
            ');
            
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':role', $data['role'], PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
            
            $id = $this->pdo->lastInsertId();
            
            return new User(
                (int) $id,
                $data['name'],
                $data['email'],
                $data['role']
            );
        });
    }
}

// Kullanımı - Controller'da
public function show($id)
{
    $result = $this->userRepository->findById((int) $id);
    
    // Either monad ile hata yönetimi
    return $result->fold(
        // Left (Hata) durumu
        function($error) {
            if ($error instanceof UserNotFoundException) {
                return $this->view('errors.404', [
                    'message' => $error->getMessage()
                ]);
            }
            
            logger()->error('User fetch error', ['error' => $error->getMessage()]);
            
            return $this->view('errors.500', [
                'message' => 'An error occurred while fetching the user'
            ]);
        },
        // Right (Başarı) durumu
        function(User $user) {
            return $this->view('users.show', [
                'user' => $user
            ]);
        }
    );
}
```

### 4. Command ve Query Yapılarında Kullanım

```php
class GetUserQuery extends AbstractQuery
{
    private $id;
    
    public function __construct(int $id)
    {
        $this->id = $id;
    }
    
    public function getId(): int
    {
        return $this->id;
    }
}

class GetUserQueryHandler implements QueryHandlerInterface
{
    private UserRepositoryInterface $userRepository;
    
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    public function handle(QueryInterface $query): Either
    {
        if (!$query instanceof GetUserQuery) {
            return Either::left(new \InvalidArgumentException('Invalid query type'));
        }
        
        return $this->userRepository->findById($query->getId());
    }
}

// Controller'da kullanım
public function show($id)
{
    $query = new GetUserQuery((int) $id);
    $result = $this->queryBus->dispatch($query);
    
    return $result->fold(
        // Hata durumu
        fn($error) => $this->handleError($error),
        // Başarı durumu
        fn(User $user) => $this->view('users.show', ['user' => $user])
    );
}

private function handleError(\Throwable $error)
{
    // Hata türüne göre farklı görünümler
    if ($error instanceof UserNotFoundException) {
        return $this->view('errors.404', ['message' => $error->getMessage()]);
    }
    
    logger()->error('Query error', ['error' => $error->getMessage()]);
    
    return $this->view('errors.500', [
        'message' => 'An error occurred'
    ]);
}
```

### 5. Validation ve Form İşlemlerinde Map Kullanımı

```php
class ContactFormHandler
{
    private MailServiceInterface $mailService;
    private ContactRepositoryInterface $contactRepository;
    
    public function __construct(
        MailServiceInterface $mailService,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->mailService = $mailService;
        $this->contactRepository = $contactRepository;
    }
    
    public function handle(array $data): Either
    {
        // Veriyi ImmutableMap'e dönüştür
        $formData = new ImmutableMap($data);
        
        // Validation kuralları
        $rules = new Map([
            'name' => ['required', 'min:3', 'max:50'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'min:5', 'max:100'],
            'message' => ['required', 'min:10']
        ]);
        
        // Validasyon
        $validator = new Validator();
        $validation = $validator->validate($formData->toArray(), $rules->toArray());
        
        if (!$validation->isValid()) {
            return Either::left($validation->getErrors());
        }
        
        // Form verilerini kaydet
        try {
            $contact = $this->contactRepository->create($formData->toArray());
            
            // E-posta gönder
            $this->mailService->sendContactNotification(
                $formData->get('email'),
                $formData->get('subject'),
                $formData->get('message')
            );
            
            return Either::right($contact);
        } catch (\Throwable $e) {
            logger()->error('Contact form error', [
                'error' => $e->getMessage(),
                'data' => $formData->toArray()
            ]);
            
            return Either::left($e);
        }
    }
}

// Controller'da kullanım
public function store()
{
    $result = $this->contactFormHandler->handle($this->request()->post());
    
    return $result->fold(
        function($errors) {
            return $this->redirectToRoute('contact.form')
                ->flash('errors', $errors)
                ->flash('old', $this->request()->post());
        },
        function($contact) {
            return $this->redirectToRoute('contact.success')
                ->flash('success', 'Thank you for your message!');
        }
    );
}
```

## 🧩 Tipler ve Genelleştirmeler

PHP 8.2+'nin generics özelliklerini kullanarak, veri yapılarında tip güvenliği sağlanır:

```php
/**
 * @template T
 */
interface CollectionInterface
{
    /**
     * @template R
     * @param callable(T): R $callback
     * @return static<R>
     */
    public function map(callable $callback): static;
}

// Kullanım (IDE otomatik tamamlama ve tip kontrolü sağlar)
/** @var Collection<User> $users */
$users = new Collection([
    new User('John'),
    new User('Jane')
]);

/** @var Collection<string> $names */
$names = $users->map(fn(User $user) => $user->getName());
```

## 🔄 Immutability (Değişmezlik)

Değiştirilemez (immutable) veri yapıları, veriyi değiştiren her operasyonun yeni bir kopya döndürmesini sağlar:

```php
$immutableList = new ImmutableCollection([1, 2, 3]);

// Yeni nesne döndürür, orijinali değişmez
$newList = $immutableList->append(4);

echo count($immutableList); // 3
echo count($newList); // 4

// Mutable koleksiyonlara dönüştürülebilir
$mutableList = $immutableList->toMutable();
$mutableList->add(0, 4); // Orijinali değiştirir
```

## 🔄 Lazy Evaluation (Tembel Değerlendirme)

Tembel değerlendirme, değerlerin yalnızca gerektiğinde hesaplanmasını sağlar:

```php
$numbers = LazyCollection::range(1, PHP_INT_MAX);

// Bu ifade sonsuz bir döngüye girmez, çünkü sadece ilk 10 öğe hesaplanır
$first10 = $numbers->take(10)->toArray();

// Büyük koleksiyonları işlerken bellek kullanımını azaltır
$evenSquares = $numbers
    ->filter(fn($n) => $n % 2 === 0) // Çift sayıları filtrele
    ->map(fn($n) => $n * $n)         // Karelerini al
    ->take(10)                      // İlk 10 öğeyi al
    ->toArray();                    // Diziye dönüştür
```

## 🛠️ Higher-Order Functions

Koleksiyonlar, fonksiyonel programlama paradigmasını destekleyen higher-order functions içerir:

```php
// Map - Her öğeyi dönüştür
$doubled = $collection->map(fn($n) => $n * 2);

// Filter - Koşulu sağlayan öğeleri filtrele
$evens = $collection->filter(fn($n) => $n % 2 === 0);

// Reduce - Koleksiyonu tek bir değere indirgeme
$sum = $collection->reduce(fn($carry, $n) => $carry + $n, 0);

// Any - Herhangi bir öğe koşulu sağlıyor mu?
$hasEven = $collection->any(fn($n) => $n % 2 === 0);

// All - Tüm öğeler koşulu sağlıyor mu?
$allPositive = $collection->all(fn($n) => $n > 0);

// Each - Yan etki için her öğe üzerinde işlem yap
$collection->each(fn($n) => logger()->info("Processing: {$n}"));
```

## 🔧 Extension ve Customization

Kendi özel veri yapılarınızı oluşturmak için soyut sınıfları ve arayüzleri genişletebilirsiniz:

```php
/**
 * @template T
 * @extends AbstractCollection<T>
 */
class CustomCollection extends AbstractCollection
{
    /**
     * Koleksiyondaki tüm sayısal değerlerin toplamını döndürür.
     * 
     * @return int|float Toplam
     */
    public function sum(): int|float
    {
        return array_sum($this->items);
    }

    /**
     * Koleksiyondaki tüm sayısal değerlerin ortalamasını döndürür.
     * 
     * @return float Ortalama
     */
    public function average(): float
    {
        if ($this->isEmpty()) {
            return 0;
        }
        
        return $this->sum() / $this->count();
    }
}
```

## 🧪 Performans İpuçları

1. **Zincir Operasyonları**  
   LazyCollection kullanarak operasyonları zincirleyin, böylece ara sonuçlar için bellek tahsis edilmez.

2. **Büyük Veri Setleri**  
   Büyük dosyalar veya veritabanı sonuçları için LazyCollection kullanın.

3. **Immutable vs Mutable**  
   Çok sık değişiklik yapılacaksa, performans kritikse Mutable koleksiyonları tercih edin.
   Değer paylaşımı ve thread-safety önemliyse Immutable koleksiyonları kullanın.

4. **Tip Bildirimleri**  
   IDE desteği ve statik analiz için PHPDoc tip notasyonlarını kullanın.

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-structure`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-structure`)
5. Pull Request oluşturun