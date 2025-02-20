<?php

declare(strict_types=1);

namespace Framework\Core\Routing;

use Framework\Core\Routing\Interfaces\{RouteCollectionInterface, RouteInterface};

/**
 * Route gruplarını yönetmek için kullanılan sınıf.
 *
 * Bu sınıf, ortak prefix, middleware, domain vb. özellikleri paylaşan
 * route'ları gruplamak için kullanılır. Grup üzerinden eklenen route'lar
 * otomatik olarak grup özelliklerini devralır.
 *
 * @package Framework\Core\Routing
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RouteGroup
{
    /**
     * Route koleksiyonu.
     */
    protected RouteCollectionInterface $routes;

    /**
     * Grup prefix'i.
     */
    protected string $prefix;

    /**
     * Grup middleware'leri.
     *
     * @var array<string>
     */
    protected array $middleware = [];

    /**
     * Grup domain'i.
     */
    protected ?string $domain = null;

    /**
     * Grup namespace'i.
     */
    protected ?string $namespace = null;

    /**
     * Route parametre pattern'leri.
     *
     * @var array<string,string>
     */
    protected array $wheres = [];

    /**
     * Constructor.
     *
     * @param RouteCollectionInterface $routes Route koleksiyonu
     * @param string $prefix Grup prefix'i
     */
    public function __construct(RouteCollectionInterface $routes, string $prefix = '')
    {
        $this->routes = $routes;
        $this->prefix = $prefix;
    }

    /**
     * GET metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function get(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute(['GET'], $path, $handler, $name);
    }

    /**
     * POST metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function post(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute(['POST'], $path, $handler, $name);
    }

    /**
     * PUT metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function put(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute(['PUT'], $path, $handler, $name);
    }

    /**
     * DELETE metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function delete(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute(['DELETE'], $path, $handler, $name);
    }

    /**
     * PATCH metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function patch(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute(['PATCH'], $path, $handler, $name);
    }

    /**
     * OPTIONS metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function options(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute(['OPTIONS'], $path, $handler, $name);
    }

    /**
     * Birden fazla HTTP metodu için route tanımlar.
     *
     * @param array<string> $methods HTTP metodları
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function map(array $methods, string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->addRoute($methods, $path, $handler, $name);
    }

    /**
     * Alt grup oluşturur.
     *
     * @param string $prefix Grup prefix'i
     * @param callable $callback Grup tanımlama callback'i
     * @return static
     */
    public function group(string $prefix, callable $callback): static
    {
        $group = new static($this->routes, $this->prefix . '/' . ltrim($prefix, '/'));

        // Grup özelliklerini miras al
        $group->middleware($this->middleware);

        if ($this->domain) {
            $group->domain($this->domain);
        }

        if ($this->namespace) {
            $group->namespace($this->namespace);
        }

        $group->wheres($this->wheres);

        $callback($group);

        return $this;
    }

    /**
     * Gruba middleware ekler.
     *
     * @param string|array<string> $middleware Eklenecek middleware(ler)
     * @return static
     */
    public function middleware(string|array $middleware): static
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middleware);

        return $this;
    }

    /**
     * Grup domain'ini ayarlar.
     *
     * @param string $domain Grup domain'i
     * @return static
     */
    public function domain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Grup namespace'ini ayarlar.
     *
     * @param string $namespace Grup namespace'i
     * @return static
     */
    public function namespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Grup parametre pattern'i ekler.
     *
     * @param string $name Parametre adı
     * @param string $pattern Parametre pattern'i
     * @return static
     */
    public function where(string $name, string $pattern): static
    {
        $this->wheres[$name] = $pattern;

        return $this;
    }

    /**
     * Birden fazla parametre pattern'i ekler.
     *
     * @param array<string,string> $wheres Pattern listesi
     * @return static
     */
    public function wheres(array $wheres): static
    {
        foreach ($wheres as $name => $pattern) {
            $this->where($name, $pattern);
        }

        return $this;
    }

    /**
     * Route ekler ve grup özelliklerini uygular.
     *
     * @param array<string> $methods HTTP metodları
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    protected function addRoute(array $methods, string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        // Handler'a namespace ekle
        if (is_string($handler) && $this->namespace) {
            $handler = $this->namespace . '\\' . $handler;
        } elseif (is_array($handler) && is_string($handler[0]) && $this->namespace) {
            $handler[0] = $this->namespace . '\\' . $handler[0];
        }

        // Route oluştur
        $route = new Route($methods, $path, $handler, $name);

        // Grup özelliklerini uygula
        $route = $route->prefix($this->prefix);

        if (!empty($this->middleware)) {
            $route = $route->middleware($this->middleware);
        }

        if ($this->domain) {
            $route = $route->domain($this->domain);
        }

        if (!empty($this->wheres)) {
            $route = $route->whereArray($this->wheres);
        }

        // Koleksiyona ekle
        $this->routes->add($route);

        return $route;
    }
}