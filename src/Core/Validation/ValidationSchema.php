<?php

declare(strict_types=1);

namespace Framework\Core\Validation;

use Framework\Core\Validation\Contracts\ValidationSchemaInterface;
use Framework\Core\Validation\SchemaType\BaseType;
use Framework\Core\Validation\SchemaType\StringType;
use Framework\Core\Validation\SchemaType\NumberType;
use Framework\Core\Validation\SchemaType\BooleanType;
use Framework\Core\Validation\SchemaType\DateType;
use Framework\Core\Validation\SchemaType\ArrayType;
use Framework\Core\Validation\SchemaType\ObjectType;
use Framework\Core\Validation\SchemaType\UuidType;
use Framework\Core\Validation\SchemaType\AdvancedStringType;
use Framework\Core\Validation\SchemaType\CreditCardType;
use Framework\Core\Validation\SchemaType\IbanType;
use Framework\Core\Validation\Traits\AdvancedValidationTrait;

/**
 * PHP için Zod benzeri bir şema doğrulama sınıfı.
 *
 * @package Framework\Core\Validation
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ValidationSchema implements ValidationSchemaInterface
{
    use AdvancedValidationTrait;

    /**
     * Şema tanımları için özel tip.
     *
     * @var array<string, BaseType>
     */
    private array $schema = [];

    /**
     * Özel doğrulama kuralları.
     *
     * @var array<string, callable>
     */
    private array $customRules = [];

    /**
     * Koşullu doğrulama kuralları
     *
     * @var array<array{field: string, expectedValue: mixed, callback: callable}>
     */
    protected array $conditionalRules = [];

    /**
     * Çapraz alan doğrulama kuralları
     *
     * @var array<callable>
     */
    protected array $crossValidators = [];

    /**
     * {@inheritdoc}
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function string(?string $description = null): StringType
    {
        $stringType = new StringType($description);
        return $stringType;
    }

    /**
     * {@inheritdoc}
     */
    public function number(?string $description = null): NumberType
    {
        $numberType = new NumberType($description);
        return $numberType;
    }

    /**
     * {@inheritdoc}
     */
    public function boolean(?string $description = null): BooleanType
    {
        $booleanType = new BooleanType($description);
        return $booleanType;
    }

    /**
     * {@inheritdoc}
     */
    public function date(?string $description = null): DateType
    {
        $dateType = new DateType($description);
        return $dateType;
    }

    /**
     * {@inheritdoc}
     */
    public function object(?string $description = null): ObjectType
    {
        $objectType = new ObjectType($description);
        return $objectType;
    }

    /**
     * {@inheritdoc}
     */
    public function array(?string $description = null): ArrayType
    {
        $arrayType = new ArrayType($description);
        return $arrayType;
    }

    /**
     * UUID alanı ekler.
     *
     * @param string|null $description Alan açıklaması
     * @return UuidType UUID alan tipi
     */
    public function uuid(?string $description = null): UuidType
    {
        $uuidType = new UuidType($description);
        return $uuidType;
    }

    /**
     * Kredi kartı alanı ekler.
     *
     * @param string|null $description Alan açıklaması
     * @return CreditCardType Kredi kartı alan tipi
     */
    public function creditCard(?string $description = null): CreditCardType
    {
        $creditCardType = new CreditCardType($description);
        return $creditCardType;
    }

    /**
     * IBAN alanı ekler.
     *
     * @param string|null $description Alan açıklaması
     * @return IbanType IBAN alan tipi
     */
    public function iban(?string $description = null): IbanType
    {
        $ibanType = new IbanType($description);
        return $ibanType;
    }

    /**
     * Gelişmiş string doğrulama için alan ekler.
     *
     * @param string|null $description Alan açıklaması
     * @return AdvancedStringType Gelişmiş string alan tipi
     */
    public function advancedString(?string $description = null): AdvancedStringType
    {
        $advancedStringType = new AdvancedStringType($description);
        return $advancedStringType;
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomRule(string $name, callable $rule): self
    {
        $this->customRules[$name] = $rule;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data): ValidationResult
    {
        $result = new ValidationResult($data);

        foreach ($this->schema as $field => $rules) {
            $value = $data[$field] ?? null;

            // Her bir kural için doğrulama
            $rules->validate($field, $value, $result);
        }

        // Gelişmiş doğrulamaları uygula
        $this->applyAdvancedValidations($data, $result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toRulesArray(): array
    {
        $rules = [];

        foreach ($this->schema as $field => $type) {
            $rules[$field] = $type->toRulesArray();
        }

        return $rules;
    }

    /**
     * Hata mesajlarını diziye dönüştürür.
     *
     * @return array<string, array<string, string>> Hata mesajları
     */
    public function getErrorMessages(): array
    {
        $messages = [];

        foreach ($this->schema as $field => $type) {
            // getErrorMessages metodu varsa çağır, yoksa boş dizi kullan
            if (method_exists($type, 'getErrorMessages')) {
                $fieldMessages = $type->getErrorMessages($field);
                if (is_array($fieldMessages)) {
                    $messages = array_merge($messages, $fieldMessages);
                }
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function shape(array $shape): self
    {
        $this->schema = $shape;
        return $this;
    }

    /**
     * Nesne klonlama desteği.
     *
     * @return void
     */
    public function __clone()
    {
        // Derin kopyalama için schema'yı klonla
        $clonedSchema = array_map(function ($rule) {
            return clone $rule;
        }, $this->schema);
        $this->schema = $clonedSchema;

        // Koşullu kuralları ve çapraz doğrulamaları da kopyala
        $this->conditionalRules = $this->conditionalRules;
        $this->crossValidators = $this->crossValidators;
    }

    /**
     * Command/Query validasyon için formatlanmış kurallar.
     *
     * @return array<string, string> Doğrulama kuralları
     */
    public function toCQRSRules(): array
    {
        $rules = [];

        foreach ($this->toRulesArray() as $field => $fieldRules) {
            $rules[$field] = is_array($fieldRules) ? implode('|', $fieldRules) : $fieldRules;
        }

        return $rules;
    }

    /**
     * Şema tanımını diziye dönüştürür.
     *
     * @return array<string, mixed> Şema tanımı
     */
    public function toArray(): array
    {
        $schema = [];

        foreach ($this->schema as $field => $type) {
            $schema[$field] = [
                'type' => get_class($type),
                'rules' => $type->toRulesArray()
            ];
        }

        return $schema;
    }
}