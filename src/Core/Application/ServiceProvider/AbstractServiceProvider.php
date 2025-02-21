<?php

declare(strict_types=1);

namespace Framework\Core\Application\ServiceProvider;

use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Servis provider'lar için temel sınıf.
 * 
 * Bu sınıf, ServiceProviderInterface'in temel implementasyonunu sağlar.
 * Varsayılan değerler ve davranışlar içerir, alt sınıfların sadece
 * gerekli metodları override etmesi yeterlidir.
 * 
 * @package Framework\Core\Application
 * @subpackage ServiceProvider
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Provider'ın container'a kaydedilmesi gereken bindings listesi.
     * 
     * @var array<string,string>
     */
    protected array $bindings = [];

    /**
     * Provider'ın container'a kaydedilmesi gereken singleton'lar listesi.
     * 
     * @var array<string,string>
     */
    protected array $singletons = [];

    /**
     * {@inheritdoc}
     */
    public function register(ApplicationInterface $app): void
    {
        $container = $app->getContainer();

        // Binding'leri kaydet
        foreach ($this->bindings as $abstract => $concrete) {
            $container->bind($abstract, $concrete);
        }

        // Singleton'ları kaydet
        foreach ($this->singletons as $abstract => $concrete) {
            $container->singleton($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(ApplicationInterface $app): void
    {
        // Default implementation
    }

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
    public function environments(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isDeferred(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return array_merge(
            array_keys($this->bindings),
            array_keys($this->singletons)
        );
    }

    /**
     * Binding'leri ayarlar.
     * 
     * @param array<string,string> $bindings Binding'ler
     * @return static
     */
    protected function setBindings(array $bindings): static
    {
        $this->bindings = $bindings;
        return $this;
    }

    /**
     * Singleton'ları ayarlar.
     * 
     * @param array<string,string> $singletons Singleton'lar
     * @return static
     */
    protected function setSingletons(array $singletons): static
    {
        $this->singletons = $singletons;
        return $this;
    }
}