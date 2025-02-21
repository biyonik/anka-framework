<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Attributes;

/**
 * Aspect attribute.
 *
 * Bir sınıfın aspect olduğunu belirtmek için kullanılır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Aspect
{
    /**
     * Constructor.
     *
     * @param string|null $id Aspect ID'si (null ise sınıf adı kullanılır)
     * @param int $priority Aspect önceliği
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly int $priority = 10
    ) {
    }
}