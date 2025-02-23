<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Contracts;

/**
 * Loglama/raporlama davranışını özelleştirmek isteyen exception'lar için interface.
 *
 * Bu interface'i implemente eden exception'lar kendi raporlama
 * mantıklarını tanımlayabilirler. Exception handler, bu interface'i
 * implemente eden exception'lar için özel raporlama davranışını kullanır.
 *
 * @package Framework\Core\Exception
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * Örnek Kullanım:
 * ```php
 * class CustomException extends Exception implements ReportableExceptionInterface
 * {
 *     public function report(): void
 *     {
 *         logger()->critical('Özel hata: ' . $this->getMessage(), [
 *             'exception' => $this
 *         ]);
 *     }
 * }
 * ```
 */
interface ReportableExceptionInterface
{
    /**
     * Exception'ın nasıl raporlanacağını tanımlar.
     *
     * Bu metot, exception oluştuğunda handler tarafından çağrılır ve
     * exception'ın özel raporlama mantığını çalıştırır.
     *
     * @return void
     */
    public function report(): void;

    /**
     * Exception'ın raporlanıp raporlanmayacağını belirler.
     *
     * Exception raporlamanın atlanması gerektiği durumlar için
     * bu metot false dönebilir.
     *
     * @return bool Raporlanacaksa true
     */
    public function shouldReport(): bool;
}