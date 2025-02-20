<?php

declare(strict_types=1);

namespace Framework\Core\Routing;

use Framework\Core\Routing\Interfaces\{RouterInterface, RouteCollectionInterface, RouteInterface};
use Framework\Core\Http\Response\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Framework\Core\Middleware\MiddlewareDispatcher;
use RuntimeException;

/**
 * Framework'ün ana router sınıfı.
 *
 * Bu sınıf, HTTP isteklerini route'lara eşleştirme, route tanımlama ve
 * route'ları çalıştırma işlevlerini sağlar. Route koleksiyonu ve middleware
 * entegrasyonu ile kapsamlı bir routing çözümü sunar.
 *
 * @package Framework\Core\Routing
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Router implements RouterInterface
{
    /**
     * Route koleksiyonu.
     */
    protected RouteCollectionInterface $routes;

    /**
     * Grup prefix'i.
     */
    protected string $prefix = '';

    /**
     * Grup namespace'i.
     */
    protected ?string $namespace = null;

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
     * Middleware dispatcher.
     */
    protected ?MiddlewareDispatcher $middlewareDispatcher = null;

    /**
     * Constructor.
     *
     * @param RouteCollectionInterface|null $routes Route koleksiyonu
     * @param MiddlewareDispatcher|null $middlewareDispatcher Middleware dispatcher
     */
    public function __construct(
        ?RouteCollectionInterface $routes = null,
        ?MiddlewareDispatcher $middlewareDispatcher = null
    ) {
        $this->routes = $routes ?? new RouteCollection();
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): RouteCollectionInterface
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['GET'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['POST'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['PUT'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['DELETE'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['PATCH'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function options(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['OPTIONS'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function head(string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        return $this->map(['HEAD'], $path, $handler, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $methods, string $path, callable|array|string $handler, ?string $name = null): RouteInterface
    {
        // Handler'a namespace ekle
        if (is_string($handler) && $this->namespace) {
            $handler = $this->namespace . '\\' . $handler;
        } elseif (is_array($handler) && is_string($handler[0]) && $this->namespace) {
            $handler[0] = $this->namespace . '\\' . $handler[0];
        }

        // Route oluştur
        $path = $this->prefix . '/' . ltrim($path, '/');
        $path = RouteCompiler::normalizeUrl($path);

        $route = new Route($methods, $path, $handler, $name);

        // Grup özelliklerini uygula
        if (!empty($this->middleware)) {
            $route = $route->middleware($this->middleware);
        }

        if ($this->domain) {
            $route = $route->domain($this->domain);
        }

        // Koleksiyona ekle
        $this->routes->add($route);

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $prefix, callable $callback): static
    {
        $group = new RouteGroup($this->routes, $this->prefix . '/' . ltrim($prefix, '/'));

        // Grup özelliklerini miras al
        if (!empty($this->middleware)) {
            $group->middleware($this->middleware);
        }

        if ($this->domain) {
            $group->domain($this->domain);
        }

        if ($this->namespace) {
            $group->namespace($this->namespace);
        }

        $callback($group);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function namespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function middleware(string|array $middleware): static
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        $this->middleware = array_merge($this->middleware, $middleware);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function route(string $name, array $parameters = []): string
    {
        $route = $this->routes->getByName($name);

        if (!$route) {
            throw new RuntimeException("Route '{$name}' not found");
        }

        return $route->generateUrl($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->match($request);

        if (!$route) {
            return new Response(404, [], 'Not Found');
        }

        // Route middleware'lerini al
        $middlewares = $route->getMiddleware();

        // Middleware dispatcher varsa kullan
        if ($this->middlewareDispatcher && !empty($middlewares)) {
            // Route handler'ı fallback olarak ayarla
            $this->middlewareDispatcher->setFallbackHandler(function (ServerRequestInterface $request) use ($route) {
                return $route->handle($request, $route->getParameters());
            });

            // Middleware'leri ekle ve çalıştır
            foreach ($middlewares as $middleware) {
                $this->middlewareDispatcher->add($middleware);
            }

            return $this->middlewareDispatcher->handle($request);
        }

        // Middleware yoksa direkt route'u çalıştır
        return $route->handle($request, $route->getParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request): ?RouteInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $path = RouteCompiler::normalizeUrl($path);

        // HTTP metoduna göre route'ları filtrele
        $methodRoutes = $this->routes->getByMethod($method);

        // Eşleşen route'u bul
        foreach ($methodRoutes as $route) {
            if ($route->matches($path)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function compilePattern(string $pattern): string
    {
        [$compiled] = RouteCompiler::compile($pattern);
        return $compiled;
    }

    /**
     * Middleware dispatcher'ı ayarlar.
     *
     * @param MiddlewareDispatcher $dispatcher Middleware dispatcher
     * @return static
     */
    public function setMiddlewareDispatcher(MiddlewareDispatcher $dispatcher): static
    {
        $this->middlewareDispatcher = $dispatcher;
        return $this;
    }

    /**
     * Resource route'ları oluşturur (RESTful controller için).
     *
     * @param string $name Resource adı
     * @param string $controller Controller sınıfı
     * @param array<string> $only Sadece belirli action'lar
     * @param array<string> $except Hariç tutulacak action'lar
     * @return static
     */
    public function resource(string $name, string $controller, array $only = [], array $except = []): static
    {
        $actions = [
            'index' => ['GET', "/{$name}", 'index'],
            'create' => ['GET', "/{$name}/create", 'create'],
            'store' => ['POST', "/{$name}", 'store'],
            'show' => ['GET', "/{$name}/{id}", 'show'],
            'edit' => ['GET', "/{$name}/{id}/edit", 'edit'],
            'update' => ['PUT', "/{$name}/{id}", 'update'],
            'destroy' => ['DELETE', "/{$name}/{id}", 'destroy'],
        ];

        // Filtreleme yap
        if (!empty($only)) {
            $actions = array_intersect_key($actions, array_flip($only));
        }

        if (!empty($except)) {
            $actions = array_diff_key($actions, array_flip($except));
        }

        // Route'ları oluştur
        foreach ($actions as $action => $config) {
            [$method, $path, $method] = $config;
            $this->map([$method], $path, [
                $controller, $method
            ], "{$name}.{$action}");
        }

        return $this;
    }
}