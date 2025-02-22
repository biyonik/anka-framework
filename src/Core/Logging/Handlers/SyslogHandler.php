<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Handlers;

use Framework\Core\Logging\LogLevel;
use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını system log'a (syslog) yazar.
 *
 * @package Framework\Core\Logging
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class SyslogHandler extends AbstractHandler
{
    /**
     * Syslog loglama facility değerleri.
     */
    public const LOG_AUTH     = LOG_AUTH;
    public const LOG_AUTHPRIV = LOG_AUTHPRIV;
    public const LOG_CRON     = LOG_CRON;
    public const LOG_DAEMON   = LOG_DAEMON;
    public const LOG_KERN     = LOG_KERN;
    public const LOG_LOCAL0   = LOG_LOCAL0;
    public const LOG_LOCAL1   = LOG_LOCAL1;
    public const LOG_LOCAL2   = LOG_LOCAL2;
    public const LOG_LOCAL3   = LOG_LOCAL3;
    public const LOG_LOCAL4   = LOG_LOCAL4;
    public const LOG_LOCAL5   = LOG_LOCAL5;
    public const LOG_LOCAL6   = LOG_LOCAL6;
    public const LOG_LOCAL7   = LOG_LOCAL7;
    public const LOG_LPR      = LOG_LPR;
    public const LOG_MAIL     = LOG_MAIL;
    public const LOG_NEWS     = LOG_NEWS;
    public const LOG_SYSLOG   = LOG_SYSLOG;
    public const LOG_USER     = LOG_USER;
    public const LOG_UUCP     = LOG_UUCP;

    /**
     * @var string Uygulama adı (identity)
     */
    protected string $ident;

    /**
     * @var int Syslog facility değeri
     */
    protected int $facility;

    /**
     * @var int|null Syslog options
     */
    protected ?int $logopts;

    /**
     * @var bool Syslog'un açık olup olmadığı
     */
    protected bool $opened = false;

    /**
     * @param string $ident Uygulama adı (identity)
     * @param int $facility Syslog facility değeri
     * @param int|null $logopts Syslog options
     */
    public function __construct(
        string $ident,
        int $facility = self::LOG_USER,
        ?int $logopts = null,
        LogLevel $level = LogLevel::DEBUG
    ) {
        parent::__construct();

        $this->ident = $ident;
        $this->facility = $facility;
        $this->logopts = $logopts;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): bool
    {
        if (!$this->opened) {
            $this->openSyslog();
        }

        // Context içindeki verileri düz metne çevir, çünkü syslog sadece düz metni destekler
        syslog(
            $this->toSyslogPriority($record->level),
            $this->formatter->format($record)
        );

        return true;
    }

    /**
     * Logger seviyesini syslog öncelik değerine dönüştürür.
     */
    protected function toSyslogPriority(LogLevel $level): int
    {
        return match($level) {
            LogLevel::DEBUG     => LOG_DEBUG,
            LogLevel::INFO      => LOG_INFO,
            LogLevel::NOTICE    => LOG_NOTICE,
            LogLevel::WARNING   => LOG_WARNING,
            LogLevel::ERROR     => LOG_ERR,
            LogLevel::CRITICAL  => LOG_CRIT,
            LogLevel::ALERT     => LOG_ALERT,
            LogLevel::EMERGENCY => LOG_EMERG,
        };
    }

    /**
     * Syslog bağlantısını açar.
     */
    protected function openSyslog(): void
    {
        if ($this->logopts !== null) {
            openlog($this->ident, $this->logopts, $this->facility);
        } else {
            openlog($this->ident, LOG_PID, $this->facility);
        }

        $this->opened = true;
    }

    /**
     * Nesne yok edildiğinde syslog bağlantısını kapatır.
     */
    public function __destruct()
    {
        if ($this->opened) {
            closelog();
        }
    }
}