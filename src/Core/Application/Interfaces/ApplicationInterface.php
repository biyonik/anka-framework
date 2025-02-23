<?php

declare(strict_types=1);

namespace Framework\Core\Application\Interfaces;

use Framework\Core\Container\Contracts\ContainerInterface;
use Framework\Core\Routing\Interfaces\RouterInterface;
use Framework\Core\Middleware\MiddlewareDispatcher;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface as PsrResponseInterface};

/**
 * Framework'ün ana uygulama arayüzü.
 * 
 * Bu arayüz, framework'ün çekirdek işlevselliğini tanımlar.
 * Temel bileşenleri bootstrapping, konfigürasyon, servis provider'ların yönetimi
 * ve HTTP isteklerinin işlenmesi işlevlerini içerir.
 * 
 * @package Framework\Core\Application
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ApplicationInterface
{
    /**
     * Uygulama için temel path'i döndürür.
     * 
     * @return string Uygulama base path'i
     */
    public function getBasePath(): string;

    /**
     * Container instance'ını döndürür.
     * 
     * @return ContainerInterface Container instance'ı
     */
    public function getContainer(): ContainerInterface;

    /**
     * Router instance'ını döndürür.
     * 
     * @return RouterInterface Router instance'ı
     */
    public function getRouter(): RouterInterface;

    /**
     * Middleware dispatcher'ı döndürür.
     * 
     * @return MiddlewareDispatcher Middleware dispatcher
     */
    public function getMiddlewareDispatcher(): MiddlewareDispatcher;

    /**
     * Servis provider kaydeder.
     * 
     * @param string|object $provider Servis provider sınıfı veya instance'ı
     * @return static
     */
    public function register(string|object $provider): static;

    /**
     * Middleware kaydeder.
     * 
     * @param string|array<string> $middleware Middleware(ler)
     * @return static
     */
    public function middleware(string|array $middleware): static;

    /**
     * Servis provider'ları boot eder.
     * 
     * @return static
     */
    public function boot(): static;

    /**
     * Uygulamayı çalıştırır.
     * 
     * @param ServerRequestInterface|null $request İşlenecek request, null ise global'dan oluşturulur
     * @return PsrResponseInterface
     */
    public function run(?ServerRequestInterface $request = null): PsrResponseInterface;

    /**
     * Uygulamayı sonlandırır ve yanıt gönderir.
     * 
     * @param PsrResponseInterface $response Gönderilecek yanıt
     * @return void
     */
    public function terminate(PsrResponseInterface $response): void;

    /**
     * Çevre (environment) adını döndürür.
     * 
     * @return string Çevre adı (production, development, testing)
     */
    public function getEnvironment(): string;

    /**
     * Debug modunda olup olmadığını kontrol eder.
     * 
     * @return bool Debug modundaysa true
     */
    public function isDebug(): bool;

    /**
     * Uygulama versiyonunu döndürür.
     * 
     * @return string Uygulama versiyonu
     */
    public function getVersion(): string;

    /**
     * Konfigürasyon değerini döndürür.
     * 
     * @param string $key Konfigürasyon anahtarı
     * @param mixed $default Bulunamazsa dönecek değer
     * @return mixed Konfigürasyon değeri
     */
    public function config(string $key, mixed $default = null): mixed;

    /**
     * Bootstrap sınıfı kaydeder.
     * 
     * @param string|object $bootstrapper Bootstrap sınıfı veya instance'ı
     * @return static
     */
    public function bootstrap(string|object $bootstrapper): static;
}