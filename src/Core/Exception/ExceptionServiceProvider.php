<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

use Framework\Core\Application\ServiceProvider\AbstractServiceProvider;
use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Exception\Contracts\ExceptionHandlerInterface;
use Framework\Core\Exception\Handlers\{
    GlobalExceptionHandler,
    HttpExceptionHandler,
    ConsoleExceptionHandler
};
use Framework\Core\Http\Request\Factory\RequestFactory;
use Framework\Core\Http\Response\Factory\ResponseFactory;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Logging\Contracts\LoggerInterface;
use Framework\Core\View\ViewFactory;
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
     * {@inheritdoc}
     */
    public function register(ApplicationInterface $app): void
    {
        $container = $app->getContainer();

        // Handler bağımlılıklarını register et
        $this->registerHandlerDependencies($container);

        // Her bir handler'ı kaydedelim
        $this->registerHandlers($container);

        // Ortama göre doğru handler'ı ExceptionHandlerInterface ile bind et
        $container->singleton(ExceptionHandlerInterface::class, function($container) {
            if (PHP_SAPI === 'cli') {
                return $container->get(ConsoleExceptionHandler::class);
            }

            return $container->get(HttpExceptionHandler::class);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(ApplicationInterface $app): void
    {
        $handler = $app->getContainer()->get(ExceptionHandlerInterface::class);

        // PHP'nin error handler'ını ayarla
        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });

        // Exception handler'ı ayarla
        set_exception_handler(function (\Throwable $e) use ($handler) {
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
     * Handler'lar için gerekli bağımlılıkları register eder.
     */
    protected function registerHandlerDependencies($container): void
    {
        if (!$container->has(RequestFactory::class)) {
            $container->singleton(RequestFactory::class);
        }

        if (!$container->has(ResponseFactory::class)) {
            $container->singleton(ResponseFactory::class);
        }
    }

    /**
     * Exception handler'ları register eder.
     */
    protected function registerHandlers($container): void
    {
        // Global Handler
        $container->singleton(GlobalExceptionHandler::class, function($container) {
            return new GlobalExceptionHandler(
                $container->get(LoggerInterface::class),
                $container->get(RequestFactory::class),
                $container->get(ResponseFactory::class),
                $container->get(ConfigRepositoryInterface::class)
            );
        });

        // HTTP Handler
        $container->singleton(HttpExceptionHandler::class, function($container) {
            return new HttpExceptionHandler(
                $container->get(LoggerInterface::class),
                $container->get(RequestFactory::class),
                $container->get(ResponseFactory::class),
                $container->get(ConfigRepositoryInterface::class),
                $container->get(ViewFactory::class)
            );
        });

        // Console Handler
        $container->singleton(ConsoleExceptionHandler::class, function($container) {
            return new ConsoleExceptionHandler(
                $container->get(LoggerInterface::class),
                $container->get(RequestFactory::class),
                $container->get(ResponseFactory::class),
                $container->get(ConfigRepositoryInterface::class)
            );
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
     * {@inheritdoc}
     */
    public function dependencies(): array
    {
        return [
            LoggerServiceProvider::class,
            'Framework\Core\Configuration\ConfigServiceProvider',
            'Framework\Core\View\ViewServiceProvider'
        ];
    }
}