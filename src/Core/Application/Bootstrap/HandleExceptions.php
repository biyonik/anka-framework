<?php

declare(strict_types=1);

namespace Framework\Core\Application\Bootstrap;

use Framework\Core\Application\Interfaces\ApplicationInterface;
use ErrorException;
use Throwable;

/**
 * Hataları ve istisnaları yöneten bootstrap sınıfı.
 * 
 * Bu sınıf, uygulama başlatılırken hata işleyicilerini kaydeder.
 * PHP hatalarını istisna olarak yakalar ve istisnalar için özel işleyiciler tanımlar.
 * 
 * @package Framework\Core\Application
 * @subpackage Bootstrap
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class HandleExceptions implements BootstrapInterface
{
    /**
     * Uygulama instance'ı.
     */
    protected ApplicationInterface $app;

    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationInterface $app): void
    {
        $this->app = $app;

        // Error handler'ı ayarla
        set_error_handler([$this, 'handleError']);

        // Exception handler'ı ayarla
        set_exception_handler([$this, 'handleException']);

        // Terminate handler'ı ayarla
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * PHP hatalarını yakalar ve istisna olarak fırlatır.
     * 
     * @param int $level Hata seviyesi
     * @param string $message Hata mesajı
     * @param string $file Hata oluşan dosya
     * @param int $line Hata oluşan satır
     * @return bool|void
     * @throws ErrorException
     */
    public function handleError(int $level, string $message, string $file, int $line)
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }

        return false;
    }

    /**
     * İstisnaları yakalar ve işler.
     * 
     * @param Throwable $e Yakalanan istisna
     * @return void
     */
    public function handleException(Throwable $e)
    {
        // Debug modunda detaylı istisna göster
        if ($this->app->isDebug()) {
            $this->renderExceptionWithWhoops($e);
            return;
        }

        // Production modunda basit hata göster
        $this->renderExceptionWithoutTrace($e);
    }

    /**
     * Fatal hatalar için shutdown handler.
     * 
     * @return void
     */
    public function handleShutdown()
    {
        $error = error_get_last();

        if (!is_null($error) && $this->isFatal($error['type'])) {
            $this->handleException(
                new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * Hata tipinin fatal olup olmadığını kontrol eder.
     * 
     * @param int $type Hata tipi
     * @return bool Fatal hataysa true
     */
    protected function isFatal(int $type): bool
    {
        return in_array($type, [
            E_ERROR,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_PARSE,
            E_RECOVERABLE_ERROR,
            E_DEPRECATED,
        ]);
    }

    /**
     * İstisnayı Whoops ile render eder (debug mode).
     * 
     * @param Throwable $e Render edilecek istisna
     * @return void
     */
    protected function renderExceptionWithWhoops(Throwable $e)
    {
        // Whoops varsa kullan
        if (class_exists(\Whoops\Run::class)) {
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->handleException($e);
            return;
        }

        // Yoksa varsayılan renderer'ı kullan
        $this->renderExceptionWithTrace($e);
    }

    /**
     * İstisnayı trace ile render eder.
     * 
     * @param Throwable $e Render edilecek istisna
     * @return void
     */
    protected function renderExceptionWithTrace(Throwable $e)
    {
        $class = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Error</title>
            <style>
                body { font-family: monospace; padding: 20px; }
                .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; }
                .trace { background: #eee; padding: 10px; margin-top: 10px; overflow: auto; }
                .file { color: #007bff; }
                .line { color: #dc3545; }
            </style>
        </head>
        <body>
            <h1>Error: {$class}</h1>
            <div class="error">
                <p><strong>Message:</strong> {$message}</p>
                <p><strong>File:</strong> <span class="file">{$file}</span> <strong>Line:</strong> <span class="line">{$line}</span></p>
            </div>
            <div class="trace">
                <pre>{$trace}</pre>
            </div>
        </body>
        </html>
        HTML;

        echo $html;
    }

    /**
     * İstisnayı trace olmadan render eder (production mode).
     * 
     * @param Throwable $e Render edilecek istisna
     * @return void
     */
    protected function renderExceptionWithoutTrace(Throwable $e)
    {
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Error</title>
            <style>
                body { font-family: sans-serif; padding: 40px; text-align: center; }
                .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 4px; max-width: 500px; margin: 0 auto; }
            </style>
        </head>
        <body>
            <div class="error">
                <h2>Server Error</h2>
                <p>Sorry, something went wrong on our servers.</p>
            </div>
        </body>
        </html>
        HTML;

        echo $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        // En yüksek öncelikle çalışmalı
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldRun(ApplicationInterface $app): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function runsInEnvironment(string $environment): bool
    {
        return true;
    }
}