<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Attributes;

/**
 * Pointcut attribute.
 *
 * Bir metod veya sınıf için pointcut tanımlamak için kullanılır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Pointcut
{
    /**
     * Constructor.
     *
     * @param string $expression Pointcut ifadesi (örn: "execution(* com.example.*.*(..))")
     * @param string|null $name Pointcut adı (null ise metod adı kullanılır)
     */
    public function __construct(
        public readonly string $expression,
        public readonly ?string $name = null
    ) {
    }
}