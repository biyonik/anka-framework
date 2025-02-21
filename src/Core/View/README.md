# View Sistemi

Esnek, basit ve güçlü template sistemi ve view yönetimi sağlayan katman.

## 🌟 Özellikler

- PHP native template desteği
- Layout ve section yönetimi
- Component bazlı render
- Namespace desteği
- Global veri paylaşımı
- View önbellekleme
- View finder ile dosya konumu yönetimi
- Genişletilebilir engine yapısı

## 📂 Dizin Yapısı

```plaintext
View/
├── Interfaces/
│   ├── ViewInterface.php
│   ├── ViewEngineInterface.php
│   └── ViewFinderInterface.php
├── Engines/
│   ├── PhpViewEngine.php
│   └── AbstractViewEngine.php
├── View.php
├── ViewFinder.php
└── ViewFactory.php
```

## 🚀 Kullanım Örnekleri

### 1. Temel View Render Etme

```php
// ViewFactory oluştur
$finder = new ViewFinder([__DIR__ . '/views']);
$engine = new PhpViewEngine($finder);
$factory = new ViewFactory($finder, $engine);

// View oluştur ve render et
$view = $factory->make('home', [
    'title' => 'Ana Sayfa',
    'user' => $user
]);

// HTML çıktısı al
$html = $view->render();
```

### 2. View Kontrolcüden Render Etme

```php
class HomeController extends Controller
{
    public function index()
    {
        return $this->view('home.index', [
            'title' => 'Ana Sayfa',
            'posts' => $this->service('post.repository')->getLatest()
        ]);
    }
}
```

### 3. Layout Kullanımı

```php
// layout içinde (layouts/main.php)
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'Site' ?></title>
</head>
<body>
    <header>
        <!-- Header içeriği -->
    </header>
    
    <main>
        <?= $content ?>
    </main>
    
    <footer>
        <!-- Footer içeriği -->
    </footer>
</body>
</html>

// View oluştur ve layout ekle
$view = $factory->make('home')
    ->withLayout('main')
    ->with('title', 'Ana Sayfa');
```

### 4. Component Kullanımı

```php
// components/alert.php
<div class="alert alert-<?= $type ?>">
    <?= $message ?>
    <?php if ($dismissible): ?>
        <button class="close">&times;</button>
    <?php endif; ?>
</div>

// Component'i render et
$alert = $factory->component('alert', [
    'type' => 'success',
    'message' => 'İşlem başarılı!',
    'dismissible' => true
]);
```

### 5. Namespace Kullanımı

```php
// Admin modülü için namespace tanımla
$factory->addNamespace('admin', __DIR__ . '/views/admin');

// Admin modülündeki view'ı render et
$view = $factory->make('admin::dashboard', [
    'stats' => $dashboardStats
]);
```

### 6. Global Veri Paylaşımı

```php
// App servis sağlayıcıda global veriler tanımla
$factory->share('app_name', 'My Application');
$factory->share('version', '1.0.0');
$factory->share('user', $authenticatedUser);

// Tüm view'larda kullanılabilir
// view.php
<p>Welcome to <?= $app_name ?> v<?= $version ?></p>
<p>Hello, <?= $user->name ?></p>
```

## 📄 PHP Template Syntax

PHP view engine, PHP'nin native template yeteneklerini kullanır.

### Değişken Ekrana Yazdırma
```php
<?= $variable ?>
// veya
<?php echo $variable; ?>
```

### Koşullu İfadeler
```php
<?php if ($condition): ?>
    <!-- HTML -->
<?php elseif ($otherCondition): ?>
    <!-- HTML -->
<?php else: ?>
    <!-- HTML -->
<?php endif; ?>
```

### Döngüler
```php
<?php foreach ($items as $item): ?>
    <li><?= $item->name ?></li>
<?php endforeach; ?>

<?php for ($i = 0; $i < 10; $i++): ?>
    <span><?= $i ?></span>
<?php endfor; ?>
```

### HTML Escape
```php
<?= htmlspecialchars($variable, ENT_QUOTES, 'UTF-8') ?>
// veya helper kullanarak
<?= e($variable) ?>
```

### View İçinde Başka Bir View İnclude Etme
```php
<?php include $this->engine->getFinder()->find('partials.header'); ?>
// veya helper kullanarak
<?= $this->renderPartial('partials.header', ['title' => 'Başlık']) ?>
```

## 🏗️ View Engine Sistemi

View sistemi, farklı template engine'lerin entegrasyonuna olanak sağlar.

### 1. AbstractViewEngine'i Extend Etme

```php
class TwigViewEngine extends AbstractViewEngine
{
    protected \Twig\Environment $twig;
    
    public function __construct(ViewFinderInterface $finder, array $options = [])
    {
        parent::__construct($finder);
        
        $loader = new \Twig\Loader\FilesystemLoader($finder->getPaths());
        $this->twig = new \Twig\Environment($loader, $options);
        
        // Shared verileri twig'e ekle
        foreach ($this->shared as $key => $value) {
            $this->twig->addGlobal($key, $value);
        }
    }
    
    public function render(string $path, array $data = []): string
    {
        $name = $this->getTemplateName($path);
        return $this->twig->render($name, $this->mergeSharedData($data));
    }
    
    // ...
}
```

### 2. Factory ile Engine Kullanımı

```php
// Twig engine oluştur
$twigEngine = new TwigViewEngine($finder, [
    'cache' => __DIR__ . '/cache',
    'debug' => true
]);

// Twig kullanarak view oluştur
$view = $factory->makeWith($twigEngine, 'home.index', [
    'title' => 'Ana Sayfa'
]);
```

## 📝 Best Practices

1. **View Organizasyonu**

   Modüllere göre view'ları gruplandırın:
   ```
   views/
   ├── layouts/
   │   ├── main.php
   │   └── admin.php
   ├── components/
   │   ├── alert.php
   │   └── card.php
   ├── partials/
   │   ├── header.php
   │   └── footer.php
   ├── users/
   │   ├── index.php
   │   ├── show.php
   │   └── edit.php
   └── posts/
       ├── index.php
       └── show.php
   ```

2. **Controller İçinde View Kullanımı**

   Controller'larda view'ları aşağıdaki şekilde kullanın:
   ```php
   // Tercih edilen
   return $this->view('users.show', ['user' => $user]);
   
   // Kaçınılması gereken
   $factory = $this->container->get(ViewFactory::class);
   $view = $factory->make('users.show', ['user' => $user]);
   return new Response($view->render());
   ```

3. **Layout ve Component Kullanımı**

   Tekrarlanan kodları layout ve component'lere ayırın:
   ```php
   // Layout içinde
   <?= $content ?>
   
   // Component kullanımı
   <?= $this->component('button', ['text' => 'Kaydet', 'type' => 'submit']) ?>
   ```

4. **HTML Escape**

   Kullanıcı girdilerini her zaman escape edin:
   ```php
   <?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?>
   ```

5. **Önbellekleme**

   Yoğun view'lar için önbellekleme kullanın:
   ```php
   $engine = new PhpViewEngine($finder, true); // Önbellekleme aktif
   ```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-view`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-view`)
5. Pull Request oluşturun