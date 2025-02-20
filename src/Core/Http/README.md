# HTTP Katmanı

HTTP Request ve Response işlemlerini yöneten PSR-7 ve PSR-17 uyumlu katman.

## 🌟 Özellikler

### Request İşlemleri
- PSR-7 uyumlu Request implementasyonu
- Superglobals ($_GET, $_POST, vb.) yönetimi
- JSON request desteği
- File upload yönetimi
- Header ve Cookie yönetimi
- Request validasyonu
- Attribute yönetimi

### Response İşlemleri
- PSR-7 uyumlu Response implementasyonu
- JSON response desteği
- File download desteği
- Redirect yönetimi
- Cache control
- CORS desteği
- Cookie yönetimi

### Factory Sistemi
- PSR-17 uyumlu RequestFactory
- PSR-17 uyumlu ResponseFactory
- Stream ve URI factory'leri
- Özelleştirilmiş factory metodları

## 📁 Dizin Yapısı

```plaintext
Http/
├── Message/
│   ├── Stream.php             # Stream implementasyonu
│   ├── Uri.php               # URI implementasyonu
│   └── UploadedFile.php      # Yüklenen dosya yönetimi
│
├── Request/
│   ├── Interfaces/
│   │   └── RequestInterface.php
│   ├── Traits/
│   │   ├── RequestTrait.php
│   │   └── UploadedFilesTrait.php 
│   ├── Factory/
│   │   └── RequestFactory.php
│   └── Request.php
│
└── Response/
    ├── Interfaces/
    │   └── ResponseInterface.php
    ├── Traits/
    │   └── ResponseTrait.php
    ├── Factory/
    │   └── ResponseFactory.php
    └── Response.php
```

## 🚀 Kullanım

### Request Oluşturma

```php
// Superglobals'dan request oluşturma
$request = Request::fromGlobals();

// Factory ile oluşturma
$factory = new RequestFactory();
$request = $factory->createServerRequest('GET', '/users');

// JSON request oluşturma
$request = $factory->createJsonRequest('POST', '/api/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Request Metodları

```php
// Request bilgileri
$method = $request->getMethod();
$path = $request->getPath();
$uri = $request->getUri();

// Request parametreleri
$id = $request->query('id');             // $_GET
$name = $request->post('name');          // $_POST
$token = $request->header('X-Token');    // Headers
$file = $request->file('avatar');        // Files

// Request özellikleri
$isAjax = $request->isXhr();
$isJson = $request->isJson();
$isSecure = $request->isSecure();
```

### Response Oluşturma

```php
// Factory ile oluşturma
$factory = new ResponseFactory();
$response = $factory->createResponse(200);

// JSON response
$response = Response::json([
    'status' => 'success',
    'data' => $user
]);

// File download
$response = Response::download('/path/to/file.pdf', 'document.pdf');

// Redirect
$response = Response::redirect('/dashboard');
```

### Response Metodları

```php
// Response manipülasyonu
$response = $response
    ->withStatus(201)
    ->withHeader('Content-Type', 'application/json')
    ->withJson($data);

// CORS ayarları
$response = $response->withCors([
    'allowedOrigins' => ['example.com'],
    'allowedMethods' => ['GET', 'POST'],
    'maxAge' => 3600
]);

// Cache control
$response = $response
    ->withCache(3600)               // 1 saat cache
    ->withHeader('ETag', $etag);
```

## 🔧 Upload Yönetimi

```php
// Dosya kontrolü
if ($file = $request->file('document')) {
    // Dosya bilgileri
    $name = $file->getClientFilename();
    $type = $file->getClientMediaType();
    $size = $file->getSize();
    
    // Dosya taşıma
    $file->moveTo('/path/to/uploads/' . $name);
}
```

## 🛠 Best Practices

1. **İmmutability**
   - Request ve Response nesneleri immutable'dır
   - Manipülasyonlar yeni instance döndürür
   - Orijinal nesne değişmez

```php
// Doğru kullanım
$newResponse = $response->withHeader('X-Custom', 'Value');

// Yanlış kullanım
$response->headers['X-Custom'] = 'Value';
```

2. **Factory Kullanımı**
   - Request/Response oluşturmak için factory kullanın
   - Default değerler factory'de tanımlanabilir
   - Tutarlı nesne oluşturma sağlar

```php
// Factory ile default headers
$factory = new ResponseFactory([
    'X-Powered-By' => 'Our Framework',
    'Server' => 'Our Server'
]);
```

3. **Stream Kullanımı**
   - Büyük içerikler için stream kullanın
   - Memory kullanımını optimize eder
   - Resource yönetimini kolaylaştırır

```php
$stream = new Stream('php://memory', 'wb+');
$stream->write('Large content');
$response = $response->withBody($stream);
```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.