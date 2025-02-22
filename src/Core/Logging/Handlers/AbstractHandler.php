<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Handlers;

use Framework\Core\Logging\Contracts\LogFormatterInterface;
use Framework\Core\Logging\Contracts\LogHandlerInterface;
use Framework\Core\Logging\Formatters\LineFormatter;
use Framework\Core\Logging\LogLevel;
use Framework\Core\Logging\LogRecord;

/**
 * Temel handler fonksiyonalitesini sağlayan soyut sınıf.
 *
 * @package Framework\Core\Logging
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractHandler implements LogHandlerInterface
{
    /**
     * @var LogFormatterInterface Kullanılacak formatter
     */
    protected LogFormatterInterface $formatter;

    /**
     * @param LogLevel $level Minimum log seviyesi
     * @param bool $bubble İşlendikten sonra diğer handlerlara geçsin mi
     */
    public function __construct(
        protected LogLevel $level = LogLevel::DEBUG,
        protected bool $bubble = true
    ) {
        $this->formatter = new LineFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(LogRecord $record): bool
    {
        return $record->level->isHigherOrEqualTo($this->level);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(LogFormatterInterface $formatter): self
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(): LogFormatterInterface
    {
        return $this->formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records): bool
    {
        $filtered = [];

        foreach ($records as $record) {
            if ($this->isHandling($record)) {
                $filtered[] = $record;
            }
        }

        if (empty($filtered)) {
            return false;
        }

        return $this->processBatch($filtered);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(LogRecord $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $result = $this->write($record);

        return $result && !$this->bubble;
    }

    /**
     * Kayıtları toplu olarak işler.
     *
     * @param LogRecord[] $records İşlenecek kayıtlar
     * @return bool İşlem başarısı
     */
    protected function processBatch(array $records): bool
    {
        foreach ($records as $record) {
            $this->write($record);
        }

        return true;
    }

    /**
     * Kaydı yazar. Alt sınıflar tarafından implemente edilmelidir.
     *
     * @param LogRecord $record Yazılacak kayıt
     * @return bool Yazma başarısı
     */
    abstract protected function write(LogRecord $record): bool;
}