<?php

declare(strict_types=1);

namespace Framework\Core\Routing;

use Framework\Core\Routing\Interfaces\{RouteCollectionInterface, RouteInterface};
use ArrayIterator;
use Traversable;

/**
 * Route'ların bir koleksiyon olarak yönetilmesini sağlayan sınıf.
 *
 * Bu sınıf, route'ların eklenmesi, silinmesi, filtrelenmesi ve sorgulanması için
 * yöntemler sunar. Ayrıca, route'ların gruplandırılması, prefix eklenmesi ve
 * middleware atanması gibi toplu işlemleri de destekler.
 *
 * @package Framework\Core\Routing
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RouteCollection implements RouteCollectionInterface
{
    /**
     * Route'ların listesi.
     *
     * @var array<RouteInterface>
     */
    protected array $routes = [];

    /**
     * İsimlendirilmiş route'lar.
     *
     * @var array<string,RouteInterface>
     */
    protected array $namedRoutes = [];

    /**
     * Constructor.
     *
     * @param array<RouteInterface> $routes Başlangıç route'ları
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(RouteInterface $route): static
    {
        $this->routes[] = $route;

        // İsimlendirilmiş route ise kaydet
        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMany(array $routes): static
    {
        foreach ($routes as $route) {
            $this->add($route);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getByName(string $name): ?RouteInterface
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getByMethod(string $method): array
    {
        $method = strtoupper($method);

        return array_filter($this->routes, function (RouteInterface $route) use ($method) {
            return in_array($method, $route->getMethods());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getByDomain(string $domain): array
    {
        return array_filter($this->routes, function (RouteInterface $route) use ($domain) {
            return $route->getDomain() === $domain;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getByPrefix(string $prefix): array
    {
        return array_filter($this->routes, function (RouteInterface $route) use ($prefix) {
            return str_starts_with($route->getPattern(), $prefix);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getByMiddleware(string $middleware): array
    {
        return array_filter($this->routes, function (RouteInterface $route) use ($middleware) {
            return in_array($middleware, $route->getMiddleware());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function remove(RouteInterface $route): static
    {
        $this->routes = array_filter($this->routes, function ($item) use ($route) {
            return $item !== $route;
        });

        // İsimlendirilmiş route ise sil
        if ($name = $route->getName()) {
            unset($this->namedRoutes[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeByName(string $name): static
    {
        if (isset($this->namedRoutes[$name])) {
            $this->remove($this->namedRoutes[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): static
    {
        $this->routes = [];
        $this->namedRoutes = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callback): array
    {
        return array_filter($this->routes, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function has(RouteInterface $route): bool
    {
        return in_array($route, $this->routes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function hasName(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $prefix, callable $callback): static
    {
        $group = new RouteGroup($this, $prefix);
        $callback($group);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prefix(string $prefix): static
    {
        foreach ($this->routes as $route) {
            $route->prefix($prefix);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function middleware(string|array $middleware): static
    {
        foreach ($this->routes as $route) {
            $route->middleware($middleware);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function domain(string $domain): static
    {
        foreach ($this->routes as $route) {
            $route->domain($domain);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(): array
    {
        return array_map(function (RouteInterface $route) {
            return $route->getCompiledPattern();
        }, $this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->routes);
    }
}