<?php

declare(strict_types=1);

namespace Framework\Core\Routing\Attributes;

use Attribute;

/**
 * Route middleware'lerini tanımlamak için kullanılan attribute.
 *
 * Bu attribute, controller sınıflarına veya metodlarına middleware tanımlamak
 * için kullanılır. Birden fazla middleware'i sıralı şekilde tanımlama
 * imkanı sağlar.
 *
 * @package Framework\Core\Routing
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * Örnek Kullanım:
 * ```php
 * #[Middleware(['auth', 'admin'])]
 * class AdminController { ... }
 *
 * #[Middleware('auth')]
 * public function profile() { ... }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Middleware
{
    /**
     * Middleware listesi.
     *
     * @var array<string>
     */
    private array $middleware;

    /**
     * Constructor.
     *
     * @param string|array<string> $middleware Middleware veya middleware listesi
     */
    public function __construct(string|array $middleware)
    {
        $this->middleware = is_array($middleware) ? $middleware : [$middleware];
    }

    /**
     * Middleware listesini döndürür.
     *
     * @return array<string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Yeni middleware'ler ekler.
     *
     * @param string|array<string> $middleware Eklenecek middleware(ler)
     * @return static
     */
    public function add(string|array $middleware): static
    {
        $new = clone $this;
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        $new->middleware = array_merge($this->middleware, $middleware);
        return $new;
    }

    /**
     * Middleware listesini temizler.
     *
     * @return static
     */
    public function clear(): static
    {
        $new = clone $this;
        $new->middleware = [];
        return $new;
    }

    /**
     * Middleware'leri sıralar.
     *
     * @param array<string> $priorityList Öncelik listesi
     * @return static
     */
    public function sort(array $priorityList): static
    {
        $new = clone $this;
        usort($new->middleware, function ($a, $b) use ($priorityList) {
            $posA = array_search($a, $priorityList);
            $posB = array_search($b, $priorityList);

            if ($posA === false) return 1;
            if ($posB === false) return -1;

            return $posA <=> $posB;
        });
        return $new;
    }
}