<?php

declare(strict_types=1);

namespace Framework\Core\Routing\Attributes;

use Attribute;

/**
 * Route tanımlamak için kullanılan attribute.
 *
 * Bu attribute, controller metodlarına route özelliklerini tanımlamak için kullanılır.
 * HTTP metodu, path, isim ve middleware gibi route özelliklerini attribute üzerinden
 * tanımlama imkanı sağlar.
 *
 * @package Framework\Core\Routing
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * Örnek Kullanım:
 * ```php
 * #[Route('/users', methods: ['GET'], name: 'users.index')]
 * public function index() { ... }
 *
 * #[Route('/users/{id}', methods: ['GET'], name: 'users.show')]
 * public function show(int $id) { ... }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{
    /**
     * Constructor.
     *
     * @param string $path Route path'i
     * @param array<string> $methods HTTP metodları
     * @param string|null $name Route adı
     * @param array<string> $middleware Middleware'ler
     * @param string|null $domain Route domain'i
     * @param array<string,string> $where Route parametreleri için regex pattern'lar
     * @param int $priority Route önceliği
     */
    public function __construct(
        private string $path,
        private array $methods = ['GET'],
        private ?string $name = null,
        private array $middleware = [],
        private ?string $domain = null,
        private array $where = [],
        private int $priority = 0
    ) {}

    /**
     * Route path'ini döndürür.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * HTTP metodlarını döndürür.
     *
     * @return array<string>
     */
    public function getMethods(): array
    {
        return array_map('strtoupper', $this->methods);
    }

    /**
     * Route adını döndürür.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Middleware'leri döndürür.
     *
     * @return array<string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Domain'i döndürür.
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Route parametre pattern'larını döndürür.
     *
     * @return array<string,string>
     */
    public function getWherePatterns(): array
    {
        return $this->where;
    }

    /**
     * Route önceliğini döndürür.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}