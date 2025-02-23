<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Contracts;

use Throwable;

/**
 * Framework'ün exception handling sisteminin temel arayüzü.
 *
 * Bu interface, farklı ortamlar (HTTP, Console, vb.) için
 * özel exception handler'ların uygulaması gereken temel metotları tanımlar.
 *
 * Exception'ların yakalanması, raporlanması ve render edilmesi işlemlerini
 * standardize eder.
 *
 * @package Framework\Core\Exception
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * Örnek Kullanım:
 * ```php
 * class HttpExceptionHandler implements ExceptionHandlerInterface
 * {
 *     public function handle(Throwable $e): mixed
 *     {
 *         $this->report($e);
 *         return $this->render($request, $e);
 *     }
 * }
 * ```
 */
interface ExceptionHandlerInterface
{
    /**
     * Exception'ı yakalar ve işler.
     *
     * Bu metot, bir exception oluştuğunda çağrılır ve
     * exception'ı raporlama ve render etme işlemlerini koordine eder.
     *
     * @param Throwable $e İşlenecek exception
     * @return mixed Exception'a uygun response
     */
    public function handle(Throwable $e): mixed;

    /**
     * Exception'ı raporlar (loglar).
     *
     * Bu metot exception'ı uygun şekilde loglar ve
     * gerektiğinde diğer raporlama sistemlerine iletir.
     *
     * @param Throwable $e Raporlanacak exception
     * @return void
     */
    public function report(Throwable $e): void;

    /**
     * Exception için uygun response'u render eder.
     *
     * Ortama ve exception tipine göre (HTTP, API, Console)
     * uygun formatta response oluşturur.
     *
     * @param mixed $request İsteği temsil eden nesne (ortama göre değişir)
     * @param Throwable $e Render edilecek exception
     * @return mixed Render edilmiş response
     */
    public function render(mixed $request, Throwable $e): mixed;

    /**
     * Exception'ın raporlanıp raporlanmayacağını kontrol eder.
     *
     * Bazı exception tipleri raporlanmayabilir veya
     * özel koşullarda raporlama atlanabilir.
     *
     * @param Throwable $e Kontrol edilecek exception
     * @return bool Raporlanacaksa true
     */
    public function shouldReport(Throwable $e): bool;
}