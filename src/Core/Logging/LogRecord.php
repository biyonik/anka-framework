<?php

declare(strict_types=1);

namespace Framework\Core\Logging;

/**
 * Tek bir log kaydını temsil eden değer nesnesi.
 *
 * @package Framework\Core\Logging
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
readonly class LogRecord
{
    /**
     * Log kaydı oluşturur.
     */
    public function __construct(
        public LogLevel           $level,
        public string             $message,
        public array              $context = [],
        public \DateTimeImmutable $datetime = new \DateTimeImmutable(),
        public ?string            $channel = null,
        public ?string            $requestId = null
    ) {}

    /**
     * Yeni bağlam bilgisiyle kaydın klonunu oluşturur.
     */
    public function withContext(array $context): self
    {
        return new self(
            $this->level,
            $this->message,
            array_merge($this->context, $context),
            $this->datetime,
            $this->channel,
            $this->requestId
        );
    }

    /**
     * Yeni kanal bilgisiyle kaydın klonunu oluşturur.
     */
    public function withChannel(string $channel): self
    {
        return new self(
            $this->level,
            $this->message,
            $this->context,
            $this->datetime,
            $channel,
            $this->requestId
        );
    }

    /**
     * Yeni request ID ile kaydın klonunu oluşturur.
     */
    public function withRequestId(string $requestId): self
    {
        return new self(
            $this->level,
            $this->message,
            $this->context,
            $this->datetime,
            $this->channel,
            $requestId
        );
    }
}