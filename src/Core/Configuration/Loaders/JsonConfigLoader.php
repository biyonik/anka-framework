<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Loaders;

use Framework\Core\Configuration\Contracts\ConfigLoaderInterface;
use InvalidArgumentException;
use JsonException;

/**
 * JSON dosyalarından konfigürasyon yükleyici.
 *
 * @package Framework\Core\Configuration
 * @subpackage Loaders
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class JsonConfigLoader implements ConfigLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadFromFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('Konfigürasyon dosyası bulunamadı: %s', $path));
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new InvalidArgumentException(sprintf('Konfigürasyon dosyası okunamadı: %s', $path));
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                sprintf('Konfigürasyon dosyası geçerli JSON içermiyor: %s (%s)', $path, $e->getMessage()),
                0,
                $e
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromDirectory(string $directory, string $extension = 'json'): array
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
        return ['json'];
    }
}