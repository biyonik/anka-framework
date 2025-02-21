<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Attributes;

/**
 * Cacheable attribute.
 *
 * Bir metodun sonucunun önbelleğe alınabilir olduğunu belirtmek için kullanılır.
 * Bu attribute ile işaretlenen metodların sonuçları önbelleğe alınır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable
{
    /**
     * Constructor.
     *
     * @param string $key Önbellek anahtarı (default: sınıf + metod adı + parametreler)
     * @param int $ttl Önbellek süresi (saniye)
     * @param string|null $region Önbellek bölgesi (null ise default bölge kullanılır)
     * @param array<string> $unless Bu parametreler veya return değerleri için önbellekleme yapılmaz
     */
    public function __construct(
        public readonly string $key = '',
        public readonly int $ttl = 3600,
        public readonly ?string $region = null,
        public readonly array $unless = []
    ) {
    }
}