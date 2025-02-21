<?php

declare(strict_types=1);

namespace Framework\Core\View;

use Framework\Core\View\Interfaces\{ViewInterface, ViewEngineInterface, ViewFinderInterface};

/**
 * View nesnelerini oluşturan factory sınıfı.
 * 
 * Bu sınıf, view nesnelerinin oluşturulması, view engine'lerin yönetilmesi
 * ve global verilerin paylaşılması işlevlerini sağlar. View sistemi için
 * ana giriş noktasıdır.
 * 
 * @package Framework\Core\View
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ViewFactory
{
    /**
     * View finder.
     */
    protected ViewFinderInterface $finder;

    /**
     * View engine.
     */
    protected ViewEngineInterface $engine;

    /**
     * Global paylaşılan veriler.
     * 
     * @var array<string,mixed>
     */
    protected array $shared = [];

    /**
     * Constructor.
     * 
     * @param ViewFinderInterface $finder View finder
     * @param ViewEngineInterface $engine View engine
     */
    public function __construct(ViewFinderInterface $finder, ViewEngineInterface $engine)
    {
        $this->finder = $finder;
        $this->engine = $engine;
    }

    /**
     * View oluşturur.
     * 
     * @param string $view View adı
     * @param array<string,mixed> $data View verileri
     * @return ViewInterface
     */
    public function make(string $view, array $data = []): ViewInterface
    {
        $path = $this->finder->find($view);
        
        // Global verileri ekle
        $data = array_merge($this->shared, $data);
        
        return new View($view, $path, $this->engine, $data);
    }

    /**
     * Verileri tüm view'lar için paylaşır.
     * 
     * @param string $key Veri anahtarı
     * @param mixed $value Veri değeri
     * @return static
     */
    public function share(string $key, mixed $value): static
    {
        $this->shared[$key] = $value;
        $this->engine->share($key, $value);
        
        return $this;
    }

    /**
     * Birden fazla veriyi tüm view'lar için paylaşır.
     * 
     * @param array<string,mixed> $data Veriler
     * @return static
     */
    public function shareMany(array $data): static
    {
        foreach ($data as $key => $value) {
            $this->share($key, $value);
        }
        
        return $this;
    }

    /**
     * View'ın var olup olmadığını kontrol eder.
     * 
     * @param string $view View adı
     * @return bool View varsa true
     */
    public function exists(string $view): bool
    {
        return $this->finder->exists($view);
    }

    /**
     * View finder'ı döndürür.
     * 
     * @return ViewFinderInterface View finder
     */
    public function getFinder(): ViewFinderInterface
    {
        return $this->finder;
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
     * Başka bir engine kullanan view oluşturur.
     * 
     * @param ViewEngineInterface $engine Kullanılacak engine
     * @param string $view View adı
     * @param array<string,mixed> $data View verileri
     * @return ViewInterface
     */
    public function makeWith(ViewEngineInterface $engine, string $view, array $data = []): ViewInterface
    {
        $path = $this->finder->find($view);
        $data = array_merge($this->shared, $data);
        
        return new View($view, $path, $engine, $data);
    }

    /**
     * View'ı direkt render eder.
     * 
     * @param string $view View adı
     * @param array<string,mixed> $data View verileri
     * @return string Render edilmiş içerik
     */
    public function render(string $view, array $data = []): string
    {
        return $this->make($view, $data)->render();
    }

    /**
     * View path'i ekler.
     * 
     * @param string $path View klasör yolu
     * @return static
     */
    public function addPath(string $path): static
    {
        $this->finder->addPath($path);
        return $this;
    }

    /**
     * View namespace'i ekler.
     * 
     * @param string $namespace Namespace adı
     * @param string|array<string> $paths View klasör yolu veya yolları
     * @return static
     */
    public function addNamespace(string $namespace, string|array $paths): static
    {
        $this->finder->addNamespace($namespace, $paths);
        return $this;
    }

    /**
     * View component'i render eder.
     * 
     * @param string $component Component adı
     * @param array<string,mixed> $data Component verileri
     * @return string Render edilmiş içerik
     */
    public function component(string $component, array $data = []): string
    {
        return $this->engine->renderComponent($component, array_merge($this->shared, $data));
    }

    /**
     * View önbelleğini temizler.
     * 
     * @return void
     */
    public function flushCache(): void
    {
        $this->finder->flushCache();
        $this->engine->flushCache();
    }
}