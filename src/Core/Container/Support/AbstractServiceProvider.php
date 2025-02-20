<?php

declare(strict_types=1);

namespace Framework\Core\Container\Support;

use Framework\Core\Container\Contracts\{ContainerInterface, ServiceProviderInterface};

/**
 * ServiceProvider'lar için temel sınıf.
 * 
 * Bu sınıf, ServiceProvider'lar için ortak işlevselliği sağlar ve
 * varsayılan implementasyonlar sunar. Provider'ların çoğu bu sınıfı
 * extend ederek temel özellikleri hazır olarak kullanabilir.
 * 
 * Özellikler:
 * - Boş dependencies metodu
 * - Boş boot metodu
 * - Deferred loading desteği
 * - Helper metodlar
 * 
 * @package Framework\Core\Container
 * @subpackage Support
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * class LoggingServiceProvider extends AbstractServiceProvider
 * {
 *     protected array $bindings = [
 *         LoggerInterface::class => FileLogger::class
 *     ];
 * 
 *     public function register(ContainerInterface $container): void
 *     {
 *         parent::register($container);
 *         $container->singleton('logger.path', fn() => storage_path('logs'));
 *     }
 * }
 * ```
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Provider'ın kaydedeceği binding'ler.
     * 
     * Key-value pairs şeklinde tanımlanır:
     * - Key: Abstract tip (interface veya abstract class)
     * - Value: Concrete tip
     * 
     * @var array<class-string,class-string>
     */
    protected array $bindings = [];

    /**
     * Provider'ın kaydedeceği singleton'lar.
     * 
     * Key-value pairs şeklinde tanımlanır:
     * - Key: Abstract tip (interface veya abstract class)
     * - Value: Concrete tip
     * 
     * @var array<class-string,class-string>
     */
    protected array $singletons = [];

    /**
     * Provider'ın tag'leyeceği servisler.
     * 
     * Key-value pairs şeklinde tanımlanır:
     * - Key: Tag adı
     * - Value: Servis sınıfları
     * 
     * @var array<string,array<class-string>>
     */
    protected array $tags = [];

    /**
     * {@inheritdoc}
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function register(ContainerInterface $container): void
    {
        // Binding'leri kaydet
        foreach ($this->bindings as $abstract => $concrete) {
            $container->bind($abstract, $concrete);
        }

        // Singleton'ları kaydet
        foreach ($this->singletons as $abstract => $concrete) {
            $container->singleton($abstract, $concrete);
        }

        // Tag'leri kaydet
        foreach ($this->tags as $tag => $services) {
            foreach ($services as $service) {
                // TODO: Container'a tag desteği eklenecek
                // $container->tag($service, $tag);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(ContainerInterface $container): void
    {
        // Default implementation
    }

    /**
     * Servisleri bir tag ile işaretler.
     * 
     * @param string $tag Tag adı
     * @param array<class-string> $services İşaretlenecek servisler
     * @return void
     */
    protected function tag(string $tag, array $services): void
    {
        $this->tags[$tag] = array_merge(
            $this->tags[$tag] ?? [],
            $services
        );
    }

    /**
     * Birden fazla binding'i tek seferde kaydeder.
     * 
     * @param array<class-string,class-string> $bindings Kaydedilecek binding'ler
     * @return void
     */
    protected function bindings(array $bindings): void
    {
        $this->bindings = array_merge(
            $this->bindings,
            $bindings
        );
    }

    /**
     * Birden fazla singleton'ı tek seferde kaydeder.
     * 
     * @param array<class-string,class-string> $singletons Kaydedilecek singleton'lar
     * @return void
     */
    protected function singletons(array $singletons): void
    {
        $this->singletons = array_merge(
            $this->singletons,
            $singletons
        );
    }
}