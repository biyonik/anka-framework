<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Loaders;

use Framework\Core\Configuration\Contracts\ConfigLoaderInterface;
use InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * YAML dosyalarından konfigürasyon yükleyici.
 *
 * Bu sınıf, Symfony Yaml bileşenini kullanarak YAML formatındaki
 * konfigürasyon dosyalarını yükler.
 *
 * @package Framework\Core\Configuration
 * @subpackage Loaders
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class YamlConfigLoader implements ConfigLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadFromFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('Konfigürasyon dosyası bulunamadı: %s', $path));
        }

        try {
            $data = Yaml::parseFile($path);

            if (!is_array($data)) {
                throw new InvalidArgumentException(
                    sprintf('Konfigürasyon dosyası bir array döndürmelidir: %s', $path)
                );
            }

            return $data;
        } catch (ParseException $e) {
            throw new InvalidArgumentException(
                sprintf('YAML dosyası parse edilemedi: %s (%s)', $path, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromDirectory(string $directory, string $extension = 'yaml'): array
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
        return ['yaml', 'yml'];
    }
}