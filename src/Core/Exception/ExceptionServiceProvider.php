<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

use Framework\Core\Application\ServiceProvider\AbstractServiceProvider;
use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Configuration\Providers\ConfigServiceProvider;
use Framework\Core\Exception\Contracts\ExceptionHandlerInterface;
use Framework\Core\Exception\Handlers\{
    GlobalExceptionHandler,
    HttpExceptionHandler,
    ConsoleExceptionHandler
};
use Framework\Core\Logging\LoggerServiceProvider;

/**
 * Exception handling sisteminin ServiceProvider'ı.
 *
 * Framework'e exception handler'ları kaydeder ve
 * ortama göre uygun handler'ı ayarlar.
 *
 * @package Framework\Core\Exception
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ExceptionServiceProvider extends AbstractServiceProvider
{
    /**
     * Exception handler sınıflarını register eder.
     */
    public function register(ApplicationInterface $app): void
    {
        $container = $app->getContainer();

        // Global handler'ı register et
        $container->singleton(GlobalExceptionHandler::class);

        // HTTP handler'ı register et
        $container->singleton(HttpExceptionHandler::class);

        // Console handler'ı register et
        $container->singleton(ConsoleExceptionHandler::class);

        // Ortama göre uygun handler'ı ExceptionHandlerInterface olarak bağla
        $container->singleton(ExceptionHandlerInterface::class, function($container) {
            if (PHP_SAPI === 'cli') {
                return $container->get(ConsoleExceptionHandler::class);
            }

            return $container->get(HttpExceptionHandler::class);
        });
    }

    /**
     * Provider'ı boot et.
     * @throws \ErrorException
     */
    public function boot(ApplicationInterface $app): void
    {
        $handler = $app->getContainer()->get(ExceptionHandlerInterface::class);

        // PHP'nin error handler'ını ayarla
        set_error_handler(static function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });

        // Exception handler'ı ayarla
        set_exception_handler(static function (\Throwable $e) use ($handler) {
            $handler->handle($e);
        });

        // Fatal error handler'ı ayarla
        register_shutdown_function(function () use ($handler) {
            $error = error_get_last();
            if ($error !== null && $this->isFatalError($error['type'])) {
                $handler->handle(new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                ));
            }
        });
    }

    /**
     * Fatal error tiplerini kontrol eder.
     */
    private function isFatalError(int $type): bool
    {
        return in_array($type, [
            E_ERROR,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_PARSE,
            E_RECOVERABLE_ERROR
        ], true);
    }

    /**
     * Provider'ın bağımlılıkları.
     */
    public function dependencies(): array
    {
        return [
            LoggerServiceProvider::class,
            ConfigServiceProvider::class
        ];
    }
}