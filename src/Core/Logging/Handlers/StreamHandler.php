<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Handlers;

use Framework\Core\Logging\LogLevel;
use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını bir stream'e yazar (stdout, stderr veya herhangi bir stream URL).
 *
 * @package Framework\Core\Logging
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class StreamHandler extends AbstractHandler
{
    /**
     * @var resource|null Stream handle
     */
    protected $stream;

    /**
     * @var string|null Stream URL
     */
    protected ?string $url = null;

    /**
     * @param string|resource $stream Stream URL veya handle
     * @param bool $useLocking Dosya yazma sırasında kilit kullan
     */
    public function __construct(
        mixed $stream,
        protected bool $useLocking = false,
        LogLevel $level = LogLevel::DEBUG
    ) {
        parent::__construct();

        if (is_resource($stream)) {
            $this->stream = $stream;
        } else {
            $this->url = $stream;
        }
    }

    /**
     * Nesne yok edildiğinde stream'i kapat.
     */
    public function __destruct()
    {
        if ($this->stream && $this->url && $this->url !== 'php://stdout' && $this->url !== 'php://stderr') {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): bool
    {
        if (!$this->stream) {
            $this->openStream();
        }

        $formatted = $this->formatter->format($record);

        if ($this->useLocking) {
            flock($this->stream, LOCK_EX);
            $result = fwrite($this->stream, $formatted);
            flock($this->stream, LOCK_UN);
        } else {
            $result = fwrite($this->stream, $formatted);
        }

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return true;
    }

    /**
     * Stream'i açar.
     */
    protected function openStream(): void
    {
        if ($this->url === 'php://stdout') {
            $this->stream = STDOUT;
        } elseif ($this->url === 'php://stderr') {
            $this->stream = STDERR;
        } else {
            $this->stream = @fopen($this->url, 'ab');
            if (!$this->stream) {
                throw new \RuntimeException('Unable to open stream: ' . $this->url);
            }
        }
    }
}