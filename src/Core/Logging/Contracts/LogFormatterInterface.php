<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Contracts;

use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını biçimlendiren arayüz.
 *
 * @package Framework\Core\Logging
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface LogFormatterInterface
{
    /**
     * Log kaydını biçimlendirir.
     *
     * @param LogRecord $record Biçimlendirilecek log kaydı
     * @return string|array Biçimlendirilmiş log verisi (string veya array olabilir)
     */
    public function format(LogRecord $record): string|array;

    /**
     * Birden çok log kaydını toplu olarak biçimlendirir.
     *
     * @param LogRecord[] $records Biçimlendirilecek log kayıtları
     * @return array Biçimlendirilmiş log verileri
     */
    public function formatBatch(array $records): array;
}