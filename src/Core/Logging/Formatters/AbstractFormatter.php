<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Formatters;

use Framework\Core\Logging\Contracts\LogFormatterInterface;

/**
 * Temel formatter fonksiyonalitesini sağlayan soyut sınıf.
 *
 * @package Framework\Core\Logging
 * @subpackage Formatters
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractFormatter implements LogFormatterInterface
{
    /**
     * Log kaydının mesajını biçimlendirir, bağlam değişkenlerini yerleştirir.
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam değişkenleri
     * @return string Biçimlendirilmiş mesaj
     */
    protected function interpolate(string $message, array $context = []): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records): array
    {
        $formatted = [];

        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }

        return $formatted;
    }
}