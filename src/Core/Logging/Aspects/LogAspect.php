<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Aspects;

use Framework\Core\Aspects\AbstractAspect;
use Framework\Core\Aspects\Attributes\Around;
use Framework\Core\Aspects\JoinPoint;
use Framework\Core\Logging\Attributes\Log;
use Framework\Core\Logging\Contracts\LoggerInterface;

/**
 * Log attribute'unu işleyen aspect.
 *
 * @package Framework\Core\Logging
 * @subpackage Aspects
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class LogAspect extends AbstractAspect
{
    /**
     * @var LoggerInterface Logger objesi
     */
    protected LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger Logger objesi
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct('logging.aspect', 100); // En son çalışsın, öncelik 100
        $this->logger = $logger;
    }

    /**
     * Log attribute'una sahip metotları yakalar ve çalıştırma öncesi/sonrası loglama yapar.
     */
    #[Around(Log::class)]
    public function logMethod(JoinPoint $joinPoint): mixed
    {
        $reflection = $joinPoint->getReflection();
        $attribute = $reflection->getAttributes(Log::class)[0] ?? null;

        if (!$attribute) {
            return $joinPoint->proceed();
        }

        $log = $attribute->newInstance();
        $logger = $this->logger;

        if ($log->channel) {
            $logger = $logger->channel($log->channel);
        }

        $className = $reflection->getDeclaringClass()->getName();
        $methodName = $reflection->getName();

        $message = $log->message ?? "Calling {$className}::{$methodName}";
        $context = [];

        if ($log->logParams) {
            $context['params'] = $joinPoint->getArguments();
        }

        // Metot çağrısından önce loğla
        $logger->log($log->level, "{$message}:start", $context);

        $startTime = microtime(true);
        $exception = null;
        $result = null;

        try {
            $result = $joinPoint->proceed();
            return $result;
        } catch (\Throwable $e) {
            $exception = $e;
            throw $e;
        } finally {
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // ms cinsinden

            $finalContext = array_merge($context, [
                'execution_time_ms' => $executionTime,
            ]);

            if ($exception) {
                $finalContext['exception'] = [
                    'class' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];

                $logger->log($log->level, "{$message}:error", $finalContext);
            } else {
                if ($log->logReturn && isset($result)) {
                    $finalContext['result'] = $this->sanitizeResult($result);
                }

                $logger->log($log->level, "{$message}:end", $finalContext);
            }
        }
    }

    /**
     * Dönüş değerini loglama için hazırlar.
     */
    protected function sanitizeResult(mixed $result): mixed
    {
        if (is_scalar($result) || is_null($result) || is_array($result)) {
            return $result;
        }

        if (is_object($result)) {
            if (method_exists($result, 'toArray')) {
                return $result->toArray();
            }

            if (method_exists($result, 'jsonSerialize')) {
                return $result->jsonSerialize();
            }

            if (method_exists($result, '__toString')) {
                return (string) $result;
            }

            return sprintf('[object %s]', get_class($result));
        }

        if (is_resource($result)) {
            return sprintf('[resource %s]', get_resource_type($result));
        }

        return '[unserializable]';
    }
}