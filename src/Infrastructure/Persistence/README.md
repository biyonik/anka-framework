# Persistence Katmanı

Veritabanı ve veri saklama işlemlerini yöneten yüksek performanslı, tip güvenli ve nesne yönelimli katman.

## 🌟 Özellikler

- PDO tabanlı veritabanı bağlantı yönetimi
- Nesne yönelimli, akıcı QueryBuilder API
- Repository Pattern implementasyonu
- Unit of Work Pattern ve Entity Manager
- Transaction yönetimi
- Çoklu veritabanı desteği (MySQL, PostgreSQL, SQLite, SQL Server)
- Domain Driven Design prensiplerine uygun soyutlama
- Yüksek performanslı sorgu optimizasyonu
- Immutable nesneler ve değer nesneleri desteği
- Type-safe (PHP 8.2+) veritabanı etkileşimi

## 📂 Dizin Yapısı

```plaintext
Persistence/
├── Contracts/
│   ├── ConnectionManagerInterface.php
│   ├── QueryBuilderInterface.php
│   ├── RepositoryInterface.php
│   └── EntityManagerInterface.php
├── Exceptions/
│   └── DatabaseException.php
├── ConnectionManager.php
├── QueryBuilder.php
├── QueryBuilderFactory.php
├── AbstractRepository.php
└── EntityManager.php
```

## 🚀 Kullanım Örnekleri

### 1. Veritabanı Bağlantısı

```php
// Konfigürasyon
$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'my_database',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];

// Bağlantı oluştur
$connectionManager = new ConnectionManager($config);

// PDO nesnesini al
$pdo = $connectionManager->getConnection();
```

### 2. QueryBuilder Kullanımı

```php
// QueryBuilder oluştur
$queryBuilder = new QueryBuilder($connectionManager);

// SELECT sorgusu
$users = $queryBuilder
    ->select(['id', 'name', 'email'])
    ->from('users')
    ->where('status = ?', [1])
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->execute();

// INSERT sorgusu
$id = $queryBuilder
    ->from('users')
    ->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 1
    ]);

// UPDATE sorgusu
$affected = $queryBuilder
    ->from('users')
    ->where('id = ?', [1])
    ->update([
        'name' => 'John Updated',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

// DELETE sorgusu
$deleted = $queryBuilder
    ->from('users')
    ->where('id = ?', [1])
    ->delete();
```

### 3. Repository Kullanımı

```php
// User repository oluştur
class UserRepository extends AbstractRepository
{
    protected string $entityClass = User::class;
    protected string $tableName = 'users';

    // Özel metotlar
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findActiveUsers(): array
    {
        return $this->queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where('status = ?', [1])
            ->orderBy('created_at', 'DESC')
            ->execute();
    }
}

// Repository kullanımı
$userRepository = new UserRepository($queryBuilder);

// Kullanıcı bul
$user = $userRepository->findById(1);

// Özel metotları kullan
$activeUsers = $userRepository->findActiveUsers();
```

### 4. Entity Manager Kullanımı

```php
// Entity Manager oluştur
$entityManager = new EntityManager($connectionManager, $queryBuilderFactory);

// Repository al
$userRepository = $entityManager->getRepository(UserRepository::class);

// User entity'si oluştur
$user = new User();
$user->setName('John Doe');
$user->setEmail('john@example.com');

// Entity'yi kaydetmek için işaretle
$entityManager->persist($user);

// Değişiklikleri veritabanına kaydet
$entityManager->flush();

// Transaction kullanımı
$entityManager->transactional(function(EntityManager $em) use ($user) {
    // User güncelle
    $user->setStatus(1);
    $em->persist($user);
    
    // Başka işlemler
    $profile = new Profile();
    $profile->setUserId($user->getId());
    $em->persist($profile);
    
    // Tüm değişiklikleri kaydet
    // Transaction başarısız olursa otomatik rollback yapılır
});
```

## 🔄 Transaction Yönetimi

