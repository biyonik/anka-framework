<?php

declare(strict_types=1);

namespace Framework\Core\Logging;

use Framework\Core\Application\ServiceProvider\AbstractServiceProvider;
use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Aspects\Contracts\AspectRegistryInterface;
use Framework\Core\Logging\Aspects\LogAspect;
use Framework\Core\Logging\Contracts\LoggerInterface;

/**
 * Logging servisini container'a kaydeden provider.
 *
 * @package Framework\Core\Logging
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class LoggerServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(ApplicationInterface $app): void
    {
        $container = $app->getContainer();

        $container->singleton(LogManager::class, function ($container) use ($app) {
            $config = $container->get('config');
            return new LogManager(
                $container,
                $config
            );
        });

        $container->singleton(LoggerInterface::class, function ($container) {
            return $container->get(LogManager::class)->channel();
        });

        // Helper fonksiyonu için alias
        $container->singleton('logger', function ($container) {
            return $container->get(LogManager::class);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(ApplicationInterface $app): void
    {
        $container = $app->getContainer();

        // Tüm loglara request ID eklemek için HTTP katmanını kullan
        if (class_exists('\\Core\\Http\\Request') && method_exists($app, 'getRequest')) {
            $request = $app->getRequest();
            if (method_exists($request, 'id')) {
                $container->get(LoggerInterface::class)
                    ->withRequestId($request->id());
            }
        }

        // Aspect registry mevcut ise LogAspect'i kaydet
        if ($container->has(AspectRegistryInterface::class)) {
            $aspectRegistry = $container->get(AspectRegistryInterface::class);
            $logAspect = new LogAspect($container->get(LoggerInterface::class));
            $aspectRegistry->register($logAspect);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dependencies(): array
    {
        // Bu provider çalıştırılmadan önce ConfigServiceProvider yüklenmiş olmalı
        return [
            'Core\\Configuration\\Providers\\ConfigServiceProvider'
        ];
    }
}