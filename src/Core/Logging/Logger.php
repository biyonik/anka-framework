<?php

declare(strict_types=1);

namespace Framework\Core\Logging;

use Framework\Core\Logging\Contracts\LogFormatterInterface;
use Framework\Core\Logging\Contracts\LoggerInterface;
use Framework\Core\Logging\Contracts\LogHandlerInterface;
use Framework\Core\Logging\Contracts\LogProcessorInterface;

/**
 * Ana logger sınıfı.
 *
 * @package Framework\Core\Logging
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Logger implements LoggerInterface
{
    /**
     * @var LogHandlerInterface[] Handler listesi
     */
    protected array $handlers = [];

    /**
     * @var LogProcessorInterface[] Processor listesi
     */
    protected array $processors = [];

    /**
     * @var string Logger kanalı
     */
    protected string $channel;

    /**
     * @var array Tüm loglar için eklenecek bağlam bilgisi
     */
    protected array $contextualData = [];

    /**
     * @var string|null Mevcut request ID
     */
    protected ?string $requestId = null;

    /**
     * @param string $channel Logger kanalı
     * @param array $handlers Handler listesi
     * @param array $processors Processor listesi
     * @param array $contextualData Tüm loglar için eklenecek bağlam bilgisi
     */
    public function __construct(
        string $channel = 'app',
        array $handlers = [],
        array $processors = [],
        array $contextualData = []
    ) {
        $this->channel = $channel;
        $this->handlers = $handlers;
        $this->processors = $processors;
        $this->contextualData = $contextualData;
    }

    /**
     * Kayıtlı handler'ları döndürür.
     *
     * @return array<LogHandlerInterface>
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Kayıtlı processor'ları döndürür.
     *
     * @return array<LogProcessorInterface>
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    /**
     * Logger'a yeni bir handler ekler.
     */
    public function addHandler(LogHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    /**
     * Logger'a yeni bir processor ekler.
     */
    public function addProcessor(LogProcessorInterface $processor): self
    {
        $this->processors[] = $processor;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withContext(array $context): self
    {
        $new = clone $this;
        $new->contextualData = array_merge($this->contextualData, $context);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFormatter(LogFormatterInterface $formatter): self
    {
        $new = clone $this;

        foreach ($new->handlers as $handler) {
            $handler->setFormatter($formatter);
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function channel(string $channel): self
    {
        $new = clone $this;
        $new->channel = $channel;
        return $new;
    }

    /**
     * Request ID ayarlar, tüm loglarda kullanılır.
     */
    public function withRequestId(string $requestId): self
    {
        $new = clone $this;
        $new->requestId = $requestId;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        // String değeri LogLevel enum değerine çevir
        if (is_string($level)) {
            $level = LogLevel::fromString($level);
        }

        // Mesaj bir obje ise string'e çevir
        if (is_object($message) && method_exists($message, '__toString')) {
            $message = (string) $message;
        } elseif (!is_scalar($message)) {
            $message = $this->stringify($message);
        }

        // Bağlam bilgisini birleştir
        $context = array_merge($this->contextualData, $context);

        // Log kaydı oluştur
        $record = new LogRecord(
            level: $level,
            message: $message,
            context: $context,
            channel: $this->channel,
            requestId: $this->requestId
        );

        // Processors'ları uygula
        $record = $this->processRecord($record);

        // Handlers'lara gönder
        $this->handleRecord($record);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($level): bool
    {
        if (is_string($level)) {
            $level = LogLevel::fromString($level);
        }

        foreach ($this->handlers as $handler) {
            if ($handler->isHandling(new LogRecord($level, ''))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Processorsları uygulamak için kayıttan geçirir.
     */
    protected function processRecord(LogRecord $record): LogRecord
    {
        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }

        return $record;
    }

    /**
     * Kayıtı handler'lara gönderir.
     */
    protected function handleRecord(LogRecord $record): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->isHandling($record)) {
                $handler->handle($record);
            }
        }
    }

    /**
     * Herhangi bir değeri düz metne çevirir.
     */
    protected function stringify($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_resource($value)) {
            return '[resource]';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}