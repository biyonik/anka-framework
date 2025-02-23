<?php

declare(strict_types=1);

namespace Framework\Core\Exception;

use Exception;
use Framework\Core\Exception\Contracts\ReportableExceptionInterface;
use Framework\Core\Exception\Contracts\RenderableExceptionInterface;

/**
 * Framework'ün temel exception sınıfı.
 *
 * Tüm özel exception sınıfları için temel davranışları ve
 * özellikleri sağlar. Reporting ve rendering yeteneklerini
 * opsiyonel olarak ekleyebilmek için ReportableExceptionInterface
 * ve RenderableExceptionInterface interface'lerini implemente eder.
 *
 * @package Framework\Core\Exception
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class BaseException extends Exception implements ReportableExceptionInterface, RenderableExceptionInterface
{
    /**
     * Exception için varsayılan mesaj.
     */
    protected string $defaultMessage = 'An error occurred';

    /**
     * Exception için varsayılan kod.
     */
    protected int $defaultCode = 0;

    /**
     * İlave bağlam verileri.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * Constructor.
     *
     * @param string|null $message Hata mesajı (null ise varsayılan kullanılır)
     * @param int|null $code Hata kodu (null ise varsayılan kullanılır)
     * @param \Throwable|null $previous Önceki exception
     * @param array<string, mixed> $context İlave bağlam verileri
     */
    public function __construct(
        ?string $message = null,
        ?int $code = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $this->context = $context;

        parent::__construct(
            $message ?? $this->defaultMessage,
            $code ?? $this->defaultCode,
            $previous
        );
    }

    /**
     * Exception için bağlam verilerini döndürür.
     *
     * @return array<string, mixed> Bağlam verileri
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Bağlam verisi ekler.
     *
     * @param string $key Veri anahtarı
     * @param mixed $value Veri değeri
     * @return self
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function report(): void
    {
        $level = $this->getLogLevel();

        logger()->log($level, $this->getMessage(), array_merge(
            $this->context,
            [
                'exception' => $this,
                'code' => $this->getCode(),
                'file' => $this->getFile(),
                'line' => $this->getLine()
            ]
        ));
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
     */
    public function render(mixed $request): mixed
    {
        return null;
    }

    /**
     * Exception için log seviyesini döndürür.
     * Alt sınıflar override edebilir.
     *
     * @return string Log seviyesi
     */
    protected function getLogLevel(): string
    {
        return 'error';
    }
}