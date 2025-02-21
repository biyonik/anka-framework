<?php

declare(strict_types=1);

namespace Framework\Core\CQRS;

use Framework\Core\CQRS\Contracts\QueryInterface;
use Framework\Core\Validation\Contracts\ValidationSchemaInterface;
use Framework\Core\Validation\ValidationResult;
use Framework\Core\Validation\ValidationSchema;
use ReflectionClass;
use ReflectionProperty;

/**
 * Abstract Query sınıfı.
 *
 * Bu sınıf, Query nesneleri için temel bir abstract sınıftır
 * ve QueryInterface'i uygular.
 *
 * @package Framework\Core\CQRS
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractQuery implements QueryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return static::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $result = [
            'query_type' => $this->getType()
        ];

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);
            $name = $property->getName();
            $result[$name] = $property->getValue($this);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        return $this->buildValidationSchema()->toCQRSRules();
    }

    /**
     * Query için validasyon şeması oluşturur.
     *
     * Alt sınıflar bu metodu override ederek kendi validasyon şemalarını tanımlayabilir.
     *
     * @return ValidationSchemaInterface Validasyon şeması
     */
    protected function buildValidationSchema(): ValidationSchemaInterface
    {
        // Varsayılan olarak boş şema döndür
        return ValidationSchema::make();
    }

    /**
     * Query verilerini doğrular.
     *
     * @return ValidationResult Doğrulama sonucu
     */
    public function validate(): ValidationResult
    {
        return $this->buildValidationSchema()->validate($this->getParameters());
    }

    /**
     * Query'i diziden oluşturur.
     *
     * @param array<string, mixed> $data Query parametreleri
     * @return static Query nesnesi
     * @throws \ReflectionException
     */
    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * Query'i JSON verisinden oluşturur.
     *
     * @param string $json JSON formatında query verisi
     * @return static Query nesnesi
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON format: ' . json_last_error_msg());
        }

        return static::fromArray($data);
    }

    /**
     * Query'i JSON'a dönüştürür.
     *
     * @return string JSON formatında query
     */
    public function toJson(): string
    {
        return json_encode($this->getParameters(), JSON_THROW_ON_ERROR);
    }
}