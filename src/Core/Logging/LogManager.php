<?php

declare(strict_types=1);

namespace Framework\Core\Logging;

use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Container\Container;
use Framework\Core\Logging\Contracts\LoggerInterface;
use Framework\Core\Logging\Contracts\LogHandlerInterface;
use Framework\Core\Logging\Contracts\LogFormatterInterface;
use Framework\Core\Logging\Contracts\LogProcessorInterface;
use Framework\Core\Logging\Formatters\JsonFormatter;
use Framework\Core\Logging\Formatters\LineFormatter;
use Framework\Core\Logging\Handlers\FileHandler;
use Framework\Core\Logging\Handlers\RotatingFileHandler;
use Framework\Core\Logging\Handlers\StreamHandler;
use Framework\Core\Logging\Handlers\SyslogHandler;
use Framework\Core\Logging\Processors\IntrospectionProcessor;
use Framework\Core\Logging\Processors\MemoryUsageProcessor;
use Framework\Core\Logging\Processors\WebProcessor;

/**
 * Logger yönetimi için merkezi sınıf.
 *
 * @package Framework\Core\Logging
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class LogManager
{
    /**
     * @var array<string, LoggerInterface> Oluşturulmuş logger'lar
     */
    protected array $loggers = [];

    /**
     * @param Container $container Container objesi
     * @param ConfigRepositoryInterface $config Konfigürasyon objesi
     */
    public function __construct(
        protected Container                 $container,
        protected ConfigRepositoryInterface $config
    )
    {
    }

    /**
     * Belirli bir kanaldaki logger'ı döndürür.
     */
    public function channel(?string $channel = null): LoggerInterface
    {
        $channel = $channel ?? $this->getDefaultChannel();

        return $this->loggers[$channel] ??= $this->resolve($channel);
    }

    /**
     * Varsayılan kanalı döndürür.
     */
    protected function getDefaultChannel(): string
    {
        return $this->config->get('logging.default', 'single');
    }

    /**
     * Belirli bir kanalı çözümler.
     */
    protected function resolve(string $channel): LoggerInterface
    {
        $config = $this->getChannelConfig($channel);
        $driver = $config['driver'] ?? 'single';

        return match ($driver) {
            'stack' => $this->createStackLogger($channel, $config),
            'single' => $this->createSingleLogger($channel, $config),
            'daily' => $this->createDailyLogger($channel, $config),
            'syslog' => $this->createSyslogLogger($channel, $config),
            'stream' => $this->createStreamLogger($channel, $config),
            default => $this->createCustomLogger($channel, $config)
        };
    }

    /**
     * Kanal konfigürasyonunu döndürür.
     */
    protected function getChannelConfig(string $channel): array
    {
        $config = $this->config->get("logging.channels.{$channel}");

        if (!$config) {
            throw new \InvalidArgumentException("Log channel [{$channel}] is not configured.");
        }

        return $config;
    }

    /**
     * Stack logger oluşturur.
     */
    protected function createStackLogger(string $channel, array $config): LoggerInterface
    {
        if (!isset($config['channels']) || empty($config['channels'])) {
            throw new \InvalidArgumentException('Stack channels not configured.');
        }

        $handlers = [];
        $processors = [];

        foreach ($config['channels'] as $stackChannel) {
            $logger = $this->channel($stackChannel);

            // Logger'dan handler ve processor'ları al
            if ($logger instanceof Logger) {
                $handlers = [...$handlers, ...$logger->getHandlers()];
                $processors = [...$processors, ...$logger->getProcessors()];
            }
        }

        return new Logger(
            $channel,
            $handlers,
            $processors,
            $config['extra'] ?? []
        );
    }

    /**
     * Tek dosya logger'ı oluşturur.
     */
    protected function createSingleLogger(string $channel, array $config): LoggerInterface
    {
        return new Logger(
            $channel,
            [$this->createFileHandler($config)],
            $this->createProcessors($config),
            $config['extra'] ?? []
        );
    }

    /**
     * Dönen dosya logger'ı oluşturur.
     */
    protected function createDailyLogger(string $channel, array $config): LoggerInterface
    {
        return new Logger(
            $channel,
            [$this->createRotatingFileHandler($config)],
            $this->createProcessors($config),
            $config['extra'] ?? []
        );
    }

    /**
     * Stream logger'ı oluşturur.
     */
    protected function createStreamLogger(string $channel, array $config): LoggerInterface
    {
        return new Logger(
            $channel,
            [$this->createStreamHandler($config)],
            $this->createProcessors($config),
            $config['extra'] ?? []
        );
    }

    /**
     * Syslog logger'ı oluşturur.
     */
    protected function createSyslogLogger(string $channel, array $config): LoggerInterface
    {
        return new Logger(
            $channel,
            [$this->createSyslogHandler($config)],
            $this->createProcessors($config),
            $config['extra'] ?? []
        );
    }

    /**
     * Özel logger oluşturur.
     */
    protected function createCustomLogger(string $channel, array $config): LoggerInterface
    {
        $factory = $config['via'] ?? null;

        if (!$factory || !class_exists($factory)) {
            throw new \InvalidArgumentException("Custom logger [{$channel}] requires a valid factory class.");
        }

        return $this->container->get($factory)->createLogger($config);
    }

    /**
     * File handler oluşturur.
     */
    protected function createFileHandler(array $config): LogHandlerInterface
    {
        $level = isset($config['level'])
            ? LogLevel::fromString($config['level'])
            : LogLevel::DEBUG;

        $handler = new FileHandler(
            $this->getLogPath($config),
            'a',
            $config['locking'] ?? false,
            $level
        );

        $handler->setFormatter($this->createFormatter($config));

        return $handler;
    }

    /**
     * Rotating file handler oluşturur.
     */
    protected function createRotatingFileHandler(array $config): LogHandlerInterface
    {
        $level = isset($config['level'])
            ? LogLevel::fromString($config['level'])
            : LogLevel::DEBUG;

        $handler = new RotatingFileHandler(
            $this->getLogPath($config),
            $config['days'] ?? 7,
            $config['keep_files'] ?? 30,
            $level
        );

        $handler->setFormatter($this->createFormatter($config));

        return $handler;
    }

    /**
     * Stream handler oluşturur.
     */
    protected function createStreamHandler(array $config): LogHandlerInterface
    {
        $level = isset($config['level'])
            ? LogLevel::fromString($config['level'])
            : LogLevel::DEBUG;

        $handler = new StreamHandler(
            $config['url'] ?? 'php://stderr',
            $config['locking'] ?? false,
            $level
        );

        $handler->setFormatter($this->createFormatter($config));

        return $handler;
    }

    /**
     * Syslog handler oluşturur.
     */
    protected function createSyslogHandler(array $config): LogHandlerInterface
    {
        $level = isset($config['level'])
            ? LogLevel::fromString($config['level'])
            : LogLevel::DEBUG;

        $handler = new SyslogHandler(
            $config['ident'] ?? $this->config->get('app.name', 'app'),
            $config['facility'] ?? LOG_USER,
            null,
            $level
        );

        $handler->setFormatter($this->createFormatter($config));

        return $handler;
    }

    /**
     * Log dosyası yolunu döndürür.
     */
    protected function getLogPath(array $config): string
    {
        $path = $config['path'] ?? null;

        if (!$path) {
            return $this->config->get('paths.storage') . '/logs/app.log';
        }

        return $path;
    }

    /**
     * Formatter oluşturur.
     */
    protected function createFormatter(array $config): LogFormatterInterface
    {
        $type = $config['formatter'] ?? 'line';

        return match ($type) {
            'json' => new JsonFormatter(
                $config['pretty'] ?? false,
                $config['newline'] ?? true
            ),
            default => new LineFormatter(
                $config['format'] ?? null,
                $config['date_format'] ?? 'Y-m-d H:i:s',
                $config['include_context'] ?? true
            )
        };
    }

    /**
     * Processor'ları oluşturur.
     *
     * @return array<LogProcessorInterface>
     */
    protected function createProcessors(array $config): array
    {
        $processors = [];

        foreach ($config['processors'] ?? [] as $processor => $options) {
            if ($options === false) {
                continue;
            }

            $processors[] = match ($processor) {
                'introspection' => new IntrospectionProcessor(
                    $options['level'] ?? 0,
                    $options['skip_classes'] ?? ['Core\\Logging\\']
                ),
                'web' => new WebProcessor(
                    is_array($options) ? $options : null
                ),
                'memory' => new MemoryUsageProcessor(
                    $options['real_usage'] ?? true,
                    $options['human_friendly'] ?? true
                ),
                default => $this->createCustomProcessor($processor, $options)
            };
        }

        return $processors;
    }

    /**
     * Özel processor oluşturur.
     */
    protected function createCustomProcessor(string $processor, mixed $options): LogProcessorInterface
    {
        if (class_exists($processor)) {
            // Önce bind edip sonra get ile alıyoruz
            $this->container->bind($processor, fn() => new $processor($options));
            return $this->container->get($processor);
        }

        throw new \InvalidArgumentException("Processor [{$processor}] not found.");
    }
}