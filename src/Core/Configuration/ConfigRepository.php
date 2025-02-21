<?php

declare(strict_types=1);

namespace Framework\Core\Configuration;

use Framework\Core\Configuration\Contracts\ConfigCacheInterface;
use Framework\Core\Configuration\Contracts\ConfigLoaderInterface;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Configuration\Contracts\EncryptedConfigInterface;
use Framework\Core\Configuration\Contracts\EnvironmentInterface;
use Framework\Core\Configuration\Loaders\PhpConfigLoader;

/**
 * Konfigürasyon deposu.
 *
 * Bu sınıf, konfigürasyon verilerinin merkezi yönetimini sağlar.
 * Farklı kaynaklardan konfigürasyon yükleme, çevre bazlı değer yönetimi,
 * şifreleme ve önbellekleme gibi özellikleri entegre eder.
 *
 * @package Framework\Core\Configuration
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * Konfigürasyon değerlerini tutan array.
     *
     * @var array<string, mixed>
     */
    protected array $items = [];

    /**
     * Çevre yöneticisi.
     *
     * @var EnvironmentInterface|null
     */
    protected ?EnvironmentInterface $environment = null;

    /**
     * Konfigürasyon yükleyici.
     *
     * @var ConfigLoaderInterface
     */
    protected ConfigLoaderInterface $loader;

    /**
     * Konfigürasyon önbelleği.
     *
     * @var ConfigCacheInterface|null
     */
    protected ?ConfigCacheInterface $cache = null;

    /**
     * Şifreleme yöneticisi.
     *
     * @var EncryptedConfigInterface|null
     */
    protected ?EncryptedConfigInterface $encrypter = null;

    /**
     * Önbellekleme etkin mi?
     *
     * @var bool
     */
    protected bool $cacheEnabled = true;

    /**
     * Constructor.
     *
     * @param ConfigLoaderInterface|null $loader Konfigürasyon yükleyici
     * @param EnvironmentInterface|null $environment Çevre yöneticisi
     * @param ConfigCacheInterface|null $cache Önbellek
     * @param EncryptedConfigInterface|null $encrypter Şifreleme yöneticisi
     */
    public function __construct(
        ?ConfigLoaderInterface $loader = null,
        ?EnvironmentInterface $environment = null,
        ?ConfigCacheInterface $cache = null,
        ?EncryptedConfigInterface $encrypter = null
    ) {
        $this->loader = $loader ?? new PhpConfigLoader();
        $this->environment = $environment;
        $this->cache = $cache;
        $this->encrypter = $encrypter;

        // Önbellek varsa ve geçerliyse, konfigürasyonu önbellekten yükle
        if ($this->cache !== null && $this->cache->isValid() && $this->cacheEnabled) {
            $cachedConfig = $this->cache->load();

            if ($cachedConfig !== null) {
                $this->items = $cachedConfig;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Şifreli değerleri otomatik olarak çöz
        $value = $this->getValueByKey($key, $default);

        if ($this->encrypter !== null && is_string($value) && $this->encrypter->isEncrypted($value)) {
            try {
                return $this->encrypter->decrypt($value);
            } catch (\Throwable $e) {
                // Şifre çözme hatası durumunda, şifreli değeri döndür
                return $value;
            }
        }

        return $value;
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

        // Önbelleği güncelle
        $this->refreshCache();
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

        // Önbelleği güncelle
        $this->refreshCache();
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): EnvironmentInterface
    {
        if ($this->environment === null) {
            throw new \RuntimeException('Çevre yöneticisi ayarlanmamış');
        }

        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(EnvironmentInterface $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(): ConfigLoaderInterface
    {
        return $this->loader;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoader(ConfigLoaderInterface $loader): self
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCache(): ?ConfigCacheInterface
    {
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function setCache(?ConfigCacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncrypter(): ?EncryptedConfigInterface
    {
        return $this->encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncrypter(?EncryptedConfigInterface $encrypter): self
    {
        $this->encrypter = $encrypter;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromFile(string $path, bool $merge = true): self
    {
        $data = $this->loader->loadFromFile($path);
        $this->load($data, $merge);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromDirectory(string $directory, string $extension = 'php', bool $merge = true): self
    {
        $data = $this->loader->loadFromDirectory($directory, $extension);
        $this->load($data, $merge);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshCache(): bool
    {
        if ($this->cache === null || !$this->cacheEnabled) {
            return false;
        }

        return $this->cache->cache($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getEncrypted(string $key): ?string
    {
        if ($this->encrypter === null) {
            throw new \RuntimeException('Şifreleme yöneticisi ayarlanmamış');
        }

        $value = $this->getValueByKey($key, null);

        if ($value === null) {
            return null;
        }

        if (is_string($value) && $this->encrypter->isEncrypted($value)) {
            return $value;
        }

        return $this->encrypter->encrypt($value);
    }

    /**
     * {@inheritdoc}
     */
    public function setEncrypted(string $key, mixed $value): void
    {
        if ($this->encrypter === null) {
            throw new \RuntimeException('Şifreleme yöneticisi ayarlanmamış');
        }

        $encrypted = $this->encrypter->encrypt($value);
        $this->set($key, $encrypted);
    }

    /**
     * Önbellekleme özelliğini açar veya kapatır.
     *
     * @param bool $enabled Etkinleştirme durumu
     * @return self
     */
    public function setCacheEnabled(bool $enabled): self
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    /**
     * Dot notation kullanarak konfigürasyon değerini döndürür.
     *
     * @param string $key Konfigürasyon anahtarı
     * @param mixed $default Varsayılan değer
     * @return mixed Konfigürasyon değeri
     */
    protected function getValueByKey(string $key, mixed $default): mixed
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
}