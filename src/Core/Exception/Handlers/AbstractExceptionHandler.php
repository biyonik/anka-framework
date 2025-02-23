<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Handlers;

use Throwable;
use Framework\Core\Exception\Contracts\ExceptionHandlerInterface;
use Framework\Core\Exception\Contracts\ReportableExceptionInterface;
use Framework\Core\Http\Response\Factory\ResponseFactory;
use Framework\Core\Http\Request\Factory\RequestFactory;
use Framework\Core\Logging\Contracts\LoggerInterface;

/**
 * Exception handler'lar için temel soyut sınıf.
 *
 * Tüm exception handler'lar için ortak davranışları ve
 * temel exception işleme mantığını içerir.
 *
 * @package Framework\Core\Exception
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * Raporlanmayacak exception tipleri.
     *
     * @var array<class-string>
     */
    protected array $dontReport = [];

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger Logger instance'ı
     * @param RequestFactory $requestFactory Request Factory instance'ı
     * @param ResponseFactory $responseFactory Response Factory instance'ı
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected RequestFactory $requestFactory,
        protected ResponseFactory $responseFactory
    ) {}

    /**
     * {@inheritdoc}
     */
    public function handle(Throwable $e): mixed
    {
        try {
            $this->report($e);

            return $this->render($this->getCurrentRequest(), $e);
        } catch (Throwable $e) {
            // Exception handler'da hata olursa emergency log
            $this->logger->emergency(
                'Exception handling failed: ' . $e->getMessage(),
                ['exception' => $e]
            );

            return $this->renderEmergencyResponse($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function report(Throwable $e): void
    {
        if (!$this->shouldReport($e)) {
            return;
        }

        if ($e instanceof ReportableExceptionInterface) {
            $e->report();
            return;
        }

        $this->logger->error($e->getMessage(), [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldReport(Throwable $e): bool
    {
        if ($e instanceof ReportableExceptionInterface) {
            return $e->shouldReport();
        }

        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }

    /**
     * Acil durum yanıtı oluşturur.
     *
     * Exception handler'da hata olduğunda çağrılır.
     *
     * @param Throwable $e Exception
     * @return mixed Acil durum yanıtı
     */
    protected function renderEmergencyResponse(Throwable $e): mixed
    {
        if (PHP_SAPI === 'cli') {
            return "Fatal Error: " . $e->getMessage() . PHP_EOL;
        }

        return $this->responseFactory->createResponse(
            500,
            'Internal Server Error',
            ['Content-Type' => 'text/html']
        );
    }

    /**
     * Mevcut request nesnesini döndürür.
     *
     * Alt sınıflar override etmelidir.
     *
     * @return mixed Request nesnesi
     */
    abstract protected function getCurrentRequest(): mixed;
}