# Controller Katmanı

HTTP istekleri için iş mantığını yöneten, route ve view katmanları arasında köprü görevi gören controller sistemi.

## 🌟 Özellikler

- Attribute tabanlı yapılandırma
- Middleware entegrasyonu
- BeforeAction ve AfterAction hooks
- Otomatik response dönüştürme
- View rendering desteği
- Redirect, JSON response ve flash mesaj yönetimi
- Validation entegrasyonu

## 📂 Dizin Yapısı

```plaintext
Controller/
├── Interfaces/
│   └── ControllerInterface.php
├── Attributes/
│   ├── BeforeAction.php
│   └── Middleware.php
├── AbstractController.php
└── Controller.php
```

## 🚀 Kullanım Örnekleri

### 1. Temel Controller Oluşturma

```php
use Framework\Core\Controller\Controller;
use Framework\Core\Controller\Attributes\Middleware;

class UserController extends Controller
{
    // Tüm controller için middleware tanımlama
    #[Middleware(['auth'])]
    public function index()
    {
        $users = $this->service('user.repository')->getAll();
        
        return $this->view('users.index', [
            'users' => $users
        ]);
    }
    
    public function show($id)
    {
        $user = $this->service('user.repository')->find($id);
        
        if (!$user) {
            return $this->redirect('/users')->flash('error', 'Kullanıcı bulunamadı');
        }
        
        return $this->view('users.show', [
            'user' => $user
        ]);
    }
    
    #[Middleware(['csrf'])]
    public function store()
    {
        $validation = $this->validate($this->request()->post(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users'
        ]);
        
        if ($validation !== true) {
            return $this->redirectToRoute('users.create')
                ->flash('errors', $validation);
        }
        
        $user = $this->service('user.repository')->create($this->request()->post());
        
        return $this->redirectToRoute('users.show', ['id' => $user->id])
            ->flash('success', 'Kullanıcı oluşturuldu');
    }
}
```

### 2. BeforeAction Kullanımı

```php
use Framework\Core\Controller\Controller;
use Framework\Core\Controller\Attributes\BeforeAction;

class PostController extends Controller
{
    // Belirli metodlar için beforeAction
    #[BeforeAction('checkOwnership', only: ['edit', 'update', 'destroy'])]
    // Bazı metodlar hariç tüm metodlar için beforeAction
    #[BeforeAction('logActivity', except: ['index', 'show'])]
    
    public function edit($id)
    {
        $post = $this->service('post.repository')->find($id);
        return $this->view('posts.edit', ['post' => $post]);
    }
    
    // BeforeAction metodu - false dönerse işlem durur
    protected function checkOwnership($request)
    {
        $postId = $request->getAttribute('id');
        $post = $this->service('post.repository')->find($postId);
        
        if (!$post || $post->user_id !== $this->service('auth')->userId()) {
            $this->flash('error', 'Bu işlem için yetkiniz yok');
            return false;
        }
        
        // Request'e veri ekleyerek action'a geçirebiliriz
        return $request->withAttribute('post', $post);
    }
    
    protected function logActivity($request)
    {
        $this->service('logger')->info('Post işlemi yapıldı', [
            'user_id' => $this->service('auth')->userId(),
            'action' => $request->getUri()->getPath()
        ]);
        
        return $request;
    }
}
```

### 3. Middleware Kullanımı

```php
use Framework\Core\Controller\Controller;
use Framework\Core\Controller\Attributes\Middleware;

class AdminController extends Controller
{
    // Tüm controller için middleware
    #[Middleware(['auth', 'admin'])]
    public function dashboard()
    {
        return $this->view('admin.dashboard');
    }
    
    // Sadece bu action için middleware
    #[Middleware(['throttle:10,1'], only: ['reports'])]
    public function reports()
    {
        $reports = $this->service('report.generator')->getAll();
        return $this->json($reports);
    }
    
    // Bu methodlar için fazladan middleware
    #[Middleware(['audit'], only: ['updateSettings', 'deleteUser'])]
    public function updateSettings()
    {
        // Ayarları güncelle
    }
}
```

### 4. JSON API Controller

```php
use Framework\Core\Controller\Controller;
use Framework\Core\Controller\Attributes\Middleware;

#[Middleware(['api', 'auth:api'])]
class ApiController extends Controller
{
    public function index()
    {
        $data = $this->service('api.service')->getData();
        return $this->json($data);
    }
    
    public function store()
    {
        $validation = $this->validate($this->request()->json(), [
            'title' => 'required|min:3',
            'content' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->json(['errors' => $validation], 422);
        }
        
        $item = $this->service('api.service')->create($this->request()->json());
        
        return $this->json($item, 201);
    }
    
    public function notFound()
    {
        return $this->json(['error' => 'Resource not found'], 404);
    }
}
```

