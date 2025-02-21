<?php

declare(strict_types=1);

namespace Framework\Core\CQRS;

use Framework\Core\CQRS\Contracts\CommandInterface;
use Framework\Core\Validation\Contracts\ValidationSchemaInterface;
use Framework\Core\Validation\ValidationResult;
use Framework\Core\Validation\ValidationSchema;
use Random\RandomException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Abstract Command sınıfı.
 *
 * Bu sınıf, Command nesneleri için temel bir abstract sınıftır
 * ve CommandInterface'i uygular.
 *
 * @package Framework\Core\CQRS
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     * Command ID'si.
     */
    protected ?string $commandId = null;

    /**
     * Command oluşturulduğunda çağrılır.
     *
     * @return void
     * @throws RandomException
     */
    public function initialize(): void
    {
        // Command ID yoksa oluştur
        if ($this->commandId === null) {
            $this->commandId = $this->generateCommandId();
        }
    }

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
    public function getCommandId(): ?string
    {
        return $this->commandId;
    }

    /**
     * Command için unique ID oluşturur.
     *
     * @return string Benzersiz ID
     * @throws RandomException
     */
    protected function generateCommandId(): string
    {
        return sprintf('%s-%s', static::class, bin2hex(random_bytes(8)));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $result = [
            'command_type' => $this->getType(),
            'command_id' => $this->getCommandId()
        ];

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);
            $name = $property->getName();

            // commandId özelliğini atlayın, zaten ekledik
            if ($name === 'commandId') {
                continue;
            }

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
     * Command için validasyon şeması oluşturur.
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
     * Command verilerini doğrular.
     *
     * @return ValidationResult Doğrulama sonucu
     */
    public function validate(): ValidationResult
    {
        return $this->buildValidationSchema()->validate($this->toArray());
    }

    /**
     * Command'i diziden oluşturur.
     *
     * @param array<string, mixed> $data Command verileri
     * @return static Command nesnesi
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

        $instance->initialize();

        return $instance;
    }

    /**
     * Command'i JSON verisinden oluşturur.
     *
     * @param string $json JSON formatında command verisi
     * @return static Command nesnesi
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
     * Command'i JSON'a dönüştürür.
     *
     * @return string JSON formatında command
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}