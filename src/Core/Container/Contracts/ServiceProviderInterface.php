<?php

declare(strict_types=1);

namespace Framework\Core\Container\Contracts;

/**
 * Uygulama modüllerinin servislerini container'a kaydetmesini sağlayan arayüz.
 * 
 * ServiceProvider'lar, bir modülün veya paketin ihtiyaç duyduğu servislerin
 * container'a kaydedilmesini ve konfigüre edilmesini sağlar. Ayrıca servisler
 * arasındaki bağımlılıkların yönetimini de kolaylaştırır.
 * 
 * Özellikler:
 * - Servislerin merkezi yönetimi
 * - Lazy loading desteği
 * - Servisler arası bağımlılık yönetimi
 * - Kolay konfigürasyon
 * 
 * @package Framework\Core\Container
 * @subpackage Contracts
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * class DatabaseServiceProvider implements ServiceProviderInterface
 * {
 *     public function register(Container $container): void
 *     {
 *         $container->singleton(Database::class, MySQLDatabase::class);
 *     }
 * 
 *     public function boot(Container $container): void
 *     {
 *         $database = $container->get(Database::class);
 *         $database->connect();
 *     }
 * }
 * ```
 */
interface ServiceProviderInterface
{
    /**
     * Provider'ın bağımlı olduğu diğer provider'ları döndürür.
     * 
     * Bu metodun döndürdüğü provider'lar, mevcut provider'dan önce
     * register ve boot edilir. Bu sayede servisler arası bağımlılıklar
     * doğru sırada yüklenir.
     * 
     * @return array<class-string> Provider sınıflarının listesi
     */
    public function dependencies(): array;

    /**
     * Servisleri container'a kaydeder.
     * 
     * Bu metod, servislerin binding'lerini oluşturur. Henüz instance
     * oluşturmaz, sadece container'a nasıl oluşturulacaklarını söyler.
     * Bu aşamada başka servisler henüz mevcut olmayabilir.
     * 
     * @param ContainerInterface $container DI container
     * @return void
     */
    public function register(ContainerInterface $container): void;

    /**
     * Kayıtlı servisleri başlatır.
     * 
     * Bu metod tüm provider'lar register edildikten sonra çağrılır.
     * Servislerin ilk kez oluşturulması, konfigüre edilmesi ve
     * birbirleriyle iletişime geçmesi bu aşamada yapılır.
     * 
     * @param ContainerInterface $container DI container
     * @return void
     */
    public function boot(ContainerInterface $container): void;
}