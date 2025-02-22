<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\EitherInterface;

/**
 * Either sınıfı.
 *
 * İki değerden birini tutan Either monad implementasyonu.
 * Left genellikle hata durumunu, Right ise başarı durumunu temsil eder.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template L
 * @template R
 * @implements EitherInterface<L, R>
 */
class Either implements EitherInterface
{
    /**
     * Left değeri.
     *
     * @var L|null
     */
    private mixed $left;

    /**
     * Right değeri.
     *
     * @var R|null
     */
    private mixed $right;

    /**
     * Durumu belirten bayrak.
     *
     * @var bool
     */
    private bool $isLeft;

    /**
     * Constructor.
     *
     * @param bool $isLeft Left ise true, Right ise false
     * @param L|null $left Left değeri
     * @param R|null $right Right değeri
     */
    private function __construct(bool $isLeft, mixed $left = null, mixed $right = null)
    {
        $this->isLeft = $isLeft;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeft(): mixed
    {
        return $this->left;
    }

    /**
     * {@inheritdoc}
     */
    public function getRight(): mixed
    {
        return $this->right;
    }

    /**
     * {@inheritdoc}
     */
    public function isLeft(): bool
    {
        return $this->isLeft;
    }

    /**
     * {@inheritdoc}
     */
    public function isRight(): bool
    {
        return !$this->isLeft;
    }

    /**
     * {@inheritdoc}
     */
    public function mapLeft(callable $callback): EitherInterface
    {
        if ($this->isLeft) {
            return new self(true, $callback($this->left), null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mapRight(callable $callback): EitherInterface
    {
        if (!$this->isLeft) {
            return new self(false, null, $callback($this->right));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fold(callable $leftCallback, callable $rightCallback): mixed
    {
        if ($this->isLeft) {
            return $leftCallback($this->left);
        }

        return $rightCallback($this->right);
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapRight(callable $callback): EitherInterface
    {
        if (!$this->isLeft) {
            return $callback($this->right);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapLeft(callable $callback): EitherInterface
    {
        if ($this->isLeft) {
            return $callback($this->left);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function right(mixed $value): EitherInterface
    {
        return new self(false, null, $value);
    }

    /**
     * {@inheritdoc}
     */
    public static function left(mixed $value): EitherInterface
    {
        return new self(true, $value, null);
    }

    /**
     * {@inheritdoc}
     */
    public static function try(callable $callback): EitherInterface
    {
        try {
            return self::right($callback());
        } catch (\Throwable $e) {
            return self::left($e);
        }
    }

    /**
     * Either durumunu ve değerlerini string olarak döndürür.
     *
     * @return string Either temsili
     */
    public function __toString(): string
    {
        if ($this->isLeft) {
            $value = $this->left instanceof \Stringable || is_scalar($this->left)
                ? (string) $this->left
                : gettype($this->left);

            return "Left({$value})";
        }

        $value = $this->right instanceof \Stringable || is_scalar($this->right)
            ? (string) $this->right
            : gettype($this->right);

        return "Right({$value})";
    }

    /**
     * Right değerini döndürür, Right değilse varsayılan değeri döndürür.
     *
     * @param R $default Varsayılan değer
     * @return R Right değeri veya varsayılan değer
     */
    public function getOrElse(mixed $default): mixed
    {
        if ($this->isLeft) {
            return $default;
        }

        return $this->right;
    }

    /**
     * Right değerini döndürür, Right değilse callback'i çalıştırır ve sonucunu döndürür.
     *
     * @param callable(): R $callback Right değilse çalışacak fonksiyon
     * @return R Right değeri veya callback sonucu
     */
    public function getOrCall(callable $callback): mixed
    {
        if ($this->isLeft) {
            return $callback();
        }

        return $this->right;
    }

    /**
     * Right değerini döndürür, Right değilse istisna fırlatır.
     *
     * @throws \RuntimeException Right değilse
     * @return R Right değeri
     */
    public function getOrThrow(\Throwable $exception = null): mixed
    {
        if ($this->isLeft) {
            if ($exception === null) {
                if ($this->left instanceof \Throwable) {
                    throw $this->left;
                }

                throw new \RuntimeException(
                    sprintf('Either is Left, expected Right. Left value: %s', $this->left)
                );
            }

            throw $exception;
        }

        return $this->right;
    }

    /**
     * Left değeri üzerinde işlem yapar, sonraki işlemler için yeni bir Either döndürür.
     *
     * @param callable(L): L $onLeft Left ise çalışacak fonksiyon
     * @return Either<L, R> Yeni Either
     */
    public function peekLeft(callable $onLeft): self
    {
        if ($this->isLeft) {
            $onLeft($this->left);
        }

        return $this;
    }

    /**
     * Right değeri üzerinde işlem yapar, sonraki işlemler için yeni bir Either döndürür.
     *
     * @param callable(R): R $onRight Right ise çalışacak fonksiyon
     * @return Either<L, R> Yeni Either
     */
    public function peekRight(callable $onRight): self
    {
        if (!$this->isLeft) {
            $onRight($this->right);
        }

        return $this;
    }

    /**
     * Swap the values of left and right.
     *
     * @return Either<R, L> Swapped Either
     */
    public function swap(): self
    {
        if ($this->isLeft) {
            return self::right($this->left);
        }

        return self::left($this->right);
    }
}