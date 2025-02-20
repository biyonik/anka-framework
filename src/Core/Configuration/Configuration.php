<?php

declare(strict_types=1);

namespace Framework\Core\Configuration;

use Framework\Core\Configuration\Contracts\ConfigurationInterface;

/**
 * Framework'ün varsayılan konfigürasyon yöneticisi.
 * 
 * Bu sınıf, konfigürasyon değerlerinin merkezi yönetimini sağlar.
 * Dot notation ile nested değerlere erişim, çevre bazlı değer yönetimi
 * ve tip güvenliği özelliklerini sunar.
 * 
 * @package Framework\Core\Configuration
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Konfigürasyon değerlerini tutan array.
     * 
     * @var array<string,mixed>
     */
    private array $items = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!str_contains($key, '.')) {
            return $this->items[$key] ?? $default;
        }

        $array = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value): void
    {
        if (!str_contains($key, '.')) {
            $this->items[$key] = $value;
            return;
        }

        $array = &$this->items;
        $segments = explode('.', $key);
        $last = array_pop($segments);

        foreach ($segments as $segment) {
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array = &$array[$segment];
        }

        $array[$last] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        if (!str_contains($key, '.')) {
            return isset($this->items[$key]);
        }

        $array = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configuration, bool $merge = true): void
    {
        $this->items = $merge
            ? array_merge_recursive($this->items, $configuration)
            : $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->items;
    }
}