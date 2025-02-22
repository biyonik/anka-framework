<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Attributes;

use Attribute;

/**
 * Sınıf veya metodun tüm yürütümünü loglamak için kullanılabilecek attribute.
 * Aspect katmanı tarafından yakalanır ve loglama işlemleri gerçekleştirilir.
 *
 * @package Framework\Core\Logging
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class LogExecution
{
    /**
     * @param string $level Log seviyesi
     * @param bool $logParams Metot parametrelerini logla
     * @param bool $logResult Dönüş değerini logla
     * @param bool $logExecutionTime Yürütme süresini logla
     * @param string|null $message Özel log mesajı
     * @param string|null $channel Kullanılacak log kanalı
     */
    public function __construct(
        public string $level = 'info',
        public bool $logParams = true,
        public bool $logResult = true,
        public bool $logExecutionTime = true,
        public ?string $message = null,
        public ?string $channel = null
    ) {}
}