<?php

declare(strict_types=1);

namespace Framework\Core\Exception\Handlers;

use Framework\Core\Exception\AuthenticationException;
use Framework\Core\Exception\AuthorizationException;
use Framework\Core\Exception\Contracts\RenderableExceptionInterface;
use Framework\Core\Exception\HttpException;
use Framework\Core\Exception\NotFoundHttpException;
use Framework\Core\Exception\ValidationException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Framework\Core\Http\Request\Factory\RequestFactory;
use Framework\Core\Http\Response\Factory\ResponseFactory;
use Framework\Core\Configuration\Contracts\ConfigRepositoryInterface;
use Framework\Core\Logging\Contracts\LoggerInterface;

/**
 * Framework'ün global exception handler'ı.
 *
 * Farklı ortamlar ve request tipleri için exception'ları
 * uygun şekilde işler ve response üretir.
 *
 * @package Framework\Core\Exception
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class GlobalExceptionHandler extends AbstractExceptionHandler
{
    /**
     * Raporlanmayacak exception tipleri.
     *
     * @var array<class-string>
     */
    protected array $dontReport = [
        ValidationException::class,
        NotFoundHttpException::class,
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
        // RenderableException kendi render'ını yapabilir
        if ($e instanceof RenderableExceptionInterface) {
            return $e->render($request);
        }

        // Production'da detaylı hata gösterme
        $isProduction = $this->config->getEnvironment()->is('production');

        // API isteği için JSON response
        if (method_exists($request, 'wantsJson') && $request->wantsJson()) {
            return $this->renderJsonResponse($e, $isProduction);
        }

        // Web isteği için HTML response
        return $this->renderHtmlResponse($e, $isProduction);
    }

    /**
     * JSON formatında hata response'u oluşturur.
     */
    protected function renderJsonResponse(Throwable $e, bool $isProduction): mixed
    {
        $data = [
            'error' => true,
            'message' => $isProduction ? 'Server Error' : $e->getMessage(),
        ];

        // Development'ta ekstra bilgiler
        if (!$isProduction) {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTraceAsString();
        }

        return $this->responseFactory->createJsonResponse(
            $data,
            $this->getHttpStatusCode($e)
        );
    }

    /**
     * HTML formatında hata response'u oluşturur.
     */
    protected function renderHtmlResponse(Throwable $e, bool $isProduction): mixed
    {
        $content = $isProduction
            ? 'Internal Server Error'
            : $e->getMessage() . "\n" . $e->getTraceAsString();

        return $this->responseFactory->createHtmlResponse(
            $content,
            $this->getHttpStatusCode($e)
        );
    }

    /**
     * Exception için uygun HTTP status kodunu belirler.
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
}