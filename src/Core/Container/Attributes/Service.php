<?php

declare(strict_types=1);

namespace Framework\Core\Container\Attributes;

/**
 * Bir sınıfın container tarafından yönetilen bir servis olduğunu belirten attribute.
 * 
 * Bu attribute, bir sınıfın DI container tarafından yönetilmesi gerektiğini 
 * ve nasıl yönetileceğini belirler. Sınıfların singleton olup olmadığını,
 * hangi interface ile bind edileceğini ve varsayılan parametrelerini tanımlar.
 * 
 * Özellikler:
 * - Singleton davranışı kontrolü
 * - Interface binding tanımlama
 * - Servis tag'leri tanımlama
 * - Varsayılan parametre değerleri
 * 
 * @package Framework\Core\Container
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * #[Service(singleton: true, binds: LoggerInterface::class, tags: ['logger'])]
 * class FileLogger implements LoggerInterface
 * {
 *     public function __construct(
 *         #[Inject('logger.path')] private string $logPath
 *     ) {}
 * }
 * ```
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Service
{
    /**
     * Service attribute constructor.
     * 
     * @param bool $singleton Servisin singleton olup olmadığı
     * @param string|null $binds Servisin bind edileceği interface/abstract class
     * @param array<string> $tags Servisi kategorize etmek için kullanılan tag'ler
     * @param array<string,mixed> $parameters Servis oluşturulurken kullanılacak varsayılan parametreler
     */
    public function __construct(
        private readonly bool $singleton = false,
        private readonly ?string $binds = null,
        private readonly array $tags = [],
        private readonly array $parameters = []
    ) {}

    /**
     * Servisin singleton olup olmadığını döndürür.
     * 
     * @return bool Singleton ise true
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    /**
     * Servisin bind edileceği interface/abstract class'ı döndürür.
     * 
     * @return string|null Bind edilecek tip veya null
     */
    public function getBinds(): ?string
    {
        return $this->binds;
    }

    /**
     * Servisin tag'lerini döndürür.
     * 
     * @return array<string> Tag listesi
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Servisin varsayılan parametrelerini döndürür.
     * 
     * @return array<string,mixed> Parametre listesi
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}