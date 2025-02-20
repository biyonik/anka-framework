<?php

declare(strict_types=1);

namespace Framework\Core\Routing\Interfaces;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

/**
 * Tekil bir route'un davranışlarını tanımlayan arayüz.
 *
 * Bu arayüz, bir route'un temel özelliklerini ve davranışlarını tanımlar.
 * Pattern eşleştirme, middleware yönetimi, parametre işleme ve
 * handler çalıştırma gibi temel özellikleri içerir.
 *
 * @package Framework\Core\Routing
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface RouteInterface
{
    /**
     * Route pattern'ini döndürür.
     *
     * @return string Route pattern'i
     */
    public function getPattern(): string;

    /**
     * Route handler'ını döndürür.
     *
     * @return callable|array|string Route handler'ı
     */
    public function getHandler(): callable|array|string;

    /**
     * Route metodlarını döndürür.
     *
     * @return array<string> HTTP metodları
     */
    public function getMethods(): array;

    /**
     * Route adını döndürür.
     *
     * @return string|null Route adı
     */
    public function getName(): ?string;

    /**
     * Route'a isim atar.
     *
     * @param string $name Route adı
     * @return static
     */
    public function name(string $name): static;

    /**
     * Route middleware'lerini döndürür.
     *
     * @return array<string> Middleware listesi
     */
    public function getMiddleware(): array;

    /**
     * Route'a middleware ekler.
     *
     * @param string|array<string> $middleware Eklenecek middleware(ler)
     * @return static
     */
    public function middleware(string|array $middleware): static;

    /**
     * Route parametrelerini döndürür.
     *
     * @return array<string,string> Route parametreleri
     */
    public function getParameters(): array;

    /**
     * Route parametresi ekler.
     *
     * @param string $name Parametre adı
     * @param string $pattern Parametre pattern'i
     * @return static
     */
    public function where(string $name, string $pattern): static;

    /**
     * Birden fazla route parametresi ekler.
     *
     * @param array<string,string> $parameters Parametre listesi
     * @return static
     */
    public function whereArray(array $parameters): static;

    /**
     * Route'un domain'ini döndürür.
     *
     * @return string|null Route domain'i
     */
    public function getDomain(): ?string;

    /**
     * Route'a domain atar.
     *
     * @param string $domain Route domain'i
     * @return static
     */
    public function domain(string $domain): static;

    /**
     * Route'un prefix'ini döndürür.
     *
     * @return string Route prefix'i
     */
    public function getPrefix(): string;

    /**
     * Route'a prefix ekler.
     *
     * @param string $prefix Eklenecek prefix
     * @return static
     */
    public function prefix(string $prefix): static;

    /**
     * Route'un derlenmiş pattern'ini döndürür.
     *
     * @return string Derlenmiş pattern
     */
    public function getCompiledPattern(): string;

    /**
     * Route'un verilen path ile eşleşip eşleşmediğini kontrol eder.
     *
     * @param string $path Kontrol edilecek path
     * @return bool Eşleşirse true
     */
    public function matches(string $path): bool;

    /**
     * Request'i route handler'ı ile işler.
     *
     * @param ServerRequestInterface $request İşlenecek request
     * @param array<string,string> $parameters Route parametreleri
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, array $parameters = []): ResponseInterface;

    /**
     * Route'un URL'ini oluşturur.
     *
     * @param array<string,mixed> $parameters Route parametreleri
     * @return string Route URL'i
     */
    public function generateUrl(array $parameters = []): string;
}