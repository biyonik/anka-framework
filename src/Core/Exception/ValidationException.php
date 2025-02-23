<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

use Framework\Core\Http\Response\Factory\ResponseFactory;

/**
 * Validation (doğrulama) hataları için exception sınıfı.
 *
 * Form doğrulama, CQRS validation ve diğer veri doğrulama
 * senaryoları için kullanılır. Validation hataları hakkında
 * detaylı bilgi taşır.
 *
 * @package Framework\Core\Exception
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ValidationException extends HttpException
{
    /**
     * Constructor.
     *
     * @param array<string,array<string>> $errors Validation hataları
     * @param string|null $message Genel hata mesajı
     * @param array<string,string> $headers HTTP başlıkları
     * @param int|null $code İç hata kodu
     * @param \Throwable|null $previous Önceki exception
     */
    public function __construct(
        protected array $errors,
        ?string $message = null,
        array $headers = [],
        ?int $code = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            422,
            $message ?? 'The given data was invalid.',
            $headers,
            $code,
            $previous,
            ['validation_errors' => $errors]
        );

        $this->errors = $errors;
    }

    /**
     * Validation hatalarını döndürür.
     *
     * @return array<string,array<string>> Validation hataları
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     * @throws \JsonException
     */
    public function render(mixed $request): mixed
    {
        $factory = new ResponseFactory();

        $data = [
            'message' => $this->getMessage(),
            'errors' => $this->getErrors()
        ];

        // API isteği kontrolü
        if (method_exists($request, 'wantsJson') && $request->wantsJson()) {
            return $factory->createJsonResponse(
                $data,
                $this->getStatusCode(),
                $this->getHeaders()
            );
        }

        // Form submit sonrası back
        return $factory->createResponse(
            $this->getStatusCode(),
            json_encode([
                'errors' => $this->getErrors(),
                'old_input' => $request->all()
            ], JSON_THROW_ON_ERROR),
            array_merge(
                $this->getHeaders(),
                ['Content-Type' => 'application/json']
            )
        );
    }
}