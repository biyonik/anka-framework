<?php

declare(strict_types=1);

namespace Framework\Core\Logging;

/**
 * PSR-3 uyumlu log seviyeleri.
 *
 * @package Framework\Core\Logging
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case NOTICE = 'notice';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';
    case ALERT = 'alert';
    case EMERGENCY = 'emergency';

    /**
     * String'den log seviyesi oluşturur.
     */
    public static function fromString(string $level): self
    {
        return match (strtolower($level)) {
            'debug' => self::DEBUG,
            'info' => self::INFO,
            'notice' => self::NOTICE,
            'warning', 'warn' => self::WARNING,
            'error', 'err' => self::ERROR,
            'critical', 'crit' => self::CRITICAL,
            'alert' => self::ALERT,
            'emergency', 'emerg' => self::EMERGENCY,
            default => throw new \InvalidArgumentException("Invalid log level: $level")
        };
    }

    /**
     * Log seviyesinin önem sırasını döndürür.
     * Düşük değer daha önemli anlamına gelir.
     */
    public function toPriority(): int
    {
        return match($this) {
            self::DEBUG => 100,
            self::INFO => 200,
            self::NOTICE => 250,
            self::WARNING => 300,
            self::ERROR => 400,
            self::CRITICAL => 500,
            self::ALERT => 550,
            self::EMERGENCY => 600
        };
    }

    /**
     * Seviyenin diğer seviyeden büyük eşit olup olmadığını kontrol eder.
     */
    public function isHigherOrEqualTo(self $level): bool
    {
        return $this->toPriority() >= $level->toPriority();
    }
}