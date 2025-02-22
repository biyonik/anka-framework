<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Handlers;

use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını tarih bazlı döndüren dosyalara yazar.
 *
 * @package Framework\Core\Logging
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RotatingFileHandler extends FileHandler
{
    /**
     * @var int Tutulacak maksimum dosya sayısı
     */
    protected int $maxFiles;

    /**
     * @var string Dosya adında kullanılacak tarih formatı
     */
    protected string $dateFormat;

    /**
     * @var string|null En son kullanılan dosya adı
     */
    protected ?string $filename = null;

    /**
     * @var string|null Mevcut dönem (tarih) değeri
     */
    protected ?string $currentPeriod = null;

    /**
     * @param string $filePath Dosya yolu (tarih eklenmeden önce)
     * @param int $maxFiles Saklanacak maksimum dosya sayısı
     * @param string $dateFormat Dosya adında kullanılacak tarih formatı
     */
    public function __construct(
        string $filePath,
        int $maxFiles = 0,
        string $dateFormat = 'Y-m-d'
    ) {
        parent::__construct($filePath, 'a', true);

        $this->maxFiles = $maxFiles;
        $this->dateFormat = $dateFormat;
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): bool
    {
        $period = $record->datetime->format($this->dateFormat);

        if ($this->currentPeriod !== $period) {
            $this->currentPeriod = $period;
            $this->closeFile();
            $this->rotateFiles();
            $this->setFilename();
        }

        return parent::write($record);
    }

    /**
     * Dosyayı kapatır.
     */
    protected function closeFile(): void
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    /**
     * Dosyaları döndürür.
     */
    protected function rotateFiles(): void
    {
        if ($this->maxFiles === 0) {
            return;
        }

        $fileInfos = [];
        $finder = new \FilesystemIterator(dirname($this->filePath));

        $baseFilename = basename($this->filePath);

        foreach ($finder as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filename = $file->getFilename();

            if (strpos($filename, $baseFilename) === 0) {
                $fileInfos[] = [
                    'file' => $file,
                    'timestamp' => $file->getMTime(),
                ];
            }
        }

        // En eski dosyayı başa getir
        usort($fileInfos, function($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        // Fazla dosyaları sil
        foreach (array_slice($fileInfos, 0, count($fileInfos) - $this->maxFiles + 1) as $fileInfo) {
            @unlink($fileInfo['file']->getPathname());
        }
    }

    /**
     * Dönen dosyanın adını ayarlar.
     */
    protected function setFilename(): void
    {
        $filenamePattern = $this->filePath . (false === strpos($this->filePath, '{date}') ? '-{date}' : '');
        $this->filename = str_replace('{date}', $this->currentPeriod, $filenamePattern);
        $this->filePath = $this->filename;
    }
}