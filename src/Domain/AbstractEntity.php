<?php

declare(strict_types=1);

namespace Framework\Domain;

use Framework\Domain\Contracts\EntityInterface;

/**
 * Soyut Entity sınıfı.
 *
 * Domain entity'leri için temel implementasyon sağlayan soyut sınıf.
 *
 * @package Framework\Domain
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * Entity'nin benzersiz kimliği.
     *
     * @var mixed
     */
    protected mixed $id;

    /**
     * {@inheritdoc}
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(mixed $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(?EntityInterface $entity): bool
    {
        if ($entity === null) {
            return false;
        }

        if (get_class($this) !== get_class($entity)) {
            return false;
        }

        if ($this->getId() === null || $entity->getId() === null) {
            return false;
        }

        return $this->getId() === $entity->getId();
    }

    /**
     * Entity'nin string temsilini döndürür.
     *
     * @return string String temsili
     */
    public function __toString(): string
    {
        return sprintf('%s#%s', get_class($this), $this->getId());
    }
}