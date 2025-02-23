<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Http\Response\Factory\ResponseFactory;

/**
 * Veritabanı işlemleri sırasında oluşan hatalar için exception sınıfı.
 *
 * SQL hataları, bağlantı sorunları ve diğer veritabanı
 * işlemleri ile ilgili hataları yönetir.
 *
 * @package Framework\Core\Exception
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class DatabaseException extends BaseException
{
    /**
     * Exception için varsayılan mesaj.
     */
    protected string $defaultMessage = 'A database error occurred';

    /**
     * Exception için varsayılan kod.
     */
    protected int $defaultCode = 500;

    /**
     * Constructor.
     *
     * @param ApplicationInterface $app Application instance
     * @param string|null $message Hata mesajı
     * @param int|null $code Hata kodu
     * @param \Throwable|null $previous Önceki exception
     * @param array<string,mixed> $context İlave bağlam verileri
     */
    public function __construct(
        private ApplicationInterface $app,
        ?string $message = null,
        ?int $code = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogLevel(): string
    {
        return 'critical';
    }

    /**
     * {@inheritdoc}
     */
    public function shouldReport(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @throws \JsonException
     */
    public function render(mixed $request): mixed
    {
        $factory = new ResponseFactory();

        // Production'da genel hata
        $environment = $this->app->getEnvironment();
        if ($environment === 'production') {
            return $factory->createResponse(
                500,
                'Internal Server Error',
                ['Content-Type' => 'text/html']
            );
        }

        // Development'ta detaylı hata
        $data = [
            'message' => $this->getMessage(),
            'sql' => $this->context['query'] ?? null,
            'bindings' => $this->context['bindings'] ?? [],
            'connection' => $this->context['connection'] ?? null
        ];

        if (method_exists($request, 'wantsJson') && $request->wantsJson()) {
            return $factory->createJsonResponse($data, 500);
        }

        return $factory->createResponse(
            500,
            json_encode($data, JSON_THROW_ON_ERROR),
            ['Content-Type' => 'application/json']
        );
    }
}