<?php

declare(strict_types=1);

namespace Framework\Core\Event\Attributes;

use Attribute;

/**
 * Listener attribute.
 *
 * Bu attribute, bir metodun veya sınıfın belirli olayları dinlemesini sağlar.
 *
 * @package Framework\Core\Event
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Listener
{
    /**
     * Constructor.
     *
     * @param string|array<string> $event Dinlenecek olay adı veya adları
     * @param int $priority Dinleyici önceliği (düşük değer, yüksek öncelik)
     * @param bool $stopPropagation Bu listener'dan sonra propagasyon dursun mu?
     */
    public function __construct(
        public string|array $event,
        public int $priority = 0,
        public bool $stopPropagation = false
    ) {}
}