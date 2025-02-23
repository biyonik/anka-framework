<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Contracts;

/**
 * Response render davranışını özelleştirmek isteyen exception'lar için interface.
 *
 * Bu interface'i implemente eden exception'lar, handler'a kendi response
 * oluşturma mantıklarını tanımlayabilirler. Exception handler,
 * bu interface'i gördüğünde render için exception'ın kendi metodunu kullanır.
 *
 * @package Framework\Core\Exception
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * Örnek Kullanım:
 * ```php
 * class ApiException extends Exception implements RenderableExceptionInterface
 * {
 *     public function render($request): mixed
 *     {
 *         return response()->json([
 *             'error' => $this->getMessage(),
 *             'code' => $this->getCode()
 *         ], 400);
 *     }
 * }
 * ```
 */
interface RenderableExceptionInterface
{
    /**
     * Exception için response'u oluşturur.
     *
     * Bu metot HTTP isteği, API isteği veya Console durumuna göre
     * uygun formatta bir response oluşturmalıdır.
     *
     * @param mixed $request İsteği temsil eden nesne
     * @return mixed Oluşturulan response
     */
    public function render(mixed $request): mixed;
}