<?php

declare(strict_types=1);

namespace Framework\Core\Middleware\Interfaces;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP request/response döngüsünde çalışacak middleware'lerin davranışlarını tanımlar.
 *
 * Bu arayüz PSR-15 MiddlewareInterface'ini extend eder ve framework'e özgü
 * middleware davranışlarını tanımlar. Her middleware, request/response zincirinde
 * bir işlem yapma ve zinciri devam ettirme yeteneğine sahiptir.
 *
 * Özellikler:
 * - PSR-15 uyumlu middleware
 * - Request/Response manipülasyonu
 * - Zincirleme middleware desteği
 * - Conditional işlem yapabilme
 *
 * @package Framework\Core\Middleware
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface MiddlewareInterface extends PsrMiddlewareInterface
{
    /**
     * Middleware'i çalıştırır.
     *
     * Bu metod, gelen requesti işler ve:
     * - Request'i modifiye edebilir
     * - Response üretebilir
     * - Sonraki middleware'e geçebilir
     * - İşlemi kesip hata response'u dönebilir
     *
     * @param ServerRequestInterface $request İşlenecek request
     * @param RequestHandlerInterface $handler Sonraki handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    /**
     * Middleware'in çalışıp çalışmayacağını kontrol eder.
     *
     * @param ServerRequestInterface $request Kontrol edilecek request
     * @return bool Middleware çalışacaksa true
     */
    public function shouldRun(ServerRequestInterface $request): bool;

    /**
     * Middleware'in önceliğini döndürür.
     * Düşük sayı, yüksek öncelik anlamına gelir.
     *
     * @return int Öncelik değeri
     */
    public function getPriority(): int;
}