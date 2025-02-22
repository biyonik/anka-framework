<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Processors;

use Framework\Core\Logging\Contracts\LogProcessorInterface;
use Framework\Core\Logging\LogRecord;

/**
 * Log kaydına hafıza kullanım bilgisini ekler.
 *
 * @package Framework\Core\Logging
 * @subpackage Processors
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class MemoryUsageProcessor implements LogProcessorInterface
{
    /**
     * @var bool Gerçek hafıza kullanımını raporla
     */
    private bool $realUsage;

    /**
     * @var bool İnsan dostu formatla
     */
    private bool $humanFriendly;

    /**
     * @param bool $realUsage Gerçek hafıza kullanımını raporla
     * @param bool $humanFriendly İnsan dostu formatla
     */
    public function __construct(
        bool $realUsage = true,
        bool $humanFriendly = true
    ) {
        $this->realUsage = $realUsage;
        $this->humanFriendly = $humanFriendly;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $bytes = memory_get_usage($this->realUsage);
        $size = $this->humanFriendly ? $this->formatBytes($bytes) : $bytes;

        $peakBytes = memory_get_peak_usage($this->realUsage);
        $peakSize = $this->humanFriendly ? $this->formatBytes($peakBytes) : $peakBytes;

        $context = $record->context;
        $context['memory_usage'] = $size;
        $context['memory_peak'] = $peakSize;

        return $record->withContext($context);
    }

    /**
     * Byte değerini insan dostu formata çevirir (KB, MB, GB).
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}