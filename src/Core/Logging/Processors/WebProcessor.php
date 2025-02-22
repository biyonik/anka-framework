<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Processors;

use Framework\Core\Logging\Contracts\LogProcessorInterface;
use Framework\Core\Logging\LogRecord;

/**
 * Log kaydına web bilgilerini (URL, IP, vb.) ekler.
 *
 * @package Framework\Core\Logging
 * @subpackage Processors
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class WebProcessor implements LogProcessorInterface
{
    /**
     * @var array Eklenecek web bilgileri
     */
    protected array $extraFields;

    /**
     * @param array $extraFields Eklenecek ek alanlar
     */
    public function __construct(array $extraFields = null)
    {
        if ($extraFields === null) {
            $this->extraFields = [
                'url' => true,
                'ip' => true,
                'http_method' => true,
                'server' => true,
                'referrer' => true,
                'user_agent' => true,
            ];
        } else {
            $this->extraFields = $extraFields;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return $record;
        }

        $context = $record->context;
        $server = $_SERVER;

        if ($this->extraFields['url'] ?? false) {
            $context['url'] = $server['REQUEST_URI'] ?? '';
        }

        if ($this->extraFields['ip'] ?? false) {
            $context['ip'] = $server['REMOTE_ADDR'] ?? '';
        }

        if ($this->extraFields['http_method'] ?? false) {
            $context['http_method'] = $server['REQUEST_METHOD'] ?? '';
        }

        if ($this->extraFields['referrer'] ?? false) {
            $context['referrer'] = $server['HTTP_REFERER'] ?? '';
        }

        if ($this->extraFields['user_agent'] ?? false) {
            $context['user_agent'] = $server['HTTP_USER_AGENT'] ?? '';
        }

        if ($this->extraFields['server'] ?? false) {
            $context['server'] = $server['SERVER_NAME'] ?? '';
        }

        return $record->withContext($context);
    }
}