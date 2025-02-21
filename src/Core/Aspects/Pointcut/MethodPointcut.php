<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Pointcut;

use Framework\Core\Aspects\AbstractPointcut;

/**
 * MethodPointcut sınıfı.
 *
 * Metod adına veya desenine göre eşleşen pointcut.
 *
 * @package Framework\Core\Aspects
 * @subpackage Pointcut
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class MethodPointcut extends AbstractPointcut
{
    /**
     * Metod adı deseni.
     *
     * @var string
     */
    protected string $pattern;

    /**
     * Sınıf adı deseni.
     *
     * @var string|null
     */
    protected ?string $classPattern = null;

    /**
     * Pattern içerisinde joker karakter kullanılmış mı?
     *
     * @var bool
     */
    protected bool $hasWildcard = false;

    /**
     * Class pattern içerisinde joker karakter kullanılmış mı?
     *
     * @var bool
     */
    protected bool $hasClassWildcard = false;

    /**
     * Constructor.
     *
     * @param string $pattern Metod adı deseni (örn: "set*", "get*", "findBy*")
     * @param string|null $classPattern Sınıf adı deseni (örn: "App\Entity\*", "*Repository")
     * @param string|null $name Pointcut adı
     * @param int $priority Pointcut önceliği
     */
    public function __construct(string $pattern, ?string $classPattern = null, ?string $name = null, int $priority = 10)
    {
        parent::__construct($name, null, $priority);
        $this->setPattern($pattern);

        if ($classPattern !== null) {
            $this->setClassPattern($classPattern);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function matches(\ReflectionMethod $method, ?object $instance = null): bool
    {
        $methodName = $method->getName();
        $className = $method->getDeclaringClass()->getName();

        // Metod adı kontrolü
        $methodMatches = $this->hasWildcard
            ? $this->matchesPattern($methodName, $this->pattern)
            : $methodName === $this->pattern;

        if (!$methodMatches) {
            return false;
        }

        // Sınıf adı kontrolü (eğer belirtilmişse)
        if ($this->classPattern !== null) {
            return $this->hasClassWildcard
                ? $this->matchesPattern($className, $this->classPattern)
                : $className === $this->classPattern;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $expression): self
    {
        // Format: [class].[method]
        if (str_contains($expression, '.')) {
            [$classPattern, $methodPattern] = explode('.', $expression, 2);
            $this->setClassPattern($classPattern);
            $this->setPattern($methodPattern);
        } else {
            $this->setPattern($expression);
        }

        return $this;
    }

    /**
     * Metod adı desenini ayarlar.
     *
     * @param string $pattern Metod adı deseni
     * @return self Akıcı arayüz için
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;
        $this->hasWildcard = str_contains($pattern, '*');
        return $this;
    }

    /**
     * Sınıf adı desenini ayarlar.
     *
     * @param string $classPattern Sınıf adı deseni
     * @return self Akıcı arayüz için
     */
    public function setClassPattern(string $classPattern): self
    {
        $this->classPattern = $classPattern;
        $this->hasClassWildcard = str_contains($classPattern, '*');
        return $this;
    }

    /**
     * Metod adı desenini döndürür.
     *
     * @return string Metod adı deseni
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Sınıf adı desenini döndürür.
     *
     * @return string|null Sınıf adı deseni
     */
    public function getClassPattern(): ?string
    {
        return $this->classPattern;
    }

    /**
     * Bir string'in belirli bir desene uyup uymadığını kontrol eder.
     *
     * @param string $value Kontrol edilecek değer
     * @param string $pattern Desen
     * @return bool Eşleşme durumu
     */
    protected function matchesPattern(string $value, string $pattern): bool
    {
        $regex = '/^' . str_replace('*', '.*', $pattern) . '$/';
        return (bool) preg_match($regex, $value);
    }
}