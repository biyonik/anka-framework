<?php

declare(strict_types=1);

namespace Framework\Core\Routing\Interfaces;

use Framework\Core\Http\Request\Request;
use Framework\Core\Http\Response\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

/**
 * Router'ın temel davranışlarını tanımlayan arayüz.
 *
 * Bu arayüz, route tanımlama, eşleştirme ve çalıştırma işlemlerini tanımlar.
 * HTTP metodları için route tanımlama, grup oluşturma, middleware ekleme ve
 * route parametrelerini yönetme gibi temel özellikleri içerir.
 *
 * @package Framework\Core\Routing
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface RouterInterface
{
    /**
     * Route koleksiyonunu döndürür.
     *
     * @return RouteCollectionInterface
     */
    public function getRoutes(): RouteCollectionInterface;

    /**
     * GET metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function get(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * POST metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function post(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * PUT metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function put(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * DELETE metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function delete(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * PATCH metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function patch(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * OPTIONS metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function options(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * HEAD metodu için route tanımlar.
     *
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function head(string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * Birden fazla HTTP metodu için route tanımlar.
     *
     * @param array<string> $methods HTTP metodları
     * @param string $path Route path'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     * @return RouteInterface
     */
    public function map(array $methods, string $path, callable|array|string $handler, ?string $name = null): RouteInterface;

    /**
     * Route grubu oluşturur.
     *
     * @param string $prefix Grup prefix'i
     * @param callable $callback Grup tanımlama callback'i
     * @return static
     */
    public function group(string $prefix, callable $callback): static;

    /**
     * Route prefix'i ekler.
     *
     * @param string $prefix Eklenecek prefix
     * @return static
     */
    public function prefix(string $prefix): static;

    /**
     * Route namespace'i ekler.
     *
     * @param string $namespace Eklenecek namespace
     * @return static
     */
    public function namespace(string $namespace): static;

    /**
     * Route middleware'i ekler.
     *
     * @param string|array<string> $middleware Eklenecek middleware(ler)
     * @return static
     */
    public function middleware(string|array $middleware): static;

    /**
     * İsimlendirilmiş route döndürür.
     *
     * @param string $name Route adı
     * @param array<string,mixed> $parameters Route parametreleri
     * @return string Route URL'i
     */
    public function route(string $name, array $parameters = []): string;

    /**
     * Request'i eşleşen route ile işler.
     *
     * @param ServerRequestInterface $request İşlenecek request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * Request için eşleşen route'u bulur.
     *
     * @param ServerRequestInterface $request Eşleştirilecek request
     * @return RouteInterface|null Eşleşen route veya null
     */
    public function match(ServerRequestInterface $request): ?RouteInterface;

    /**
     * Route pattern'i derler.
     *
     * @param string $pattern Derlenecek pattern
     * @return string Derlenmiş pattern
     */
    public function compilePattern(string $pattern): string;
}