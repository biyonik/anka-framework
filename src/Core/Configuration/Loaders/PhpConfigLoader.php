<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Loaders;

use Framework\Core\Configuration\Contracts\ConfigLoaderInterface;
use InvalidArgumentException;

/**
 * PHP dosyalarından konfigürasyon yükleyici.
 *
 * @package Framework\Core\Configuration
 * @subpackage Loaders
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class PhpConfigLoader implements ConfigLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadFromFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('Konfigürasyon dosyası bulunamadı: %s', $path));
        }

        // PHP dosyası için doğrudan include edebiliriz
        $data = require $path;

        if (!is_array($data)) {
            throw new InvalidArgumentException(
                sprintf('Konfigürasyon dosyası bir array döndürmelidir: %s', $path)
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromDirectory(string $directory, string $extension = 'php'): array
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException(sprintf('Konfigürasyon dizini bulunamadı: %s', $directory));
        }

        $config = [];
        $files = glob(rtrim($directory, '/') . '/*.' . $extension);

        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $data = $this->loadFromFile($file);
            $config[$key] = $data;
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromArray(array $data): array
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedExtensions(): array
    {
        return ['php'];
    }
}