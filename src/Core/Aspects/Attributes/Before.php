<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Attributes;

/**
 * Before attribute.
 *
 * Bir metodun before advice olduğunu belirtmek için kullanılır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Before
{
    /**
     * Constructor.
     *
     * @param string $pointcut Pointcut ifadesi
     * @param int|null $priority Advice önceliği
     */
    public function __construct(
        public readonly string $pointcut,
        public readonly ?int $priority = null
    ) {
    }
}