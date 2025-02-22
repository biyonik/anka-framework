<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Formatters;

use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını tek satırlık metin olarak biçimlendirir.
 *
 * @package Framework\Core\Logging
 * @subpackage Formatters
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class LineFormatter extends AbstractFormatter
{
    /**
     * @param string $format Satır formatı
     * @param string $dateFormat Tarih formatı
     * @param bool $includeContext Bağlam bilgisini ekle
     */
    public function __construct(
        private readonly string $format = "[%datetime%] %channel%.%level_name%: %message% %context%",
        private readonly string $dateFormat = 'Y-m-d H:i:s',
        private readonly bool $includeContext = true
    ) {}

    /**
     * {@inheritdoc}
     */
    public function format(LogRecord $record): string
    {
        $vars = [
            '%datetime%' => $record->datetime->format($this->dateFormat),
            '%channel%' => $record->channel ?? 'app',
            '%level_name%' => strtoupper($record->level->value),
            '%level%' => $record->level->value,
            '%message%' => $this->interpolate($record->message, $record->context),
            '%context%' => $this->includeContext && !empty($record->context) ? $this->formatContext($record->context) : '',
        ];

        if ($record->requestId) {
            $vars['%request_id%'] = $record->requestId;
        }

        return strtr($this->format, $vars) . PHP_EOL;
    }

    /**
     * Bağlam bilgisini okunabilir bir metne dönüştürür.
     * @throws \JsonException
     */
    private function formatContext(array $context): string
    {
        $json = json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $json !== false ? $json : '';
    }
}