<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Contracts;

use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını işleyen processor arayüzü.
 *
 * @package Framework\Core\Logging
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface LogProcessorInterface
{
    /**
     * Log kaydını işleyerek ekstra bilgi ekler.
     *
     * @param LogRecord $record İşlenecek kayıt
     * @return LogRecord İşlenmiş kayıt
     */
    public function __invoke(LogRecord $record): LogRecord;
}