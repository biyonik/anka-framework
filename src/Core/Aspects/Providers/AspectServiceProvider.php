<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Providers;

use Framework\Core\Aspects\AspectRegistry;
use Framework\Core\Aspects\AttributeListenerManager;
use Framework\Core\Aspects\Contracts\AspectRegistryInterface;
use Framework\Core\Aspects\MethodInvoker;
use Framework\Core\Aspects\ProxyFactory;
use Framework\Core\Container\Contracts\ContainerInterface;
use Framework\Core\Container\Contracts\ServiceProviderInterface;

/**
 * Aspect bileşeni için servis sağlayıcı.
 *
 * Bu sınıf, framework'ün aspect bileşenlerini container'a kaydeder.
 *
 * @package Framework\Core\Aspects
 * @subpackage Providers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AspectServiceProvider implements ServiceProviderInterface
{
    /**
     * Servis sağlayıcının kaydını yapar.
     *
     * @param ContainerInterface $container DI container
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        // AspectRegistry kaydı
        $container->singleton(AspectRegistryInterface::class, function () {
            return new AspectRegistry();
        });

        // MethodInvoker kaydı
        $container->singleton(MethodInvoker::class, function (ContainerInterface $container) {
            return new MethodInvoker(
                $container->get(AspectRegistryInterface::class)
            );
        });

        // ProxyFactory kaydı
        $container->singleton(ProxyFactory::class, function (ContainerInterface $container) {
            $basePath = $container->get('base_path', '');
            $cachePath = rtrim($basePath, '/') . '/storage/framework/cache/proxies';

            return new ProxyFactory(
                $container->get(AspectRegistryInterface::class),
                $cachePath
            );
        });

        // AttributeListenerManager kaydı
        $container->singleton(AttributeListenerManager::class, function (ContainerInterface $container) {
            return new AttributeListenerManager(
                $container->get(AspectRegistryInterface::class)
            );
        });

        // Alias kayıtları
        $container->alias('aspect.registry', AspectRegistryInterface::class);
        $container->alias('aspect.method_invoker', MethodInvoker::class);
        $container->alias('aspect.proxy_factory', ProxyFactory::class);
        $container->alias('aspect.attribute_manager', AttributeListenerManager::class);
    }

    /**
     * Servis sağlayıcıyı başlatır.
     *
     * @param ContainerInterface $container DI container
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        // Konfigürasyondan aspect dizinleri
        $aspectPaths = $container->get('config')->get('aspects.paths', []);

        if (empty($aspectPaths)) {
            return;
        }

        // AttributeListenerManager al
        $manager = $container->get(AttributeListenerManager::class);

        // Aspect dizinlerini tara
        foreach ($aspectPaths as $path => $namespace) {
            if (is_dir($path)) {
                $manager->registerListenersFromDirectory($path, $namespace);
            }
        }
    }

    /**
     * Bu servis sağlayıcının bağımlılıklarını döndürür.
     *
     * @return array<string> Bağımlılık listesi (servis sağlayıcı sınıf adları)
     */
    public function dependencies(): array
    {
        return [];
    }
}