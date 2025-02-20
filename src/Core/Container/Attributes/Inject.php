<?php

declare(strict_types = 1);

namespace Framework\Core\Container\Attributes;

/**
 * Bir constructor parametresine değer enjekte edilmesini sağlayan attribute.
 * 
 * Bu attribute, constructor dependency injection'ı özelleştirmek için kullanılır.
 * Parametre değerlerinin container tarafından nasıl çözümleneceğini belirler.
 * 
 * Özellikler:
 * - Named parameter injection
 * - Service reference injection
 * - Conditional value injection
 * - Default value overriding
 * 
 * @package Framework\Core\Container
 * @subpackage Attributes
 * @author [Yazarın Adı]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * public function __construct(
 *     #[Inject('database.host')] string $host,
 *     #[Inject(service: LoggerInterface::class)] Logger $logger,
 *     #[Inject(value: 3306)] int $port
 * ) {}
 * ```
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Inject
{
    /**
     * Inject attribute constructor.
     * 
     * @param string|null $id Container'dan çözümlenecek parametre ID'si
     * @param string|null $service Enjekte edilecek servis referansı
     * @param mixed $value Doğrudan enjekte edilecek değer
     * @param bool $required Parametrenin zorunlu olup olmadığı
     */
    public function __construct(
        private readonly ?string $id = null,
        private readonly ?string $service = null,
        private readonly mixed $value = null,
        private readonly bool $required = true
    ) {}

    /**
     * Enjeksiyon için kullanılacak parametre ID'sini döndürür.
     * 
     * @return string|null Parametre ID'si veya null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Enjekte edilecek servis referansını döndürür.
     * 
     * @return string|null Servis class adı veya null
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * Doğrudan enjekte edilecek değeri döndürür.
     * 
     * @return mixed Enjekte edilecek değer
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Parametrenin zorunlu olup olmadığını döndürür.
     * 
     * @return bool Zorunlu ise true
     */
    public function isRequired(): bool
    {
        return $this->required;
    }
}