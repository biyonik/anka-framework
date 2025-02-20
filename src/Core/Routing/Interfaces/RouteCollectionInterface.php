<?php

declare(strict_types=1);

namespace Framework\Core\Routing\Interfaces;

use Countable;
use IteratorAggregate;

/**
 * Route koleksiyonunun davranışlarını tanımlayan arayüz.
 *
 * Bu arayüz, route'ların bir koleksiyon olarak yönetilmesini sağlar.
 * Route ekleme, çıkarma, arama ve gruplama gibi temel koleksiyon
 * işlemlerini tanımlar.
 *
 * @package Framework\Core\Routing
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @extends IteratorAggregate<RouteInterface>
 */
interface RouteCollectionInterface extends Countable, IteratorAggregate
{
    /**
     * Koleksiyona route ekler.
     *
     * @param RouteInterface $route Eklenecek route
     * @return static
     */
    public function add(RouteInterface $route): static;

    /**
     * Koleksiyona birden fazla route ekler.
     *
     * @param array<RouteInterface> $routes Eklenecek route'lar
     * @return static
     */
    public function addMany(array $routes): static;

    /**
     * Route'u ismiyle bulur.
     *
     * @param string $name Route adı
     * @return RouteInterface|null Bulunan route veya null
     */
    public function getByName(string $name): ?RouteInterface;

    /**
     * Belirli metodun route'larını döndürür.
     *
     * @param string $method HTTP metodu
     * @return array<RouteInterface> Route listesi
     */
    public function getByMethod(string $method): array;

    /**
     * Belirli domain'in route'larını döndürür.
     *
     * @param string $domain Domain adı
     * @return array<RouteInterface> Route listesi
     */
    public function getByDomain(string $domain): array;

    /**
     * Prefix'e göre route'ları filtreler.
     *
     * @param string $prefix Route prefix'i
     * @return array<RouteInterface> Route listesi
     */
    public function getByPrefix(string $prefix): array;

    /**
     * Middleware'e göre route'ları filtreler.
     *
     * @param string $middleware Middleware adı
     * @return array<RouteInterface> Route listesi
     */
    public function getByMiddleware(string $middleware): array;

    /**
     * Koleksiyondan route siler.
     *
     * @param RouteInterface $route Silinecek route
     * @return static
     */
    public function remove(RouteInterface $route): static;

    /**
     * İsme göre route siler.
     *
     * @param string $name Route adı
     * @return static
     */
    public function removeByName(string $name): static;

    /**
     * Tüm route'ları temizler.
     *
     * @return static
     */
    public function clear(): static;

    /**
     * Verilen callback'e göre route'ları filtreler.
     *
     * @param callable $callback Filtreleme callback'i
     * @return array<RouteInterface> Route listesi
     */
    public function filter(callable $callback): array;

    /**
     * Tüm route'ları döndürür.
     *
     * @return array<RouteInterface> Route listesi
     */
    public function all(): array;

    /**
     * Koleksiyonun boş olup olmadığını kontrol eder.
     *
     * @return bool Koleksiyon boşsa true
     */
    public function isEmpty(): bool;

    /**
     * Verilen route'un koleksiyonda olup olmadığını kontrol eder.
     *
     * @param RouteInterface $route Kontrol edilecek route
     * @return bool Route varsa true
     */
    public function has(RouteInterface $route): bool;

    /**
     * Verilen isimde route olup olmadığını kontrol eder.
     *
     * @param string $name Kontrol edilecek route adı
     * @return bool Route varsa true
     */
    public function hasName(string $name): bool;

    /**
     * Route'ları gruplar.
     *
     * @param string $prefix Grup prefix'i
     * @param callable $callback Grup tanımlama callback'i
     * @return static
     */
    public function group(string $prefix, callable $callback): static;

    /**
     * Route'lara prefix ekler.
     *
     * @param string $prefix Eklenecek prefix
     * @return static
     */
    public function prefix(string $prefix): static;

    /**
     * Route'lara middleware ekler.
     *
     * @param string|array<string> $middleware Eklenecek middleware(ler)
     * @return static
     */
    public function middleware(string|array $middleware): static;

    /**
     * Route'lara domain atar.
     *
     * @param string $domain Atanacak domain
     * @return static
     */
    public function domain(string $domain): static;

    /**
     * Route'ların derlenmiş versiyonunu döndürür.
     *
     * @return array<RouteInterface> Derlenmiş route'lar
     */
    public function compile(): array;
}