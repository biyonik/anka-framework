<?php

declare(strict_types=1);

namespace Framework\Core\Filesystem;

use Framework\Core\Application\ServiceProvider\AbstractServiceProvider;
use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Filesystem\Storage\StorageManager;
use Framework\Core\Filesystem\Contracts\FilesystemInterface;

/**
 * Filesystem servislerini kaydeden provider.
 *
 * @package Framework\Core\Filesystem
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class FilesystemServiceProvider extends AbstractServiceProvider
{
    /**
     * Constructor'da register edilecek sınıflar.
     *
     * @var array<string,string>
     */
    protected array $singletons = [
        StorageManager::class => StorageManager::class,
        FilesystemInterface::class => StorageManager::class,
        'storage' => StorageManager::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register(ApplicationInterface $app): void
    {
        parent::register($app);

        // Storage klasörünü yarat
        $this->ensureStorageDirectoryExists($app);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(ApplicationInterface $app): void
    {
        // Symbolic link'leri oluştur
        $this->createSymbolicLinks($app);
    }

    /**
     * {@inheritdoc}
     */
    public function dependencies(): array
    {
        return [
            'Framework\Core\Configuration\ConfigServiceProvider',
        ];
    }

    /**
     * Storage dizininin varlığını kontrol eder ve gerekirse oluşturur.
     */
    protected function ensureStorageDirectoryExists(ApplicationInterface $app): void
    {
        $paths = [
            storage_path(),
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/private'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($paths as $path) {
            if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
    }

    /**
     * Konfigürasyonda tanımlı sembolik linkleri oluşturur.
     */
    protected function createSymbolicLinks(ApplicationInterface $app): void
    {
        $links = $app->getContainer()
            ->get('config')
            ->get('filesystem.links', []);

        foreach ($links as $link => $target) {
            if (!file_exists($link)) {
                $success = symlink($target, $link);

                if (!$success) {
                    $app->getContainer()
                        ->get('logger')
                        ->warning("Could not create symbolic link: {$link} -> {$target}");
                }
            }
        }
    }
}