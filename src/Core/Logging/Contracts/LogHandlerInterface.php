<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Contracts;

use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını işleyen arayüz.
 *
 * @package Framework\Core\Logging
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface LogHandlerInterface
{
    /**
     * Log kaydını işler.
     *
     * @param LogRecord $record İşlenecek log kaydı
     * @return bool İşlemin başarısı
     */
    public function handle(LogRecord $record): bool;

    /**
     * Birden çok log kaydını toplu olarak işler.
     *
     * @param LogRecord[] $records İşlenecek log kayıtları
     * @return bool İşlemin başarısı
     */
    public function handleBatch(array $records): bool;

    /**
     * Handler için formatter ayarlar.
     *
     * @param LogFormatterInterface $formatter Kullanılacak formatter
     * @return self Zincir metodlar için instance döndürür
     */
    public function setFormatter(LogFormatterInterface $formatter): self;

    /**
     * Handler'ın mevcut formatterını döndürür.
     *
     * @return LogFormatterInterface Formatter
     */
    public function getFormatter(): LogFormatterInterface;

    /**
     * Belirli bir seviyedeki kayıtları işleyip işlemeyeceğini kontrol eder.
     *
     * @param LogRecord $record Kontrol edilecek kayıt
     * @return bool İşlenmeli mi
     */
    public function isHandling(LogRecord $record): bool;
}