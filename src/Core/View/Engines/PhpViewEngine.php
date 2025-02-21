<?php

declare(strict_types=1);

namespace Framework\Core\View\Engines;

use Framework\Core\View\Interfaces\ViewFinderInterface;

/**
 * PHP dosyalarını template olarak kullanan view engine.
 * 
 * Bu sınıf, PHP dosyalarını template olarak kullanarak view render etmeyi sağlar.
 * Basit ve hızlı bir engine olup, PHP'nin kendi template yeteneklerini kullanır.
 * 
 * @package Framework\Core\View
 * @subpackage Engines
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class PhpViewEngine extends AbstractViewEngine
{
    /**
     * Render edilmiş view önbelleği.
     * 
     * @var array<string,string>
     */
    protected array $cache = [];

    /**
     * Önbellek aktif mi?
     */
    protected bool $cachingEnabled = false;

    /**
     * Constructor.
     * 
     * @param ViewFinderInterface $finder View finder
     * @param bool $cachingEnabled Önbellekleme aktif mi
     */
    public function __construct(ViewFinderInterface $finder, bool $cachingEnabled = false)
    {
        parent::__construct($finder);
        $this->cachingEnabled = $cachingEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $path, array $data = []): string
    {
        // Önbellekte var mı kontrol et
        $cacheKey = $path . ':' . md5(serialize($data));
        
        if ($this->cachingEnabled && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // Dosya var mı kontrol et
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("View file {$path} does not exist");
        }
        
        // Veriyi extract et
        $data = $this->mergeSharedData($data);
        extract($data, EXTR_SKIP);
        
        // Output buffer başlat
        ob_start();
        
        // View dosyasını include et
        include $path;
        
        // Buffer'ı al ve temizle
        $content = ob_get_clean();
        
        if ($content === false) {
            throw new \RuntimeException("Failed to render view {$path}");
        }
        
        // Önbelleğe ekle
        if ($this->cachingEnabled) {
            $this->cache[$cacheKey] = $content;
        }
        
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function flushCache(): void
    {
        $this->cache = [];
    }

    /**
     * Önbellekleme durumunu ayarlar.
     * 
     * @param bool $enabled Önbellekleme durumu
     * @return static
     */
    public function setCaching(bool $enabled): static
    {
        $this->cachingEnabled = $enabled;
        return $this;
    }

    /**
     * Önbellekleme durumunu döndürür.
     * 
     * @return bool Önbellekleme aktif mi
     */
    public function isCachingEnabled(): bool
    {
        return $this->cachingEnabled;
    }
}