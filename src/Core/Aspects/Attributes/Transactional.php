<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Attributes;

/**
 * Transactional attribute.
 *
 * Bir metodun transactional olduğunu belirtmek için kullanılır.
 * Bu attribute ile işaretlenen metodlar, transaction içinde çalıştırılır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Transactional
{
    /**
     * Constructor.
     *
     * @param bool $readOnly Salt okunur transaction mı?
     * @param int $timeout Transaction timeout süresi (saniye)
     * @param array<string> $rollbackFor Bu istisna sınıfları için rollback yapılır
     * @param array<string> $noRollbackFor Bu istisna sınıfları için rollback yapılmaz
     */
    public function __construct(
        public readonly bool $readOnly = false,
        public readonly int $timeout = 30,
        public readonly array $rollbackFor = [\Throwable::class],
        public readonly array $noRollbackFor = []
    ) {
    }
}