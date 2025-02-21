# Modern PHP Framework

Modern PHP 8+ tabanlı, yüksek performanslı ve sıradışı bir PHP framework.

## 🚀 Özellikler

- PHP 8.2+ gereksinimi
- Domain Driven Design (DDD) prensipleri
- Hexagonal (Ports & Adapters) Mimari
- CQRS (Command Query Responsibility Segregation) Pattern
- Aspect Oriented Programming desteği
- Modern veri yapıları implementasyonları
- Event Sourcing desteği
- Immutable Objects
- Yüksek performanslı Query Builder
- Circuit Breaker Pattern implementasyonu
- Güçlü tip kontrolü ve validasyon sistemi

## 📦 Gereksinimler

- PHP >= 8.2
- Composer >= 2.0
- ext-pdo
- ext-json
- ext-mbstring

## 🛠 Kurulum

```bash
composer create-project ahmetaltun/anka project-name
```

## 📂 Proje Yapısı

```plaintext
project/
├── src/
│   ├── Core/               # Framework çekirdek bileşenleri
│   │   ├── Contracts/      # Interfaces
│   │   ├── DataStructures/ # Özel veri yapıları
│   │   ├── Patterns/      # Tasarım desenleri
│   │   └── Aspects/       # AOP implementasyonları
│   │
│   ├── Domain/            # Domain katmanı
│   │   ├── Entities/      # Domain entities
│   │   ├── ValueObjects/  # Value objects
│   │   └── Services/      # Domain services
│   │
│   ├── Application/       # Uygulama katmanı
│   │   ├── Commands/      # Command handlers
│   │   ├── Queries/       # Query handlers
│   │   └── DTOs/          # Data transfer objects
│   │
│   ├── Infrastructure/    # Altyapı katmanı
│   │   ├── Persistence/   # Veritabanı işlemleri
│   │   ├── Cache/         # Cache mekanizmaları
│   │   └── External/      # Dış servis entegrasyonları
│   │
│   └── Presentation/      # Sunum katmanı
│       ├── Http/          # HTTP controllers
│       ├── Console/       # Console commands
│       └── Views/         # View templates
│
├── tests/                 # Test dosyaları
├── config/               # Konfigürasyon dosyaları
└── public/               # Public dosyalar
```

## 🔧 Konfigürasyon

Framework'ün konfigürasyonu `config/` dizini altındaki dosyalar üzerinden yapılır:

- `config/app.php` - Uygulama ayarları
- `config/database.php` - Veritabanı ayarları
- `config/cache.php` - Cache ayarları

## 📝 Örnek Kullanım

```php
<?php

use Framework\Core\Application;

// Uygulama başlatma
$app = new Application();

// Route tanımlama
$app->router()->get('/users', [UserController::class, 'index']);

// Uygulamayı çalıştırma
$app->run();
```

## 🧪 Testler

```bash
composer test
```

## 📚 Dokümantasyon

Detaylı dokümantasyon için `docs/` dizinini inceleyebilirsiniz.

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Değişikliklerinizi commit edin (`git commit -m 'feat: Add amazing feature'`)
4. Branch'inizi push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📄 Lisans

Bu proje [MIT lisansı](LICENSE) ile lisanslanmıştır.
