# Configuration Katmanı

Esnek, modüler ve güçlü tipli konfigürasyon yönetim sistemi. Farklı kaynaklardan konfigürasyon yükleme, çevre bazlı değer yönetimi, önbellekleme ve şifreleme özellikleri sunar.

## 🌟 Özellikler

- Hiyerarşik konfigürasyon yapısı ve dot notation erişimi
- Çeşitli formatlardan konfigürasyon yükleme (PHP, JSON, YAML)
- Çevre (environment) bazlı konfigürasyon yönetimi
- Konfigürasyon verilerinin önbelleklenmesi
- Hassas verilerin şifrelenmesi
- PSR uyumlu ve modern PHP 8.2+ özelliklerini kullanma
- Esnek ve genişletilebilir tasarım

## 📂 Dizin Yapısı

```plaintext
Configuration/
├── Cache/
│   └── FileConfigCache.php         # Dosya tabanlı önbellekleme
├── Contracts/
│   ├── ConfigCacheInterface.php    # Önbellekleme arayüzü
│   ├── ConfigLoaderInterface.php   # Yükleyici arayüzü
│   ├── ConfigRepositoryInterface.php # Gelişmiş depo arayüzü
│   ├── ConfigurationInterface.php  # Temel konfigürasyon arayüzü
│   ├── EncryptedConfigInterface.php # Şifreleme arayüzü
│   └── EnvironmentInterface.php    # Çevre yönetim arayüzü
├── Loaders/
│   ├── JsonConfigLoader.php        # JSON yükleyici
│   ├── PhpConfigLoader.php         # PHP yükleyici
│   └── YamlConfigLoader.php        # YAML yükleyici
├── Providers/
│   └── ConfigServiceProvider.php   # Servis sağlayıcı
├── Security/
│   └── EncryptedConfig.php         # Şifreleme işlemleri
├── Configuration.php               # Temel konfigürasyon sınıfı
├── ConfigRepository.php            # Gelişmiş konfigürasyon deposu
└── Environment.php                 # Çevre yöneticisi
```

## 🚀 Kullanım Örnekleri

### 1. Temel Konfigürasyon İşlemleri

```php
// Konfigürasyon nesnesini oluştur
$config = new Configuration();

// Değer ekle
$config->set('app.name', 'Benim Uygulamam');
$config->set('app.version', '1.0.0');
$config->set('database.connections.mysql.host', 'localhost');

// Değer oku
$appName = $config->get('app.name'); // 'Benim Uygulamam'
$dbHost = $config->get('database.connections.mysql.host'); // 'localhost'
$debug = $config->get('app.debug', false); // Varsayılan değer: false

// Değer kontrolü
$hasVersion = $config->has('app.version'); // true
$hasPort = $config->has('database.connections.mysql.port'); // false

// Tüm konfigürasyonu al
$allConfig = $config->all();
```

### 2. Dosyadan Konfigürasyon Yükleme

```php
// PHP dosyası
$loader = new PhpConfigLoader();
$config = new ConfigRepository($loader);

// Tek dosya yükleme
$config->loadFromFile(__DIR__ . '/config/app.php');

// Örnek config/app.php içeriği:
// return [
//    'name' => 'Benim Uygulamam',
//    'version' => '1.0.0',
//    'debug' => false
// ];

// Dizinden yükleme
$config->loadFromDirectory(__DIR__ . '/config');

// JSON dosyaları için
$jsonLoader = new JsonConfigLoader();
$config->setLoader($jsonLoader);
$config->loadFromFile(__DIR__ . '/config/app.json');
```

### 3. Çevre (Environment) Yönetimi

```php
// Çevre yöneticisi oluştur
$env = new Environment('production');

// .env dosyasını yükle
$env->load(__DIR__ . '/.env');

// Çevre değişkenlerini oku
$appKey = $env->get('APP_KEY');
$debug = $env->get('APP_DEBUG', false);

// Çevre kontrolü
if ($env->is('development')) {
    // Development modunda yapılacak işlemler
}

if ($env->is(['development', 'testing'])) {
    // Development veya testing modunda yapılacak işlemler
}

// ConfigRepository ile entegrasyon
$config = new ConfigRepository($loader, $env);

// Çevre bazlı konfigürasyon dizinini yükle
$envConfigPath = __DIR__ . '/config/' . $env->getEnvironment();
if (is_dir($envConfigPath)) {
    $config->loadFromDirectory($envConfigPath);
}
```

### 4. Konfigürasyon Önbellekleme

```php
// Önbellek oluştur
$cache = new FileConfigCache(__DIR__ . '/storage/cache/config.cache');

// ConfigRepository ile kullan
$config = new ConfigRepository($loader, $env, $cache);

// Önbelleği temizle
$cache->clear();

// Önbelleği yenile
$config->refreshCache();
```

### 5. Hassas Verilerin Şifrelenmesi

