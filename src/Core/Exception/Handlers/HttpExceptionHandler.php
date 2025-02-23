<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Handlers;

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
     * Hata kodları ve exception tipleri eşleşmesi.
     */
    protected array $errorViews = [
        401 => 'errors.401',
        403 => 'errors.403',
        404 => 'errors.404',
        419 => 'errors.419',
        429 => 'errors.429',
        500 => 'errors.500',
        503 => 'errors.503'
    ];

    /**
     * Constructor.
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected RequestFactory $requestFactory,
        protected ResponseFactory $responseFactory,
        protected ConfigRepositoryInterface $config,
        protected ViewFactory $viewFactory
    ) {
        parent::__construct($logger, $requestFactory, $responseFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed $request, Throwable $e): mixed
    {
        $isProduction = $this->config->getEnvironment()->is('production');
        $isApiRequest = $request->wantsJson();

        if ($isApiRequest) {
            return $this->renderApiResponse($e, $isProduction);
        }

        return $this->renderWebResponse($e, $isProduction);
    }

    /**
     * API response'u render eder.
     */
    protected function renderApiResponse(Throwable $e, bool $isProduction): mixed
    {
        $status = $this->getHttpStatusCode($e);
        $data = [
            'error' => true,
            'message' => $isProduction ? $this->getProductionMessage($status) : $e->getMessage()
        ];

        if ($e instanceof ValidationException) {
            $data['errors'] = $e->getErrors();
        }

        if (!$isProduction) {
            $data['exception'] = get_class($e);
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = explode("\n", $e->getTraceAsString());
        }

        return $this->responseFactory->createJsonResponse($data, $status);
    }

    /**
     * Web response'u render eder.
     */
    protected function renderWebResponse(Throwable $e, bool $isProduction): mixed
    {
        $status = $this->getHttpStatusCode($e);
        $view = $this->getErrorView($status, $isProduction);

        $data = [
            'exception' => $e,
            'statusCode' => $status,
            'message' => $isProduction ? $this->getProductionMessage($status) : $e->getMessage(),
            'isProduction' => $isProduction
        ];

        if (!$isProduction) {
            $data = array_merge($data, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'previous' => $e->getPrevious(),
                'request' => $this->getCurrentRequest(),
                'server' => $_SERVER
            ]);

            // Exception tipine göre özel veriler ekle
            if ($e instanceof ValidationException) {
                $data['errors'] = $e->getErrors();
            } elseif ($e instanceof AuthenticationException) {
                $data['guards'] = $this->getAuthGuards();
            } elseif ($e instanceof AuthorizationException) {
                $data['user'] = $this->getCurrentUser();
            }
        }

        return $this->responseFactory->createResponse(
            $status,
            $this->viewFactory->make($view, $data)->render()
        );
    }

    /**
     * HTTP status kodunu belirler.
     */
    protected function getHttpStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        return match (true) {
            $e instanceof ValidationException => 422,
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            default => 500
        };
    }

    /**
     * Error view'ını belirler.
     */
    protected function getErrorView(int $status, bool $isProduction): string
    {
        $basePath = $isProduction ? 'errors.production.' : 'errors.development.';
        return $basePath . ($this->errorViews[$status] ?? '500');
    }

    /**
     * Production ortamı için hata mesajını döndürür.
     */
    protected function getProductionMessage(int $status): string
    {
        return match ($status) {
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            419 => 'Page Expired',
            429 => 'Too Many Requests',
            503 => 'Service Unavailable',
            default => 'Internal Server Error'
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentRequest(): mixed
    {
        return $this->requestFactory->createFromGlobals();
    }

    /**
     * Mevcut auth guard'ları döndürür.
     */
    protected function getAuthGuards(): array
    {
        // Framework'ünüzün auth sistemine göre implemente edilmeli
        return [];
    }

    /**
     * Mevcut kullanıcıyı döndürür.
     */
    protected function getCurrentUser(): ?array
    {
        // Framework'ünüzün auth sistemine göre implemente edilmeli
        return null;
    }
}