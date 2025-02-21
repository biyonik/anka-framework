<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Pointcut;

use Framework\Core\Aspects\AbstractPointcut;

/**
 * AnnotationPointcut sınıfı.
 *
 * Belirli attribute'lara sahip metodları hedefleyen pointcut.
 *
 * @package Framework\Core\Aspects
 * @subpackage Pointcut
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class AnnotationPointcut extends AbstractPointcut
{
    /**
     * Attribute sınıf adı.
     *
     * @var string
     */
    protected string $attributeName;

    /**
     * Constructor.
     *
     * @param string $attributeName Attribute sınıf adı
     * @param string|null $name Pointcut adı
     * @param int $priority Pointcut önceliği
     */
    public function __construct(string $attributeName, ?string $name = null, int $priority = 10)
    {
        parent::__construct($name, null, $priority);
        $this->attributeName = $attributeName;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(\ReflectionMethod $method, ?object $instance = null): bool
    {
        // Metod üzerinde attribute var mı?
        $attributes = $method->getAttributes($this->attributeName);

        if (!empty($attributes)) {
            return true;
        }

        // Sınıf üzerinde attribute var mı?
        $classAttributes = $method->getDeclaringClass()->getAttributes($this->attributeName);

        return !empty($classAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $expression): self
    {
        $this->attributeName = $expression;
        return $this;
    }

    /**
     * Attribute adını döndürür.
     *
     * @return string Attribute adı
     */
    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    /**
     * Attribute adını ayarlar.
     *
     * @param string $attributeName Attribute adı
     * @return self Akıcı arayüz için
     */
    public function setAttributeName(string $attributeName): self
    {
        $this->attributeName = $attributeName;
        return $this;
    }

    /**
     * Belirli bir metod üzerinde attribute örneğini alır.
     *
     * @param \ReflectionMethod $method Metod yansıması
     * @return object|null Attribute örneği veya null
     */
    public function getAttributeInstance(\ReflectionMethod $method): ?object
    {
        $attributes = $method->getAttributes($this->attributeName);

        if (!empty($attributes)) {
            return $attributes[0]->newInstance();
        }

        $classAttributes = $method->getDeclaringClass()->getAttributes($this->attributeName);

        if (!empty($classAttributes)) {
            return $classAttributes[0]->newInstance();
        }

        return null;
    }
}