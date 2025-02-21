<?php

declare(strict_types=1);

namespace Framework\Domain;

use Framework\Domain\Contracts\ValueObjectInterface;

/**
 * Soyut Value Object sınıfı.
 *
 * Value object'ler için temel implementasyon sağlayan soyut sınıf.
 *
 * @package Framework\Domain
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractValueObject implements ValueObjectInterface
{
    /**
     * {@inheritdoc}
     */
    public function equals(ValueObjectInterface $valueObject): bool
    {
        if (get_class($this) !== get_class($valueObject)) {
            return false;
        }

        return $this->hash() === $valueObject->hash();
    }

    /**
     * {@inheritdoc}
     */
    public function hash(): string
    {
        return md5(serialize($this->toArray()));
    }

    /**
     * {@inheritdoc}
     */
    abstract public function toArray(): array;

    /**
     * Value object'in string temsilini döndürür.
     *
     * @return string String temsili
     */
    public function __toString(): string
    {
        return json_encode($this->toArray()) ?: '';
    }
}