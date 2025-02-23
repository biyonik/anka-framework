<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Handlers;

use Throwable;
use Framework\Core\Http\Request\Factory\RequestFactory;
use Framework\Core\Http\Response\Factory\ResponseFactory;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Logging\Contracts\LoggerInterface;

/**
 * Console (CLI) istekleri için özel exception handler.
 *
 * Komut satırı uygulamalarında oluşan hataları işler ve
 * konsola uygun formatta çıktı üretir.
 *
 * @package Framework\Core\Exception
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ConsoleExceptionHandler extends AbstractExceptionHandler
{
    /**
     * ANSI renk kodları.
     */
    private const COLORS = [
        'red' => '0;31',
        'green' => '0;32',
        'yellow' => '0;33',
        'blue' => '0;34',
        'white' => '0;37',
        'bold_red' => '1;31'
    ];

    /**
     * Constructor.
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected RequestFactory $requestFactory,
        protected ResponseFactory $responseFactory,
        protected ConfigRepositoryInterface $config
    ) {
        parent::__construct($logger, $requestFactory, $responseFactory);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentRequest(): mixed
    {
        return null; // CLI'da request kullanılmıyor
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed $request, Throwable $e): mixed
    {
        if (!$this->config->getEnvironment()->is('production')) {
            return $this->renderDebug($e);
        }

        return $this->renderProduction($e);
    }

    /**
     * Development/debug modunda detaylı hata çıktısı oluşturur.
     */
    protected function renderDebug(Throwable $e): string
    {
        $output = PHP_EOL;

        // Exception tipi
        $output .= $this->colorize('Exception: ', 'white');
        $output .= $this->colorize(get_class($e), 'bold_red');
        $output .= PHP_EOL . PHP_EOL;

        // Hata mesajı
        $output .= $this->colorize('Message: ', 'white');
        $output .= $this->colorize($e->getMessage(), 'red');
        $output .= PHP_EOL;

        // Dosya ve satır
        $output .= $this->colorize('File: ', 'white');
        $output .= $e->getFile() . ':' . $e->getLine();
        $output .= PHP_EOL . PHP_EOL;

        // Stack trace
        $output .= $this->colorize('Stack trace:', 'white') . PHP_EOL;
        $output .= $this->formatTrace($e->getTraceAsString());
        $output .= PHP_EOL;

        // Previous exception
        if ($e->getPrevious()) {
            $output .= $this->colorize('Previous Exception:', 'white') . PHP_EOL;
            $output .= $this->colorize($e->getPrevious()->getMessage(), 'yellow');
            $output .= PHP_EOL;
        }

        return $output;
    }

    /**
     * Production modunda basit hata çıktısı oluşturur.
     */
    protected function renderProduction(Throwable $e): string
    {
        return PHP_EOL .
            $this->colorize('Error: ', 'red') .
            $e->getMessage() .
            PHP_EOL;
    }

    /**
     * Stack trace'i formatlar.
     */
    protected function formatTrace(string $trace): string
    {
        $lines = explode(PHP_EOL, $trace);
        $output = '';

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Satır numaralarını vurgula
            $line = preg_replace('/^#(\d+)/', $this->colorize('$0', 'blue'), $line);

            // Dosya yollarını vurgula
            $line = preg_replace('/(\/[^\s:]+)/', $this->colorize('$1', 'green'), $line);

            $output .= $line . PHP_EOL;
        }

        return $output;
    }

    /**
     * ANSI renkli metin oluşturur.
     */
    protected function colorize(string $text, string $color): string
    {
        // Renk desteği kapalıysa düz metin döndür
        if ($this->hasColorSupport() === false) {
            return $text;
        }

        $colorCode = self::COLORS[$color] ?? '0';
        return "\033[{$colorCode}m{$text}\033[0m";
    }

    /**
     * Terminal'in renk desteği olup olmadığını kontrol eder.
     */
    protected function hasColorSupport(): bool
    {
        // Windows için ANSICON veya ConEmu kontrolü
        if (DIRECTORY_SEPARATOR === '\\') {
            return getenv('ANSICON') !== false
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM') === 'xterm';
        }

        // UNIX benzeri sistemler için
        return stream_isatty(STDOUT);
    }
}