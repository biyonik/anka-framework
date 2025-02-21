<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Exceptions;

use InvalidArgumentException;

/**
 * Command validation hatası durumunda fırlatılan istisna.
 *
 * @package Framework\Core\CQRS
 * @subpackage Exceptions
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class CommandValidationException extends InvalidArgumentException
{
    /**
     * Validation hataları.
     *
     * @var array<string, array<string>>
     */
    protected array $errors = [];

    /**
     * Constructor.
     *
     * @param array<string, array<string>> $errors Validation hataları
     * @param string $message Hata mesajı
     * @param int $code Hata kodu
     * @param \Throwable|null $previous Önceki istisna
     */
    public function __construct(
        array $errors,
        string $message = 'Command validation failed',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Validation hatalarını döndürür.
     *
     * @return array<string, array<string>> Validation hataları
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}