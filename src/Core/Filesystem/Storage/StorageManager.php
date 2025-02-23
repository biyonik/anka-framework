<?php

declare(strict_types=1);

namespace Framework\Core\Filesystem\Storage;

use Framework\Core\Filesystem\Adapters\FtpAdapter;
use Framework\Core\Filesystem\Adapters\LocalAdapter;
use Framework\Core\Filesystem\Contracts\FilesystemInterface;
use Framework\Core\Filesystem\Exception\FilesystemException;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;

/**
 * Storage sistemini yöneten ana sınıf.
 *
 * Farklı disk ve sürücüleri yönetir, konfigürasyona göre
 * uygun adapter'ları oluşturur ve cache'ler.
 *
 * @package Framework\Core\Filesystem
 * @subpackage Storage
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class StorageManager
{
    /**
     * Aktif diskler.
     *
     * @var array<string,FilesystemInterface>
     */
    private array $disks = [];

    /**
     * Disk oluşturucu callback'ler.
     *
     * @var array<string,callable>
     */
    private array $customCreators = [];

    /**
     * Constructor.
     */
    public function __construct(
        private readonly ConfigRepositoryInterface $config
    ) {}

    /**
     * Varsayılan diski döndürür.
     */
    public function disk(?string $name = null): FilesystemInterface
    {
        $name = $name ?: $this->getDefaultDisk();

        return $this->disks[$name] = $this->disks[$name] ?? $this->resolve($name);
    }

    /**
     * Özel disk oluşturucu ekler.
     */
    public function extend(string $driver, callable $callback): self
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * Varsayılan disk adını döndürür.
     */
    public function getDefaultDisk(): string
    {
        return $this->config->get('filesystem.default', 'local');
    }

    /**
     * Disk için adapter oluşturur.
     */
    protected function resolve(string $name): FilesystemInterface
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return call_user_func($this->customCreators[$config['driver']], $config);
        }

        return match($config['driver']) {
            'local' => $this->createLocalAdapter($config),
            'ftp' => $this->createFtpAdapter($config),
            default => throw new FilesystemException("Driver [{$config['driver']}] is not supported.")
        };
    }

    /**
     * Disk konfigürasyonunu döndürür.
     *
     * @throws FilesystemException
     */
    protected function getConfig(string $name): array
    {
        $config = $this->config->get("filesystem.disks.{$name}");

        if (!$config) {
            throw new FilesystemException("Disk [{$name}] is not configured.");
        }

        return $config;
    }

    /**
     * Local adapter oluşturur.
     */
    protected function createLocalAdapter(array $config): FilesystemInterface
    {
        return new LocalAdapter(
            $config['root']
        );
    }

    /**
     * FTP adapter oluşturur.
     */
    protected function createFtpAdapter(array $config): FilesystemInterface
    {
        return new FtpAdapter(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['port'] ?? 21,
            $config['ssl'] ?? false,
            $config['timeout'] ?? 30,
            $config['passive'] ?? true
        );
    }

    /**
     * Dinamik metot çağrılarını varsayılan diske yönlendirir.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}