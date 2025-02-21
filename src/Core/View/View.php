<?php

declare(strict_types=1);

namespace Framework\Core\View;

use Framework\Core\View\Interfaces\{ViewInterface, ViewEngineInterface, ViewFinderInterface};

/**
 * Framework'ün temel view sınıfı.
 * 
 * Bu sınıf, view'ların temsil edilmesi, veri yönetimi ve render edilmesi
 * işlevlerini sağlar. View engine'ini kullanarak şablonları render eder ve
 * veri manipülasyonu için yardımcı metodlar sunar.
 * 
 * @package Framework\Core\View
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 */
class View implements ViewInterface
{
    /**
     * View adı.
     */
    protected string $name;

    /**
     * View engine.
     */
    protected ViewEngineInterface $engine;

    /**
     * View dosya yolu.
     */
    protected string $path;

    /**
     * View verileri.
     * 
     * @var array<string,mixed>
     */
    protected array $data = [];

    /**
     * Layout adı.
     */
    protected ?string $layout = null;

    /**
     * Layout'ta kullanılacak section.
     */
    protected ?string $section = null;

    /**
     * Constructor.
     * 
     * @param string $name View adı
     * @param string $path View dosya yolu
     * @param ViewEngineInterface $engine View engine
     * @param array<string,mixed> $data View verileri
     */
    public function __construct(
        string $name,
        string $path,
        ViewEngineInterface $engine,
        array $data = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->engine = $engine;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function with(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->data[$key] = $value;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withMany(array $data): static
    {
        $clone = clone $this;
        $clone->data = array_merge($clone->data, $data);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        // View'ı render et
        $content = $this->engine->render($this->path, $this->data);
        
        // Layout varsa işle
        if ($this->layout !== null) {
            // Layout view dosyasını bul
            $layoutPath = $this->findLayoutPath($this->layout);
            
            // Layout section'ı belirle
            $section = $this->section ?? 'content';
            
            // Layout'ı render et
            $content = $this->engine->render($layoutPath, array_merge(
                $this->data,
                [$section => $content]
            ));
        }
        
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function withLayout(string $layout): static
    {
        $clone = clone $this;
        $clone->layout = $layout;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * {@inheritdoc}
     */
    public function section(string $section): static
    {
        $clone = clone $this;
        $clone->section = $section;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function shared(string $key, mixed $default = null): mixed
    {
        return $this->engine->getShared($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->render();
    }
    
    /**
     * View dosya yolunu döndürür.
     * 
     * @return string View dosya yolu
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * View engine'i döndürür.
     * 
     * @return ViewEngineInterface View engine
     */
    public function getEngine(): ViewEngineInterface
    {
        return $this->engine;
    }
    
    /**
     * Layout dosya yolunu bulur.
     * 
     * @param string $layout Layout adı
     * @return string Layout dosya yolu
     * @throws \InvalidArgumentException Layout bulunamazsa
     */
    protected function findLayoutPath(string $layout): string
    {
        // Layout için ViewFinder kullan
        $finder = $this->getFinder();
        
        try {
            return $finder->find('layouts.' . $layout);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Layout [{$layout}] not found.", 0, $e);
        }
    }
    
    /**
     * View finder'ı döndürür.
     * 
     * @return ViewFinderInterface View finder
     * @throws \RuntimeException Engine'in finder'ı yoksa
     */
    protected function getFinder(): ViewFinderInterface
    {
        // Engine'den finder'ı al
        $method = new \ReflectionMethod($this->engine, 'getFinder');
        
        if (!$method->isPublic()) {
            throw new \RuntimeException('Engine does not provide access to ViewFinder');
        }
        
        return $this->engine->getFinder();
    }
}