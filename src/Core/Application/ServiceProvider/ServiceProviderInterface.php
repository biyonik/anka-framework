<?php

declare(strict_types=1);

namespace Framework\Core\Application\ServiceProvider;

use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Servis provider'ların davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz, framework'e harici servislerin entegrasyonu için kullanılır.
 * Her servis provider, belirli servislerin kaydedilmesi ve başlatılması
 * işlemlerini gerçekleştirir.
 * 
 * @package Framework\Core\Application
 * @subpackage ServiceProvider
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ServiceProviderInterface
{
    /**
     * Servisleri kaydeder.
     * 
     * Bu metod, container'a servis binding'lerini kaydeder.
     * Henüz instance'lar oluşturulmaz, sadece tanımları yapılır.
     * 
     * @param ApplicationInterface $app Uygulama instance'ı
     * @return void
     */
    public function register(ApplicationInterface $app): void;

    /**
     * Servisleri başlatır.
     * 
     * Bu metod, tüm servis provider'lar register edildikten sonra çalışır.
     * Servislerin ilk kez oluşturulması ve hazırlanması bu aşamada yapılır.
     * 
     * @param ApplicationInterface $app Uygulama instance'ı
     * @return void
     */
    public function boot(ApplicationInterface $app): void;

    /**
     * Provider'ın çalışması için gereken diğer provider'ları döndürür.
     * 
     * @return array<class-string> Bağımlı olunan provider'ların listesi
     */
    public function dependencies(): array;

    /**
     * Provider'ın hangi çevrelerde çalışacağını döndürür.
     * Boş dizi dönerse tüm çevrelerde çalışır.
     * 
     * @return array<string> Çevre listesi
     */
    public function environments(): array;

    /**
     * Provider'ın defer edilip edilmeyeceğini döndürür.
     * Defer edilen provider'lar, sadece servisleri kullanıldığında boot edilir.
     * 
     * @return bool Defer edilecekse true
     */
    public function isDeferred(): bool;

    /**
     * Provider'ın sağladığı servisleri döndürür.
     * Defer edilen provider'lar için gereklidir.
     * 
     * @return array<string> Servis listesi
     */
    public function provides(): array;
}