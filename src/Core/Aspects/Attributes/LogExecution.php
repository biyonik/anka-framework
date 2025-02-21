<?php

declare(strict_types=1);

namespace Framework\Core\Aspects\Attributes;

/**
 * LogExecution attribute.
 *
 * Bir metodun yürütülmesinin loglanacağını belirtmek için kullanılır.
 * Bu attribute ile işaretlenen metodların başlangıç ve bitiş zamanları, parametreleri
 * ve sonuçları loglanır.
 *
 * @package Framework\Core\Aspects
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class LogExecution
{
    /**
     * Constructor.
     *
     * @param string $level Log seviyesi (DEBUG, INFO, WARN, ERROR)
     * @param bool $logParams Parametreler loglansın mı?
     * @param bool $logResult Sonuç loglansın mı?
     * @param bool $logExecutionTime Yürütme süresi loglansın mı?
     * @param bool $logExceptions İstisnalar detaylı loglansın mı?
     */
    public function __construct(
        public readonly string $level = 'DEBUG',
        public readonly bool $logParams = true,
        public readonly bool $logResult = true,
        public readonly bool $logExecutionTime = true,
        public readonly bool $logExceptions = true
    ) {
    }
}