<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Attributes;

use Attribute;

/**
 * Metot çağrılarını loglamak için kullanılabilecek attribute.
 *
 * @package Framework\Core\Logging
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Log
{
    /**
     * @param string $level Log seviyesi
     * @param string|null $message Log mesajı, null ise otomatik oluşturulur
     * @param bool $logParams Metot parametrelerini logla
     * @param bool $logReturn Dönüş değerini logla
     * @param string|null $channel Kullanılacak log kanalı
     */
    public function __construct(
        public string $level = 'debug',
        public ?string $message = null,
        public bool $logParams = true,
        public bool $logReturn = true,
        public ?string $channel = null
    ) {}
}