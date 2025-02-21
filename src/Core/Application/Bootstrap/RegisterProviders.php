<?php

declare(strict_types=1);

namespace Framework\Core\Application\Bootstrap;

use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Servis provider'ları kaydeden bootstrap sınıfı.
 * 
 * Bu sınıf, uygulama başlatılırken servis provider'ları kaydeder.
 * Konfigürasyonda tanımlanan provider'ları bulur ve register metodlarını çalıştırır.
 * 
 * @package Framework\Core\Application
 * @subpackage Bootstrap
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RegisterProviders implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        // Konfigürasyondan provider'ları al
        $providers = $app->config('providers', []);

        // Her provider'ı kaydet
        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        // Provider'lar yüksek öncelikle kaydedilmeli
        return 10;
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