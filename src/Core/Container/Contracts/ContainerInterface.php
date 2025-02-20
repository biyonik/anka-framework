<?php

declare(strict_types=1);

namespace Framework\Core\Container\Contracts;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Framework\Core\Container\Exceptions\ContainerException;
use Framework\Core\Container\Exceptions\NotFoundException;

/**
 * Framework'ün gelişmiş Dependency Injection Container arayüzü.
 * 
 * Bu arayüz, PSR-11 ContainerInterface'ini extend eder ve framework'e özgü 
 * gelişmiş servis container yetenekleri sağlar. Container, uygulama genelinde
 * servislerin yaşam döngüsünü, bağımlılıkların çözümlenmesini ve singleton
 * instance'ların yönetimini kontrol eder.
 * 
 * Temel Özellikler:
 * - PSR-11 uyumlu container implementasyonu
 * - Otomatik bağımlılık enjeksiyonu (autowiring)
 * - Singleton ve transient servis yönetimi
 * - Önceden tanımlanmış servis binding'leri
 * - Attribute tabanlı servis konfigürasyonu
 * - Lazy loading servis desteği
 * 
 * Kullanım:
 * ```php
 * // Servis bağlama
 * $container->bind(Logger::class, FileLogger::class);
 * 
 * // Singleton bağlama
 * $container->singleton(Cache::class, RedisCache::class);
 * 
 * // Servis çözümleme
 * $logger = $container->get(Logger::class);
 * ```
 * 
 * @package Framework\Core\Container
 * @subpackage Contracts
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 * @see PsrContainerInterface
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Bir servis tipini container'a kaydeder.
     * 
     * Bu metod, bir abstract tip (interface veya abstract class) ile concrete tip (concrete class)
     * arasında binding oluşturur. Binding sonrasında abstract tip istendiğinde concrete tip
     * instance'ı döndürülür.
     * 
     * Özellikler:
     * - Autowiring desteği ile constructor injection otomatik olarak çözümlenir
     * - Döngüsel bağımlılıklar tespit edilir ve exception fırlatılır
     * - Servis parametreleri binding sırasında tanımlanabilir
     * 
     * @param string $abstract Bağlanacak abstract tip (interface veya abstract class)
     * @param string|object|callable|null $concrete Concrete tip veya factory callback
     * @param array<string,mixed> $parameters Servis oluşturulurken kullanılacak parametreler
     * @return void
     * 
     * @throws ContainerException Binding sırasında bir hata oluşursa
     * 
     * @example
     * ```php
     * // Interface to class binding
     * $container->bind(LoggerInterface::class, FileLogger::class);
     * 
     * // Instance binding
     * $container->bind(Logger::class, new FileLogger());
     * 
     * // Factory function binding
     * $container->bind(Cache::class, fn(Container $c) => new RedisCache($c->get(Redis::class)));
     * ```
     */
    public function bind(string $abstract, string|object|callable|null $concrete = null, array $parameters = []): void;

    /**
     * Bir servis tipini singleton olarak container'a kaydeder.
     * 
     * Bu metod bind() metoduna benzer şekilde çalışır ancak servisin sadece bir instance'ının
     * oluşturulmasını ve sonraki isteklerde aynı instance'ın dönmesini sağlar.
     * 
     * Özellikler:
     * - İlk çağrıda instance oluşturulur ve saklanır
     * - Sonraki çağrılarda saklanan instance döndürülür
     * - Singleton instance'lar container yaşam süresi boyunca korunur
     * 
     * @param string $abstract Bağlanacak abstract tip
     * @param string|object|callable|null $concrete Concrete tip veya factory callback
     * @param array<string,mixed> $parameters Servis oluşturulurken kullanılacak parametreler
     * @return void
     * 
     * @throws ContainerException Binding sırasında bir hata oluşursa
     * 
     * @example
     * ```php
     * // Singleton class binding
     * $container->singleton(Database::class, MySQLDatabase::class);
     * 
     * // Singleton instance her seferinde aynı olacaktır
     * $db1 = $container->get(Database::class);
     * $db2 = $container->get(Database::class);
     * assert($db1 === $db2); // true
     * ```
     */
    public function singleton(string $abstract, string|object|callable|null $concrete = null, array $parameters = []): void;

    /**
     * Container'a kaydedilmiş bir servisin instance'ını döndürür.
     * 
     * Bu metod PSR-11'den override edilmiştir ve gelişmiş özellikler eklenmiştir:
     * - Autowiring ile otomatik bağımlılık çözümleme
     * - Singleton instance yönetimi
     * - Lazy loading desteği
     * - Döngüsel bağımlılık kontrolü
     * 
     * @template T
     * @param string $id Çözümlenecek servis tipi
     * @param array<string,mixed> $parameters Override edilecek parametreler
     * @return T Servis instance'ı
     * 
     * @throws NotFoundException Servis bulunamazsa
     * @throws ContainerException Servis çözümlenirken hata oluşursa
     * 
     * @example
     * ```php
     * // Basic service resolution
     * $logger = $container->get(Logger::class);
     * 
     * // Resolution with parameter override
     * $cache = $container->get(Cache::class, ['ttl' => 3600]);
     * ```
     */
    public function get(string $id, array $parameters = []): mixed;

    /**
     * Bir servisin container'da kayıtlı olup olmadığını kontrol eder.
     * 
     * Bu metod PSR-11'den override edilmiştir ve şu kontrolleri yapar:
     * - Explicit binding kontrolü
     * - Singleton instance kontrolü
     * - Autowiring yapılabilirlik kontrolü
     * 
     * @param string $id Kontrol edilecek servis tipi
     * @return bool Servis çözümlenebilirse true
     * 
     * @example
     * ```php
     * if ($container->has(Logger::class)) {
     *     $logger = $container->get(Logger::class);
     * }
     * ```
     */
    public function has(string $id): bool;

    /**
     * Container'a kayıtlı bir servisi siler.
     * 
     * Bu metod ile:
     * - Binding'ler kaldırılır
     * - Singleton instance'lar temizlenir
     * - Servis parametreleri sıfırlanır
     * 
     * @param string $abstract Silinecek servis tipi
     * @return void
     * 
     * @throws ContainerException Servis silinemezse
     * 
     * @example
     * ```php
     * $container->unbind(Logger::class);
     * ```
     */
    public function unbind(string $abstract): void;

    /**
     * Container'a kayıtlı tüm servisleri temizler.
     * 
     * Bu metod container'ı sıfırlar:
     * - Tüm binding'ler kaldırılır
     * - Tüm singleton instance'lar temizlenir
     * - Tüm servis parametreleri sıfırlanır
     * 
     * @return void
     * 
     * @example
     * ```php
     * $container->flush();
     * ```
     */
    public function flush(): void;
}