<?php

declare(strict_types=1);

namespace Framework\Core\Application\Bootstrap;

use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Global middleware'leri kaydeden bootstrap sınıfı.
 * 
 * Bu sınıf, uygulama başlatılırken global middleware'leri kaydeder.
 * Konfigürasyonda tanımlanan middleware'leri bulur ve kaydeder.
 * 
 * @package Framework\Core\Application
 * @subpackage Bootstrap
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RegisterMiddleware implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        // Global middleware'leri konfigürasyondan al
        $middleware = $app->config('middleware', []);
        
        // Her middleware'i kaydet
        foreach ($middleware as $middlewareClass) {
            $app->middleware($middlewareClass);
        }
        
        // Çevre bazlı middleware'ler
        $env = $app->getEnvironment();
        $envMiddleware = $app->config("middleware.{$env}", []);
        
        foreach ($envMiddleware as $middlewareClass) {
            $app->middleware($middlewareClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        // Route'lardan sonra çalışmalı
        return 30;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldRun(ApplicationInterface $app): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function runsInEnvironment(string $environment): bool
    {
        return true;
    }
}