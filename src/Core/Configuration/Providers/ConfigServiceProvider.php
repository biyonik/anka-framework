<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Providers;

use Framework\Core\Container\Contracts\ServiceProviderInterface;
use Framework\Core\Container\Contracts\ContainerInterface;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Configuration\Contracts\ConfigLoaderInterface;
use Framework\Core\Configuration\Contracts\EnvironmentInterface;
use Framework\Core\Configuration\Contracts\ConfigCacheInterface;
use Framework\Core\Configuration\Contracts\EncryptedConfigInterface;
use Framework\Core\Configuration\ConfigRepository;
use Framework\Core\Configuration\Environment;
use Framework\Core\Configuration\Cache\FileConfigCache;
use Framework\Core\Configuration\Loaders\PhpConfigLoader;
use Framework\Core\Configuration\Loaders\JsonConfigLoader;
use Framework\Core\Configuration\Loaders\YamlConfigLoader;
use Framework\Core\Configuration\Security\EncryptedConfig;

/**
 * Konfigürasyon bileşeni için servis sağlayıcı.
 *
 * Bu sınıf, framework'ün konfigürasyon bileşenlerini container'a kaydeder.
 *
 * @package Framework\Core\Configuration
 * @subpackage Providers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * Servis sağlayıcının kaydını yapar.
     *
     * @param ContainerInterface $container DI container
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        // Yükleyicileri kaydet
        $this->registerLoaders($container);

        // Çevre yöneticisi
        $container->singleton(EnvironmentInterface::class, function () {
            return new Environment(getenv('APP_ENV') ?: 'production');
        });

        // Konfigürasyon önbelleği
        $container->singleton(ConfigCacheInterface::class, function (ContainerInterface $container) {
            $basePath = $container->get('base_path', '');
            $cachePath = rtrim($basePath, '/') . '/storage/framework/cache/config.cache';

            return new FileConfigCache($cachePath);
        });

        // Şifreleme yöneticisi
        $container->singleton(EncryptedConfigInterface::class, function (ContainerInterface $container) {
            $encrypter = new EncryptedConfig();

            /** @var EnvironmentInterface $environment */
            $environment = $container->get(EnvironmentInterface::class);
            $key = $environment->get('APP_KEY');

            if ($key) {
                $encrypter->setKey($key);
            }

            return $encrypter;
        });

        // Konfigürasyon deposu
        $container->singleton(ConfigRepositoryInterface::class, function (ContainerInterface $container) {
            /** @var ConfigLoaderInterface $loader */
            $loader = $container->get(ConfigLoaderInterface::class);

            /** @var EnvironmentInterface $environment */
            $environment = $container->get(EnvironmentInterface::class);

            /** @var ConfigCacheInterface $cache */
            $cache = $container->get(ConfigCacheInterface::class);

            /** @var EncryptedConfigInterface $encrypter */
            $encrypter = $container->get(EncryptedConfigInterface::class);

            $repository = new ConfigRepository(
                $loader,
                $environment,
                $cache,
                $encrypter
            );

            // Varsayılan konfigürasyon dizinini yükle
            $basePath = $container->get('base_path', '');
            $configPath = rtrim($basePath, '/') . '/config';

            if (is_dir($configPath)) {
                $repository->loadFromDirectory($configPath);
            }

            // Çevre bazlı konfigürasyon dizinini yükle
            $envConfigPath = $configPath . '/' . $environment->getEnvironment();

            if (is_dir($envConfigPath)) {
                $repository->loadFromDirectory($envConfigPath, 'php', true);
            }

            return $repository;
        });

        // Config facade için alias
        $container->alias('config', ConfigRepositoryInterface::class);
    }

    /**
     * Konfigürasyon yükleyicilerini kaydeder.
     *
     * @param ContainerInterface $container DI container
     * @return void
     */
    private function registerLoaders(ContainerInterface $container): void
    {
        // Varsayılan loader
        $container->singleton(ConfigLoaderInterface::class, function () {
            return new PhpConfigLoader();
        });

        // Diğer loaderlar
        $container->bind('config.loader.php', function () {
            return new PhpConfigLoader();
        });

        $container->bind('config.loader.json', function () {
            return new JsonConfigLoader();
        });

        $container->bind('config.loader.yaml', function () {
            return new YamlConfigLoader();
        });

        // Loader factory
        $container->bind('config.loader.factory', function (ContainerInterface $container, array $params) {
            $type = $params['type'] ?? 'php';

            return match ($type) {
                'json' => $container->get('config.loader.json'),
                'yaml', 'yml' => $container->get('config.loader.yaml'),
                default => $container->get('config.loader.php')
            };
        });
    }

    /**
     * Servis sağlayıcıyı başlatır.
     *
     * @param ContainerInterface $container DI container
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        // Konfigürasyon içinde .env dosyasının yolu tanımlanmışsa, çevre değişkenlerini yükle
        /** @var ConfigRepositoryInterface $config */
        $config = $container->get(ConfigRepositoryInterface::class);

        /** @var EnvironmentInterface $environment */
        $environment = $container->get(EnvironmentInterface::class);

        $envPath = $config->get('app.env_path');

        if ($envPath && file_exists($envPath)) {
            $environment->load($envPath);

            // Çevre değişkenleri yüklendikten sonra konfigürasyonu yeniden yükle
            $this->reloadConfigAfterEnvironment($container);
        }
    }

    /**
     * Çevre değişkenleri yüklendikten sonra konfigürasyonu yeniden yükler.
     *
     * @param ContainerInterface $container DI container
     * @return void
     */
    private function reloadConfigAfterEnvironment(ContainerInterface $container): void
    {
        /** @var ConfigRepositoryInterface $config */
        $config = $container->get(ConfigRepositoryInterface::class);

        /** @var EnvironmentInterface $environment */
        $environment = $container->get(EnvironmentInterface::class);

        // Çevre bazlı konfigürasyon dizinini yükle
        $basePath = $container->get('base_path', '');
        $envConfigPath = rtrim($basePath, '/') . '/config/' . $environment->getEnvironment();

        if (is_dir($envConfigPath)) {
            $config->loadFromDirectory($envConfigPath, 'php', true);
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