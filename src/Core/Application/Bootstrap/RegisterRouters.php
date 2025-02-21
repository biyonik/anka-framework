<?php

declare(strict_types=1);

namespace Framework\Core\Application\Bootstrap;

use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Route'ları kaydeden bootstrap sınıfı.
 * 
 * Bu sınıf, uygulama başlatılırken route'ları kaydeder.
 * Routes dizinindeki dosyaları bulur ve çalıştırır.
 * 
 * @package Framework\Core\Application
 * @subpackage Bootstrap
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RegisterRoutes implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        $routesPath = $app->getBasePath() . '/routes';
        
        // Web route'ları
        $webRoutesFile = $routesPath . '/web.php';
        if (file_exists($webRoutesFile)) {
            $router = $app->getRouter();
            require $webRoutesFile;
        }
        
        // API route'ları
        $apiRoutesFile = $routesPath . '/api.php';
        if (file_exists($apiRoutesFile)) {
            $router = $app->getRouter();
            $router->prefix('/api');
            require $apiRoutesFile;
        }
        
        // Console route'ları
        $consoleRoutesFile = $routesPath . '/console.php';
        if (file_exists($consoleRoutesFile)) {
            require $consoleRoutesFile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        // Provider'lardan sonra, middleware'den önce çalışmalı
        return 20;
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