### 5. Route ile Controller Bağlama

```php
// routes/web.php

$router->get('/users', [UserController::class, 'index'])->name('users.index');
$router->get('/users/create', [UserController::class, 'create'])->name('users.create');
$router->post('/users', [UserController::class, 'store'])->name('users.store');
$router->get('/users/{id}', [UserController::class, 'show'])->name('users.show')
    ->where('id', '[0-9]+');
$router->get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
$router->put('/users/{id}', [UserController::class, 'update'])->name('users.update');
$router->delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

// Resource kullanarak kısa yoldan tanımlama
$router->resource('posts', PostController::class);
```

## 🔄 Controller Yaşam Döngüsü

1. **Route Eşleştirme:**
   - Router isteği eşleşen bir route'a yönlendirir
   - Controller ve action bilgisi alınır

2. **Controller Oluşturma:**
   - Controller instance'ı oluşturulur
   - Application inject edilir
   - Attribute'lar yüklenir

3. **Middleware Çalıştırma:**
   - Route middleware'leri çalıştırılır
   - Controller middleware'leri çalıştırılır

4. **BeforeAction Hooks:**
   - Global beforeAnyAction çalıştırılır (varsa)
   - Sınıf BeforeAction attribute'ları çalıştırılır
   - Action BeforeAction attribute'ları çalıştırılır

5. **Action Çalıştırma:**
   - Route parametreleri action'a geçirilir
   - Action çalıştırılır ve response alınır

6. **AfterAction Hooks:**
   - Action response'u AfterAction hook'una geçirilir
   - Response manipüle edilebilir

7. **Response Dönüştürme:**
   - Action'dan gelen değer ResponseInterface değilse dönüştürülür
   - String → HTML Response
   - Array/Object → JSON Response

8. **Response Gönderme:**
   - Response emit edilir

## 🧩 Response Tipleri

Controller'dan döndürülebilecek response tipleri:

### HTML View
```php
return $this->view('users.profile', ['user' => $user]);
```

### JSON
```php
return $this->json(['status' => 'success', 'data' => $data]);
```

### Redirect
```php
return $this->redirect('/login');
// veya
return $this->redirectToRoute('user.profile', ['id' => 1]);
```

### Raw Response
```php
return new Response(
    200,
    ['Content-Type' => 'application/pdf'],
    $pdfContent
);
```

## 📝 Best Practices

1. **Controller Sorumlulukları**

   Controller'ların sadece aşağıdakileri yapması gerekir:
   - Request'ten veri alma
   - Servisleri çağırma (iş mantığı için)
   - Response döndürme

   ```php
   // Doğru
   public function store()
   {
       $data = $this->request()->post();
       $result = $this->service('user.service')->create($data);
       return $this->redirectToRoute('users.show', ['id' => $result->id]);
   }
   
   // Yanlış
   public function store()
   {
       $data = $this->request()->post();
       // Controller içinde iş mantığı - YAPMAYIN
       $user = new User();
       $user->name = $data['name'];
       $user->save();
       return $this->redirectToRoute('users.show', ['id' => $user->id]);
   }
   ```

2. **Single Action Controllers**

   Tek bir işlemden sorumlu controller'lar için:

   ```php
   class ShowDashboardController extends Controller
   {
       public function __invoke()
       {
           return $this->view('dashboard');
       }
   }
   
   // Routes
   $router->get('/dashboard', ShowDashboardController::class);
   ```

3. **Request Validation**

   Validation kodunu controller'ın başında yapın:

   ```php
   public function store()
   {
       // Önce validate
       $validation = $this->validate($this->request()->post(), [
           'email' => 'required|email',
           'password' => 'required|min:8'
       ]);
       
       if ($validation !== true) {
           return $this->redirectToRoute('users.create')
               ->flash('errors', $validation);
       }
       
       // Sonra işlem
       $user = $this->service('user.service')->create($this->request()->post());
       
       return $this->redirectToRoute('users.show', ['id' => $user->id]);
   }
   ```

4. **Resource Controller'ları**

   RESTful controller'lar için standart metodlar:

   - `index()` - Liste görünümü
   - `create()` - Oluşturma formu
   - `store()` - Yeni kayıt oluşturma
   - `show($id)` - Tekil kayıt gösterme
   - `edit($id)` - Düzenleme formu
   - `update($id)` - Kayıt güncelleme
   - `destroy($id)` - Kayıt silme

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-controller`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-controller`)
5. Pull Request oluşturun