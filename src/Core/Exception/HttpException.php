<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

use Framework\Core\Http\Response\Factory\ResponseFactory;

/**
 * HTTP temelli exceptionlar için temel sınıf.
 *
 * HTTP durum kodları ve headerları ile ilişkili hatalar için
 * kullanılır. Framework'ün HTTP katmanı ile entegre çalışır.
 *
 * @package Framework\Core\Exception
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class HttpException extends BaseException
{
    /**
     * Constructor.
     *
     * @param int $statusCode HTTP durum kodu
     * @param string|null $message Hata mesajı
     * @param array<string,string> $headers HTTP başlıkları
     * @param int|null $code İç hata kodu
     * @param \Throwable|null $previous Önceki exception
     * @param array<string,mixed> $context İlave bağlam verileri
     */
    public function __construct(
        protected int $statusCode,
        ?string $message = null,
        protected array $headers = [],
        ?int $code = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * HTTP durum kodunu döndürür.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * HTTP başlıklarını döndürür.
     *
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed $request): mixed
    {
        $factory = new ResponseFactory();

        $data = [
            'error' => [
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode()
            ]
        ];

        // API isteği kontrolü
        if (method_exists($request, 'wantsJson') && $request->wantsJson()) {
            return $factory->createJsonResponse(
                $data,
                $this->getStatusCode(),
                $this->getHeaders()
            );
        }

        // HTML yanıtı
        return $factory->createResponse(
            $this->getStatusCode(),
            $this->getMessage(),
            $this->getHeaders()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogLevel(): string
    {
        return match ($this->statusCode) {
            400, 401, 403, 404 => 'warning',
            500, 501, 502, 503 => 'error',
            default => 'notice'
        };
    }
}