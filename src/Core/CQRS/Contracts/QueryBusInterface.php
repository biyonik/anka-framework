<?php

declare(strict_types=1);

namespace Framework\Core\CQRS\Contracts;

/**
 * Query Bus arayüzü.
 *
 * Bu arayüz, Query'leri ilgili Handler'lara yönlendiren
 * Query Bus bileşeninin arayüzünü tanımlar.
 *
 * @package Framework\Core\CQRS
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface QueryBusInterface
{
    /**
     * Query'i işler.
     *
     * @param QueryInterface $query İşlenecek query
     * @return mixed İşlem sonucu
     * @throws \Exception Query işleme hatası durumunda
     *
     * @template T
     * @phpstan-param QueryInterface $query
     * @phpstan-return T
     */
    public function dispatch(QueryInterface $query): mixed;

    /**
     * Query Handler'ı kaydeder.
     *
     * @param string $queryType Query tipi
     * @param QueryHandlerInterface|string $handler Query handler instance'ı veya sınıf adı
     * @return self
     */
    public function registerHandler(string $queryType, QueryHandlerInterface|string $handler): self;

    /**
     * Query Handler sınıfını kaydeder.
     *
     * @param string $handlerClass Query handler sınıf adı
     * @return self
     */
    public function registerHandlerClass(string $handlerClass): self;

    /**
     * Query Handler middleware ekler.
     *
     * @param callable $middleware Query handler middleware
     * @return self
     */
    public function addMiddleware(callable $middleware): self;
}