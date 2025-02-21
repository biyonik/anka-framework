<?php

declare(strict_types=1);

namespace Framework\Core\View\Engines;

use Framework\Core\View\Interfaces\{ViewEngineInterface, ViewFinderInterface};

/**
 * View engine'ler için temel abstract sınıf.
 * 
 * Bu sınıf, view engine'ler için ortak işlevselliği sağlar.
 * View finder entegrasyonu, shared data yönetimi ve 
 * cache yönetimi gibi temel özellikleri içerir.
 * 
 * @package Framework\Core\View
 * @subpackage Engines
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractViewEngine implements ViewEngineInterface
{
    /**
     * View finder.
     */
    protected ViewFinderInterface $finder;

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
     */
    public function __construct(ViewFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        return $this->finder->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function share(string $key, mixed $value): static
    {
        $this->shared[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function shareMany(array $data): static
    {
        $this->shared = array_merge($this->shared, $data);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShared(string $key, mixed $default = null): mixed
    {
        return $this->shared[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllShared(): array
    {
        return $this->shared;
    }

    /**
     * {@inheritdoc}
     */
    public function flushCache(): void
    {
        // Default implementation, alt sınıflar override edebilir
    }

    /**
     * {@inheritdoc}
     */
    public function renderComponent(string $component, array $data = []): string
    {
        $viewName = 'components.' . $component;
        
        try {
            $path = $this->finder->find($viewName);
            return $this->render($path, $data);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                "Component '{$component}' not found.",
                0,
                $e
            );
        }
    }

    /**
     * View datası ile shared datayı birleştirir.
     * 
     * @param array<string,mixed> $data View data
     * @return array<string,mixed> Birleştirilmiş data
     */
    protected function mergeSharedData(array $data): array
    {
        return array_merge($this->shared, $data);
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
}