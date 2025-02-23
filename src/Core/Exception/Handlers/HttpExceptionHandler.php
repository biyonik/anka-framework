<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Framework\Core\Http\Request\Factory\RequestFactory;
use Framework\Core\Http\Response\Factory\ResponseFactory;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Logging\Contracts\LoggerInterface;
use Framework\Core\Exception\HttpException;
use Framework\Core\Exception\ValidationException;
use Framework\Core\Exception\AuthenticationException;
use Framework\Core\Exception\AuthorizationException;

/**
 * HTTP istekleri için özel exception handler.
 *
 * Web ve API isteklerinde oluşan hataları işler ve
 * uygun HTTP response'ları üretir.
 *
 * @package Framework\Core\Exception
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class HttpExceptionHandler extends AbstractExceptionHandler
{
    /**
     * HTTP status kodları ve exception tipleri eşleşmesi.
     *
     * @var array<class-string,int>
     */
    protected array $statusCodeMapping = [
        AuthenticationException::class => 401,
        AuthorizationException::class => 403,
        ValidationException::class => 422,
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
    protected function getCurrentRequest(): ServerRequestInterface
    {
        return $this->requestFactory->createFromGlobals();
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed $request, Throwable $e): mixed
    {
        // Development modunda detaylı hata sayfası
        if (!$this->config->getEnvironment()->is('production')) {
            return $this->renderDebugResponse($request, $e);
        }

        // API isteği için JSON response
        if (method_exists($request, 'wantsJson') && $request->wantsJson()) {
            return $this->renderApiResponse($e);
        }

        // Web isteği için HTML response
        return $this->renderWebResponse($e);
    }

    /**
     * Debug modunda detaylı hata sayfası oluşturur.
     */
    protected function renderDebugResponse(mixed $request, Throwable $e): mixed
    {
        $data = [
            'message' => $e->getMessage(),
            'code' => $this->getStatusCode($e),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'previous' => $e->getPrevious() ? [
                'message' => $e->getPrevious()->getMessage(),
                'code' => $e->getPrevious()->getCode(),
                'file' => $e->getPrevious()->getFile(),
                'line' => $e->getPrevious()->getLine()
            ] : null
        ];

        if (method_exists($request, 'wantsJson') && $request->wantsJson()) {
            return $this->responseFactory->createJsonResponse(
                $data,
                $this->getStatusCode($e)
            );
        }

        // Debug HTML sayfası
        return $this->responseFactory->createHtmlResponse(
            $this->renderDebugPage($data),
            $this->getStatusCode($e)
        );
    }

    /**
     * API response oluşturur.
     */
    protected function renderApiResponse(Throwable $e): ResponseInterface
    {
        $data = [
            'error' => true,
            'message' => $e->getMessage()
        ];

        // ValidationException için hata detayları
        if ($e instanceof ValidationException) {
            $data['errors'] = $e->getErrors();
        }

        return $this->responseFactory->createJsonResponse(
            $data,
            $this->getStatusCode($e)
        );
    }

    /**
     * Web sayfası response'u oluşturur.
     */
    protected function renderWebResponse(Throwable $e): \Framework\Core\Http\Response\Interfaces\ResponseInterface
    {
        $statusCode = $this->getStatusCode($e);

        // Basit hata mesajı
        $content = match ($statusCode) {
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            419 => 'Page Expired',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'An error occurred'
        };

        return $this->responseFactory->createHtmlResponse(
            $content,
            $statusCode
        );
    }

    /**
     * Exception için uygun HTTP status kodunu döndürür.
     */
    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        foreach ($this->statusCodeMapping as $class => $code) {
            if ($e instanceof $class) {
                return $code;
            }
        }

        return 500;
    }

    /**
     * Debug sayfası HTML'ini oluşturur.
     *
     * @param array<string,mixed> $data Debug verisi
     */
    protected function renderDebugPage(array $data): string
    {
        // Basit bir debug template
        $html = '<html><head><title>Error</title>';
        $html .= '<style>body{font-family:sans-serif;padding:20px;}</style></head>';
        $html .= '<body><h1>Error: ' . htmlspecialchars($data['message']) . '</h1>';
        $html .= '<h2>Details</h2>';
        $html .= '<ul>';
        $html .= '<li>Code: ' . $data['code'] . '</li>';
        $html .= '<li>Exception: ' . htmlspecialchars($data['exception']) . '</li>';
        $html .= '<li>File: ' . htmlspecialchars($data['file']) . '</li>';
        $html .= '<li>Line: ' . $data['line'] . '</li>';
        $html .= '</ul>';
        $html .= '<h2>Stack Trace</h2>';
        $html .= '<pre>' . htmlspecialchars($data['trace']) . '</pre>';

        if ($data['previous']) {
            $html .= '<h2>Previous Exception</h2>';
            $html .= '<ul>';
            $html .= '<li>Message: ' . htmlspecialchars($data['previous']['message']) . '</li>';
            $html .= '<li>Code: ' . $data['previous']['code'] . '</li>';
            $html .= '<li>File: ' . htmlspecialchars($data['previous']['file']) . '</li>';
            $html .= '<li>Line: ' . $data['previous']['line'] . '</li>';
            $html .= '</ul>';
        }

        $html .= '</body></html>';

        return $html;
    }
}