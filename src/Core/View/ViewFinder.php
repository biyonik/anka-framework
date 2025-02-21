<?php

declare(strict_types=1);

namespace Framework\Core\View;

use Framework\Core\View\Interfaces\ViewFinderInterface;
use InvalidArgumentException;

/**
 * View dosyalarını bulan sınıf.
 * 
 * Bu sınıf, view dosyalarının konumlarını belirlemek için kullanılır.
 * View path'lerini yönetir, view isimlerini dosya yollarına dönüştürür ve
 * namespace'leri destekler.
 * 
 * @package Framework\Core\View
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ViewFinder implements ViewFinderInterface
{
    /**
     * View dosya uzantısı.
     */
    protected string $extension = '.php';

    /**
     * View yolları.
     * 
     * @var array<string>
     */
    protected array $paths = [];

    /**
     * View namespace'leri.
     * 
     * @var array<string,array<string>>
     */
    protected array $namespaces = [];

    /**
     * Bulunan view'ların önbelleği.
     * 
     * @var array<string,string>
     */
    protected array $cache = [];

    /**
     * Constructor.
     * 
     * @param array<string> $paths View klasör yolları
     * @param string $extension View dosya uzantısı
     */
    public function __construct(array $paths = [], string $extension = '.php')
    {
        $this->paths = $paths;
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $view): string
    {
        if (isset($this->cache[$view])) {
            return $this->cache[$view];
        }

        // Namespace'i kontrol et
        if ($this->hasNamespaceHint($view)) {
            return $this->cache[$view] = $this->findNamespacedView($view);
        }

        return $this->cache[$view] = $this->findView($view);
    }

    /**
     * {@inheritdoc}
     */
    public function addPath(string $path): static
    {
        $this->paths[] = rtrim($path, '/\\');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addNamespace(string $namespace, string|array $paths): static
    {
        $paths = (array) $paths;

        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = [];
        }

        $this->namespaces[$namespace] = array_merge(
            $this->namespaces[$namespace],
            array_map(fn($path) => rtrim($path, '/\\'), $paths)
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parseNamespaceSegments(string $view): array
    {
        $segments = explode('::', $view);

        if (count($segments) !== 2) {
            return ['', $view];
        }

        return $segments;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathsForNamespace(string $namespace): array
    {
        return $this->namespaces[$namespace] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $view): bool
    {
        try {
            $this->find($view);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flushCache(): void
    {
        $this->cache = [];
    }

    /**
     * View uzantısını ayarlar.
     * 
     * @param string $extension View uzantısı
     * @return static
     */
    public function setExtension(string $extension): static
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * View uzantısını döndürür.
     * 
     * @return string View uzantısı
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Namespace içeren view'ı bulur.
     * 
     * @param string $view View adı
     * @return string View dosya yolu
     * @throws InvalidArgumentException View bulunamazsa
     */
    protected function findNamespacedView(string $view): string
    {
        [$namespace, $viewName] = $this->parseNamespaceSegments($view);

        if (!isset($this->namespaces[$namespace])) {
            throw new InvalidArgumentException("View namespace [{$namespace}] not defined");
        }

        foreach ($this->namespaces[$namespace] as $path) {
            $viewPath = $this->findViewInPath($viewName, $path);
            if (!empty($viewPath)) {
                return $viewPath;
            }
        }

        throw new InvalidArgumentException("View [{$view}] not found");
    }

    /**
     * Normal view'ı bulur.
     * 
     * @param string $view View adı
     * @return string View dosya yolu
     * @throws InvalidArgumentException View bulunamazsa
     */
    protected function findView(string $view): string
    {
        foreach ($this->paths as $path) {
            $viewPath = $this->findViewInPath($view, $path);
            if (!empty($viewPath)) {
                return $viewPath;
            }
        }

        throw new InvalidArgumentException("View [{$view}] not found");
    }

    /**
     * Belirli bir path'te view'ı bulur.
     * 
     * @param string $view View adı
     * @param string $path Aranacak klasör yolu
     * @return string View dosya yolu veya boş string
     */
    protected function findViewInPath(string $view, string $path): string
    {
        // View adını dosya yoluna dönüştür
        $view = str_replace('.', '/', $view);
        $filePath = $path . '/' . $view . $this->extension;

        if (file_exists($filePath)) {
            return $filePath;
        }

        return '';
    }

    /**
     * View'ın namespace içerip içermediğini kontrol eder.
     * 
     * @param string $view View adı
     * @return bool Namespace içeriyorsa true
     */
    protected function hasNamespaceHint(string $view): bool
    {
        return strpos($view, '::') !== false;
    }
}