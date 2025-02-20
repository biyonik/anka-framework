# Routing Katmanı

Modern ve esnek bir route yönetim sistemi sunan, PSR-7 uyumlu routing katmanı.

## 🌟 Özellikler

- PSR-7 uyumlu request/response desteği
- Attribute tabanlı routing
- Grup bazlı route yönetimi
- Middleware entegrasyonu
- RESTful resource desteği
- Pattern eşleştirme ve parametre yakalama
- Domain routing desteği
- Named routes ve URL oluşturma

## 📂 Dizin Yapısı

```plaintext
Routing/
├── Interfaces/
│   ├── RouterInterface.php
│   ├── RouteInterface.php
│   └── RouteCollectionInterface.php
├── Attributes/
│   ├── Route.php
│   └── Middleware.php
├── Router.php
├── Route.php
├── RouteCollection.php
├── RouteGroup.php
└── RouteCompiler.php
```

## 🚀 Kullanım Örnekleri

### 1. Temel Route Tanımlama

```php
// Router oluştur
$router = new Router();

// Basit route'lar tanımla
$router->get('/home', [HomeController::class, 'index']);
$router->post('/login', [AuthController::class, 'login']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/posts/{id}', [PostController::class, 'destroy']);

// Closure kullanımı
$router->get('/hello', function() {
    return 'Hello World!';
});

// Named routes
$router->get('/profile/{id}', [ProfileController::class, 'show'])
    ->name('profile.show');
```

### 2. Route Grupları

```php
// Prefix ve middleware ile grup oluşturma
$router->group('/admin', function (RouteGroup $group) {
    $group->get('/dashboard', [AdminController::class, 'dashboard']);
    $group->get('/users', [AdminController::class, 'users']);
    
    // İç içe gruplar
    $group->group('/settings', function (RouteGroup $settings) {
        $settings->get('/', [SettingsController::class, 'index']);
        $settings->post('/update', [SettingsController::class, 'update']);
    });
})->middleware(['auth', 'admin']);

// Domain bazlı gruplar
$router->domain('api.example.com')
    ->group('/v1', function (RouteGroup $api) {
        $api->get('/users', [ApiController::class, 'users']);
        $api->get('/posts', [ApiController::class, 'posts']);
    });
```

### 3. Route Parametreleri

```php
// Basit parametreler
$router->get('/users/{id}', [UserController::class, 'show']);

// Pattern ile kısıtlama
$router->get('/users/{id}', [UserController::class, 'show'])
    ->where('id', '[0-9]+');

// Birden fazla parametre
$router->get('/posts/{category}/{slug}', [PostController::class, 'show'])
    ->where('category', '[a-z]+')
    ->where('slug', '[a-z0-9\-]+');
```

### 4. RESTful Resource Routing

```php
// Tam resource
$router->resource('users', UserController::class);

// Bu route'ları oluşturur:
// GET    /users           - index
// GET    /users/create    - create
// POST   /users           - store
// GET    /users/{id}      - show
// GET    /users/{id}/edit - edit
// PUT    /users/{id}      - update
// DELETE /users/{id}      - destroy

// Sadece belirli methodları ekle
$router->resource('photos', PhotoController::class, ['index', 'show']);

// Belirli methodları hariç tut
$router->resource('posts', PostController::class, [], ['create', 'edit']);
```

### 5. Attribute Tabanlı Routing

```php
// Controller sınıfı
class UserController
{
    #[Route('/users', methods: ['GET'], name: 'users.index')]
    #[Middleware(['auth'])]
    public function index()
    {
        // Kullanıcıları listele
    }
    
    #[Route('/users/{id}', methods: ['GET'], name: 'users.show')]
    #[Middleware(['auth'])]
    public function show(int $id)
    {
        // Kullanıcı detaylarını göster
    }
    
    #[Route('/users', methods: ['POST'], name: 'users.store')]
    #[Middleware(['auth', 'csrf'])]
    public function store()
    {
        // Yeni kullanıcı oluştur
    }
}
```

### 6. Middleware Kullanımı

```php
// Tek route için middleware
$router->get('/profile', [ProfileController::class, 'index'])
    ->middleware('auth');

// Birden fazla middleware
$router->post('/admin/settings', [AdminController::class, 'updateSettings'])
    ->middleware(['auth', 'admin', 'csrf']);

// Grup için middleware
$router->middleware(['auth'])
    ->group('/dashboard', function (RouteGroup $group) {
        $group->get('/', [DashboardController::class, 'index']);
        $group->get('/stats', [DashboardController::class, 'stats']);
    });
```

### 7. URL Oluşturma

```php
// Named route için URL oluştur
$url = $router->route('profile.show', ['id' => 123]);
// Sonuç: /profile/123

// Parametrelerle URL oluştur
$url = $router->route('posts.show', [
    'category' => 'tech',
    'slug' => 'php-routing'
]);
// Sonuç: /posts/tech/php-routing

// Query string ekle
$url = $router->route('search', ['q' => 'routing', 'page' => 2]);
// Sonuç: /search?q=routing&page=2
```

## 🔧 Dispatching

```php
// PSR-7 request oluştur
$request = ServerRequest::fromGlobals();

// Route'u eşleştir ve yanıtı al
$response = $router->dispatch($request);

// Yanıtı gönder
$response->send();
```

## 🛠 Advanced Kullanım

### Namespace Tanımlama

```php
$router->namespace('App\\Controllers')
    ->group('/api', function (RouteGroup $group) {
        // UserController => App\Controllers\UserController
        $group->get('/users', [UserController::class, 'index']);
    });
```

### Domain Routing

```php
$router->domain('admin.example.com')
    ->group('/', function (RouteGroup $group) {
        $group->get('/', [AdminController::class, 'dashboard']);
    });
```

### Route Fallback

```php
// 404 sayfası için fallback route
$router->get('/{any}', [ErrorController::class, 'notFound'])
    ->where('any', '.*');
```

## 📝 Best Practices

1. **Named Routes Kullanımı**

   Her zaman route'lara isim verin, bu şekilde URL'leri programatik olarak oluşturabilirsiniz:

   ```php
   $router->get('/users/{id}', [UserController::class, 'show'])
       ->name('users.show');
       
   // Kullanım
   $url = $router->route('users.show', ['id' => 123]);
   ```

2. **Route Grupları**

   İlişkili route'ları gruplandırın, bu şekilde kod daha düzenli olur:

   ```php
   $router->group('/admin', function (RouteGroup $admin) {
       $admin->get('/dashboard', [AdminController::class, 'dashboard']);
       $admin->get('/users', [AdminController::class, 'users']);
   })->middleware('admin');
   ```

3. **Kaynak Adlandırma**

   RESTful controller'lar için tutarlı isimlendirme:

    - Resource adları çoğul olmalı: `users`, `posts`
    - Controller adları tekil olmalı: `UserController`, `PostController`

4. **Middleware Stratejisi**

   Middleware'leri doğru sırada uygulayın:

   ```php
   // Auth, önce çalışmalı
   $router->middleware(['auth', 'throttle', 'cache'])
       ->group('/api', function (RouteGroup $api) {
           // ...
       });
   ```

5. **Route Parametreleri**

   Parametreler için her zaman kısıtlama ekleyin:

   ```php
   $router->get('/users/{id}', [UserController::class, 'show'])
       ->where('id', '[0-9]+'); // Sadece sayısal değerler
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-route`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add route caching'`)
4. Branch'inizi push edin (`git push origin feature/amazing-route`)
5. Pull Request oluşturun