```php
// Manuel transaction
$connectionManager->beginTransaction();

try {
    // Veritabanı işlemleri...
    $connectionManager->commit();
} catch (DatabaseException $e) {
    $connectionManager->rollback();
    throw $e;
}

// Callback ile transaction (daha temiz yaklaşım)
$entityManager->transactional(function(EntityManager $em) {
    // İşlemler
    // Herhangi bir hata durumunda otomatik rollback
});
```

## 🔧 Repository İmplementasyonu

```php
// AbstractRepository'den türet
class ProductRepository extends AbstractRepository
{
    protected string $entityClass = Product::class;
    protected string $tableName = 'products';
    protected string $primaryKey = 'id';

    // Özel sorgu metotları
    public function findFeaturedProducts(int $limit = 10): array
    {
        return $this->queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where('is_featured = ?', [1])
            ->where('stock > ?', [0])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->execute();
    }
    
    public function searchByName(string $keyword): array
    {
        return $this->queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where('name LIKE ?', ["%{$keyword}%"])
            ->orderBy('name', 'ASC')
            ->execute();
    }
}

// Kullanım
$productRepo = new ProductRepository($queryBuilder);
$featuredProducts = $productRepo->findFeaturedProducts(5);
```

## 📊 Gelişmiş Sorgular

```php
// JOIN işlemleri
$orders = $queryBuilder
    ->select(['o.*', 'u.name as user_name'])
    ->from('orders', 'o')
    ->join('users', 'o.user_id = u.id', 'u')
    ->where('o.status = ?', ['completed'])
    ->orderBy('o.created_at', 'DESC')
    ->execute();

// GROUP BY ve aggregate fonksiyonlar
$sales = $queryBuilder
    ->select([
        'product_id', 
        'COUNT(*) as total_sales',
        'SUM(price) as revenue'
    ])
    ->from('orders')
    ->groupBy('product_id')
    ->having('total_sales > ?', [10])
    ->orderBy('revenue', 'DESC')
    ->execute();

// Subquery
$topProducts = $queryBuilder
    ->select(['p.*'])
    ->from('products', 'p')
    ->where('p.id IN (SELECT product_id FROM featured_products)')
    ->execute();
```

## 🛡️ Güvenlik

Persistence katmanımız, SQL injection ve diğer güvenlik sorunlarına karşı koruma sağlar:

- PDO prepared statements kullanımı
- Parametre bağlama (binding)
- Entity ve value nesneleri ile tip güvenliği
- Exception yönetimi ve güvenli error handling
- Transaction güvenliği

## 📝 Best Practices

1. **Repository Pattern Kullanımı**

   Domain entity'leri için her zaman repository kullanın:

   ```php
   class User {
       // Entity özellikleri ve davranışları
   }
   
   class UserRepository extends AbstractRepository {
       protected string $entityClass = User::class;
       protected string $tableName = 'users';
   }
   ```

2. **Transaction Kullanımı**

   Birbiriyle ilişkili veritabanı işlemleri için her zaman transaction kullanın:

   ```php
   $entityManager->transactional(function(EntityManager $em) {
       // İlişkili işlemler
   });
   ```

3. **Query Builder Zincirleme**

   Okunabilirliği artırmak için metot zincirleme kullanın:

   ```php
   // İyi Pratik
   $users = $queryBuilder
       ->select(['id', 'name'])
       ->from('users')
       ->where('status = ?', [1])
       ->orderBy('name')
       ->limit(10)
       ->execute();
   
   // Kaçınılması Gereken
   $queryBuilder->select(['id', 'name']);
   $queryBuilder->from('users');
   $queryBuilder->where('status = ?', [1]);
   $users = $queryBuilder->execute();
   ```

4. **Entity Tasarımı**

   Entity'ler domain mantığını içermeli, veritabanı işlemlerini değil:

   ```php
   // İyi Pratik
   class Product {
       public function increaseStock(int $amount): void {
           $this->stock += $amount;
       }
   }
   
   // Kullanım
   $product->increaseStock(5);
   $entityManager->persist($product);
   $entityManager->flush();
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-persistence`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-persistence`)
5. Pull Request oluşturun