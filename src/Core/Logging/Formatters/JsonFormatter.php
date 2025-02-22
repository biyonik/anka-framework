<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Formatters;

use Framework\Core\Logging\LogRecord;

/**
 * Log kayıtlarını JSON formatında biçimlendirir.
 *
 * @package Framework\Core\Logging
 * @subpackage Formatters
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class JsonFormatter extends AbstractFormatter
{
    /**
     * @param bool $prettyPrint JSON çıktısını güzel biçimlendir
     * @param bool $appendNewline Her kayıt sonuna yeni satır ekle
     */
    public function __construct(
        private readonly bool $prettyPrint = false,
        private readonly bool $appendNewline = true
    ) {}

    /**
     * {@inheritdoc}
     * @throws \JsonException
     */
    public function format(LogRecord $record): string
    {
        $data = [
            'timestamp' => $record->datetime->format('c'),
            'level' => $record->level->value,
            'message' => $this->interpolate($record->message, $record->context),
            'channel' => $record->channel ?? 'app',
        ];

        if ($record->requestId) {
            $data['request_id'] = $record->requestId;
        }

        if (!empty($record->context)) {
            $data['context'] = $this->normalizeContext($record->context);
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if ($this->prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($data, JSON_THROW_ON_ERROR | $flags);

        if ($this->appendNewline) {
            $json .= PHP_EOL;
        }

        return $json;
    }

    /**
     * Bağlam verilerini JSON için normalleştirir.
     */
    private function normalizeContext(array $context): array
    {

        return array_map(function ($value) {
            return $this->normalizeValue($value);
        }, $context);
    }

    /**
     * Tek bir değeri JSON için normalleştirir.
     */
    private function normalizeValue($value)
    {
        if (is_null($value) || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }

        if ($value instanceof \Throwable) {
            return [
                'class' => get_class($value),
                'message' => $value->getMessage(),
                'code' => $value->getCode(),
                'file' => $value->getFile() . ':' . $value->getLine()
            ];
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            if (method_exists($value, 'toArray')) {
                return $value->toArray();
            }

            if (method_exists($value, 'jsonSerialize')) {
                return $value->jsonSerialize();
            }

            return sprintf('[object %s]', get_class($value));
        }

        if (is_array($value)) {
            return array_map([$this, 'normalizeValue'], $value);
        }

        if (is_resource($value)) {
            return sprintf('[resource %s]', get_resource_type($value));
        }

        return '[unknown]';
    }
}