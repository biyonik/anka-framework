<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

/**
 * 404 Not Found hatası için exception sınıfı.
 *
 * Talep edilen kaynak bulunamadığında fırlatılır.
 *
 * @package Framework\Core\Exception
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class NotFoundHttpException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string|null $message Hata mesajı
     * @param array<string,string> $headers HTTP başlıkları
     * @param int|null $code İç hata kodu
     * @param \Throwable|null $previous Önceki exception
     */
    public function __construct(
        ?string $message = null,
        array $headers = [],
        ?int $code = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            404,
            $message ?? 'Not Found',
            $headers,
            $code,
            $previous
        );
    }
}