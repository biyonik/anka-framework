<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Contracts;

/**
 * Command Bus arayüzü.
 *
 * Bu arayüz, Command'leri ilgili Handler'lara yönlendiren
 * Command Bus bileşeninin arayüzünü tanımlar.
 *
 * @package Framework\Core\CQRS
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface CommandBusInterface
{
    /**
     * Command'i işler.
     *
     * @param CommandInterface $command İşlenecek command
     * @return mixed İşlem sonucu
     * @throws \Exception Command işleme hatası durumunda
     */
    public function dispatch(CommandInterface $command): mixed;

    /**
     * Command Handler'ı kaydeder.
     *
     * @param string $commandType Command tipi
     * @param CommandHandlerInterface|string $handler Command handler instance'ı veya sınıf adı
     * @return self
     */
    public function registerHandler(string $commandType, CommandHandlerInterface|string $handler): self;

    /**
     * Command Handler sınıfını kaydeder.
     *
     * @param string $handlerClass Command handler sınıf adı
     * @return self
     */
    public function registerHandlerClass(string $handlerClass): self;

    /**
     * Command Handler middleware ekler.
     *
     * @param callable $middleware Command handler middleware
     * @return self
     */
    public function addMiddleware(callable $middleware): self;
}