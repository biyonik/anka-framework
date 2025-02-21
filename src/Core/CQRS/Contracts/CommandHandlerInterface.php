<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Contracts;

/**
 * Command Handler arayüzü.
 *
 * Bu arayüz, tüm Command Handler'ların uygulaması gereken temel metotları tanımlar.
 * Command Handler'lar, Command'leri işleyerek sistem durumunu değiştirirler.
 *
 * @package Framework\Core\CQRS
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T of CommandInterface
 */
interface CommandHandlerInterface
{
    /**
     * Command'i işler ve sonucu döndürür.
     *
     * @param CommandInterface $command İşlenecek command
     * @return mixed İşlem sonucu
     * @throws \Exception Command işleme hatası durumunda
     *
     * @phpstan-param T $command
     */
    public function handle(CommandInterface $command): mixed;

    /**
     * Bu handler'ın işleyebileceği Command tipini döndürür.
     *
     * @return string Command tipi
     *
     * @phpstan-return class-string<T>
     */
    public static function getCommandType(): string;

    /**
     * Command'in işlenebilir olup olmadığını kontrol eder.
     *
     * @param CommandInterface $command Kontrol edilecek command
     * @return bool Command işlenebilirse true
     *
     * @phpstan-param T $command
     */
    public function canHandle(CommandInterface $command): bool;
}