```php
// Şifreleme yöneticisi oluştur
$encrypter = new EncryptedConfig('gizli-anahtar');

// ConfigRepository ile kullan
$config = new ConfigRepository($loader, $env, $cache, $encrypter);

// Değer şifrele
$config->setEncrypted('database.password', 'çok-gizli-şifre');

// Şifreli değeri oku (otomatik çözülür)
$password = $config->get('database.password'); // 'çok-gizli-şifre'

// Şifreli değer kontrolü
$isEncrypted = $encrypter->isEncrypted($config->getEncrypted('database.password')); // true
```

### 6. Servis Sağlayıcı ile Kullanım

```php
// Container'a servis sağlayıcıyı kaydet
$container->addProvider(ConfigServiceProvider::class);

// Konfigürasyon nesnesini container'dan al
$config = $container->get(ConfigRepositoryInterface::class);
// veya facade alias'ı kullan
$config = $container->get('config');

// Container tarafından oluşturulan konfigürasyon nesnesi:
// - Tüm konfigürasyon dizinlerini otomatik yükler
// - Çevre bazlı yükleme yapar
// - Önbellekleme kullanır (production modunda)
// - Şifrelemeyi destekler

// Kullanım
$appName = $config->get('app.name');
$dbConfig = $config->get('database.connections.mysql');
```

## 🔧 Best Practices

1. **Hiyerarşik Konfigürasyon Yapısı**

   Konfigürasyon anahtarlarını hiyerarşik olarak gruplandırın:

   ```php
   // İyi
   $config->set('database.connections.mysql.host', 'localhost');
   $config->set('database.connections.mysql.port', 3306);
   
   // Kötü
   $config->set('database_mysql_host', 'localhost');
   $config->set('database_mysql_port', 3306);
   ```

2. **Konfigürasyon Dosyalarını Modüler Tutma**

   Her bir konfigürasyon dosyası, bir modülü veya ilgili ayarları içermeli:

   ```
   config/
   ├── app.php        # Temel uygulama ayarları
   ├── auth.php       # Kimlik doğrulama ayarları
   ├── database.php   # Veritabanı bağlantıları
   ├── cache.php      # Önbellek ayarları
   └── services.php   # Servis entegrasyonları
   ```

3. **Çevre Bazlı Konfigürasyon**

   Her ortam için ayrı konfigürasyon desteği kullanın:

   ```
   config/
   ├── production/    # Sadece production ortamında geçerli
   ├── development/   # Sadece development ortamında geçerli
   └── testing/       # Sadece testing ortamında geçerli
   ```

4. **Hassas Bilgilerin Şifrelenmesi**

   Veritabanı şifreleri, API anahtarları gibi hassas bilgileri şifreleyin:

   ```php
   $config->setEncrypted('services.api.secret_key', 'gizli-api-anahtarı');
   ```

5. **Varsayılan Değer Kullanımı**

   Konfigürasyon değerleri okunurken her zaman varsayılan değer belirtin:

   ```php
   // İyi
   $timeout = $config->get('api.timeout', 30);
   
   // Kaçının
   $timeout = $config->get('api.timeout'); // Değer yoksa null olacak
   ```

## 🌐 ServiceProvider Entegrasyonu

Configuration bileşenlerini framework'ünüze entegre etmek için ConfigServiceProvider kullanabilirsiniz:

```php
// Servis sağlayıcıyı kaydet
$app->registerProvider(ConfigServiceProvider::class);

// Bu, aşağıdaki bileşenleri container'a kaydeder:
// - ConfigLoaderInterface -> PhpConfigLoader
// - EnvironmentInterface -> Environment
// - ConfigCacheInterface -> FileConfigCache
// - EncryptedConfigInterface -> EncryptedConfig
// - ConfigRepositoryInterface -> ConfigRepository
// - 'config' alias -> ConfigRepository
```

## 🔄 Genişletme ve Özelleştirme

### Özel Konfigürasyon Yükleyici Oluşturma

```php
class XmlConfigLoader implements ConfigLoaderInterface
{
    public function loadFromFile(string $path): array
    {
        // XML dosyasını yükle ve array'e dönüştür
        $xml = simplexml_load_file($path);
        return json_decode(json_encode($xml), true);
    }
    
    // Diğer metod implementasyonları...
}

// Kullanım
$loader = new XmlConfigLoader();
$config->setLoader($loader);
$config->loadFromFile('config.xml');
```

### Özel Önbellekleme Stratejisi

```php
class RedisConfigCache implements ConfigCacheInterface
{
    protected Redis $redis;
    protected string $key;
    
    public function __construct(Redis $redis, string $key = 'config')
    {
        $this->redis = $redis;
        $this->key = $key;
    }
    
    public function cache(array $config): bool
    {
        return $this->redis->set(
            $this->key,
            serialize(['timestamp' => time(), 'config' => $config])
        );
    }
    
    // Diğer metod implementasyonları...
}
```

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b feature/amazing-config`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-config`)
5. Pull Request oluşturun