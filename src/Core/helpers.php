<?php

use Framework\Core\Application\Application;
use Framework\Core\Logging\Contracts\LoggerInterface;

if (!function_exists('logger')) {
    /**
     * Logger instance'ını veya belirli bir kanalın logger'ını döndürür.
     *
     * @param string|null $channel Kanal adı
     * @param array $context Bağlam bilgisi
     * @return LoggerInterface
     */
    function logger(?string $channel = null, array $context = []): LoggerInterface
    {
        $app = Application::getInstance();
        $logger = $app->getContainer()->get('logger');

        if ($channel) {
            $logger = $logger->channel($channel);
        }

        if (!empty($context)) {
            $logger = $logger->withContext($context);
        }

        return $logger;
    }
}