<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Handlers;

use Framework\Core\Logging\LogLevel;
use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını dosyaya yazar.
 *
 * @package Framework\Core\Logging
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class FileHandler extends AbstractHandler
{
    /**
     * @var string|null Açılan dosya için resource
     */
    protected mixed $fileHandle;

    /**
     * @var string Dosya yolu
     */
    protected string $filePath;

    /**
     * @var string|null Dosyanın açılma modu (w, a, vb.)
     */
    protected ?string $fileMode;

    /**
     * @var bool|null Dosyayı kilit altında yazmak için
     */
    protected ?bool $useLocking;

    /**
     * @param string $filePath Dosya yolu
     * @param string $fileMode Dosya açma modu
     * @param bool $useLocking Dosya yazma sırasında kilit kullan
     */
    public function __construct(
        string $filePath,
        string $fileMode = 'a',
        bool $useLocking = false,
        LogLevel $level = LogLevel::DEBUG
    ) {
        parent::__construct();

        $this->filePath = $filePath;
        $this->fileMode = $fileMode;
        $this->useLocking = $useLocking;
    }

    /**
     * Nesne yok edildiğinde dosyayı kapat.
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): bool
    {
        if (!$this->fileHandle) {
            $this->openFile();
        }

        $formatted = $this->formatter->format($record);

        if ($this->useLocking) {
            flock($this->fileHandle, LOCK_EX);
            $result = fwrite($this->fileHandle, $formatted);
            flock($this->fileHandle, LOCK_UN);
        } else {
            $result = fwrite($this->fileHandle, $formatted);
        }

        if ($result === false) {
            throw new \RuntimeException('Unable to write to log file: ' . $this->filePath);
        }

        return true;
    }

    /**
     * Log dosyasını açar.
     */
    protected function openFile(): void
    {
        $dir = dirname($this->filePath);

        if (!is_dir($dir)) {
            $status = mkdir($dir, 0777, true);
            if (!$status && !is_dir($dir)) {
                throw new \RuntimeException('Unable to create directory for log file: ' . $dir);
            }
        }

        $this->fileHandle = fopen($this->filePath, $this->fileMode);

        if (!$this->fileHandle) {
            throw new \RuntimeException('Unable to open log file: ' . $this->filePath);
        }
    }
}