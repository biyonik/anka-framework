<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Cache;

use Framework\Core\Configuration\Contracts\ConfigCacheInterface;
use RuntimeException;

/**
 * Dosya tabanlı konfigürasyon önbelleği.
 *
 * Bu sınıf, konfigürasyon verilerini disk üzerinde serialize edilmiş halde saklar.
 *
 * @package Framework\Core\Configuration
 * @subpackage Cache
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class FileConfigCache implements ConfigCacheInterface
{
    /**
     * Önbellek dosyasının yolu.
     *
     * @var string
     */
    protected string $cachePath;

    /**
     * Önbelleğin geçerlilik süresi (saniye).
     *
     * @var int
     */
    protected int $ttl;

    /**
     * Constructor.
     *
     * @param string $cachePath Önbellek dosyasının yolu
     * @param int $ttl Önbelleğin geçerlilik süresi (saniye)
     */
    public function __construct(string $cachePath, int $ttl = 3600)
    {
        $this->cachePath = $cachePath;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function cache(array $config): bool
    {
        $directory = dirname($this->cachePath);

        // Dizin yoksa oluştur
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Önbellek dizini oluşturulamadı: %s', $directory));
        }

        // Konfigürasyon verilerini serialize et
        $data = serialize([
            'timestamp' => time(),
            'ttl' => $this->ttl,
            'config' => $config
        ]);

        // Dosyaya yaz
        $result = file_put_contents($this->cachePath, $data);

        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function load(): ?array
    {
        if (!$this->isValid()) {
            return null;
        }

        // Dosyadan oku
        $data = file_get_contents($this->cachePath);

        if ($data === false) {
            return null;
        }

        // Deserialize et
        try {
            /** @var array{timestamp: int, ttl: int, config: array<string, mixed>} $cachedData */
            $cachedData = unserialize($data, ['allowed_classes' => false]);
            return $cachedData['config'];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        if (!file_exists($this->cachePath)) {
            return false;
        }

        // Dosya varsa, geçerlilik süresini kontrol et
        try {
            $data = file_get_contents($this->cachePath);

            if ($data === false) {
                return false;
            }

            /** @var array{timestamp: int, ttl: int, config: array<string, mixed>} $cachedData */
            $cachedData = unserialize($data, ['allowed_classes' => false]);

            // Önbellek süresi dolmuş mu?
            return (time() - $cachedData['timestamp']) < $cachedData['ttl'];
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        if (!file_exists($this->cachePath)) {
            return true;
        }

        return unlink($this->cachePath);
    }

    /**
     * Önbellek dosyasının yolunu değiştirir.
     *
     * @param string $cachePath Yeni önbellek dosyası yolu
     * @return self
     */
    public function setCachePath(string $cachePath): self
    {
        $this->cachePath = $cachePath;
        return $this;
    }

    /**
     * Önbellek geçerlilik süresini değiştirir.
     *
     * @param int $ttl Yeni geçerlilik süresi (saniye)
     * @return self
     */
    public function setTtl(int $ttl): self
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * Önbellek dosyasının yolunu döndürür.
     *
     * @return string Önbellek dosyası yolu
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Önbellek geçerlilik süresini döndürür.
     *
     * @return int Geçerlilik süresi (saniye)
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}