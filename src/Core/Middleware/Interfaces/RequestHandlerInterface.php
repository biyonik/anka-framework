<?php

declare(strict_types=1);

namespace Framework\Core\Middleware\Interfaces;

use Psr\Http\Server\RequestHandlerInterface as PsrRequestHandlerInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

/**
 * Request handler'ların davranışlarını tanımlar.
 *
 * Bu arayüz PSR-15 RequestHandlerInterface'ini extend eder ve framework'e özgü
 * request handler davranışlarını tanımlar. Handler'lar middleware zincirini
 * yönetir ve son response'u üretir.
 *
 * Özellikler:
 * - PSR-15 uyumlu request handler
 * - Middleware zinciri yönetimi
 * - Response üretimi
 * - Error handling
 *
 * @package Framework\Core\Middleware
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface RequestHandlerInterface extends PsrRequestHandlerInterface
{
    /**
     * Request'i işler ve response döndürür.
     *
     * @param ServerRequestInterface $request İşlenecek request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;

    /**
     * Middleware ekler.
     *
     * @param MiddlewareInterface $middleware Eklenecek middleware
     * @return static
     */
    public function add(MiddlewareInterface $middleware): static;

    /**
     * Birden fazla middleware ekler.
     *
     * @param MiddlewareInterface[] $middlewares Eklenecek middleware'ler
     * @return static
     */
    public function addMiddlewares(array $middlewares): static;

    /**
     * Tüm middleware'leri temizler.
     *
     * @return static
     */
    public function clearMiddlewares(): static;

    /**
     * Handler'ın middleware stack'ini döndürür.
     *
     * @return array<MiddlewareInterface>
     */
    public function getMiddlewares(): array;

    /**
     * Son handler'ı ayarlar.
     * Bu handler, hiçbir middleware response üretmezse çalışır.
     *
     * @param callable $handler Son handler
     * @return static
     */
    public function setFallbackHandler(callable $handler): static;
}