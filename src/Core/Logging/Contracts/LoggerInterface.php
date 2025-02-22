<?php

declare(strict_types=1);

namespace Framework\Core\Logging\Contracts;

/**
 * Framework için gelişmiş loglama arayüzü.
 * PSR-3 uyumlu ama ekstra metodlar eklenmiş.
 *
 * @package Framework\Core\Logging
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    /**
     * Belirli bir bağlamla loggerı klonlar.
     *
     * @param array $context Bağlam bilgileri
     * @return self Yeni logger instance'ı
     */
    public function withContext(array $context): self;

    /**
     * Belirli bir formatterla loggerı klonlar.
     *
     * @param LogFormatterInterface $formatter Kullanılacak formatter
     * @return self Yeni logger instance'ı
     */
    public function withFormatter(LogFormatterInterface $formatter): self;

    /**
     * Belirli bir kanal için loggerı klonlar.
     *
     * @param string $channel Kanal adı
     * @return self Yeni logger instance'ı
     */
    public function channel(string $channel): self;

    /**
     * Belirli bir seviyede loglamanın aktif olup olmadığını kontrol eder.
     *
     * @param string|int $level Log seviyesi
     * @return bool Log seviyesi aktif mi
     */
    public function isEnabled($level): bool;
}