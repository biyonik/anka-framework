<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Processors;

use Framework\Core\Logging\Contracts\LogProcessorInterface;
use Framework\Core\Logging\LogRecord;

/**
 * Log kaydına çağıran dosya, sınıf, metod bilgilerini ekler.
 *
 * @package Framework\Core\Logging
 * @subpackage Processors
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class IntrospectionProcessor implements LogProcessorInterface
{
    /**
     * @var int Stack trace'te kaç seviye yukarı bakılacak
     */
    private int $level;

    /**
     * @var string[] Atlanacak sınıf isimleri
     */
    private array $skipClassesPartials;

    /**
     * @var string[] Atlanacak namespace'ler
     */
    private array $skipNamespaces;

    /**
     * @param int $level Stack'te kaç seviye yukarı bakılacak
     * @param array $skipClassesPartials Atlanacak sınıf isimleri
     */
    public function __construct(
        int $level = 0,
        array $skipClassesPartials = ['Core\\Logging\\']
    ) {
        $this->level = $level;
        $this->skipClassesPartials = $skipClassesPartials;
        $this->skipNamespaces = array_filter($skipClassesPartials, static function($part) {
            return str_contains($part, '\\');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $i = 0;
        while (isset($trace[$i])) {
            $class = $trace[$i]['class'] ?? '';
            $namespace = substr($class, 0, strrpos($class, '\\') ?: 0);

            if (!$this->isTraceClassSkipped($class, $namespace)) {
                break;
            }

            $i++;
        }

        $i += $this->level;

        $context = $record->context;

        if (isset($trace[$i]['file'])) {
            $context['file'] = $trace[$i]['file'];
        }

        if (isset($trace[$i]['line'])) {
            $context['line'] = $trace[$i]['line'];
        }

        if (isset($trace[$i]['class'])) {
            $context['class'] = $trace[$i]['class'];
        }

        if (isset($trace[$i]['function'])) {
            $context['function'] = $trace[$i]['function'];
        }

        return $record->withContext($context);
    }

    /**
     * Trace'teki sınıfın atlanıp atlanmayacağını kontrol eder.
     */
    private function isTraceClassSkipped(string $class, string $namespace): bool
    {
        if (empty($class)) {
            return false;
        }

        foreach ($this->skipClassesPartials as $skipClass) {
            if (str_contains($class, $skipClass)) {
                return true;
            }
        }

        foreach ($this->skipNamespaces as $skipNamespace) {
            if (str_starts_with($namespace, $skipNamespace)) {
                return true;
            }
        }

        return false;
    }
}