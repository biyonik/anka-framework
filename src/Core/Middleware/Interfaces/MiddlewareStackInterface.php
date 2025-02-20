<?php

declare(strict_types=1);

namespace Framework\Core\Middleware\Interfaces;

/**
 * Middleware stack yönetimini tanımlar.
 *
 * Bu arayüz, middleware'lerin bir stack (yığın) olarak yönetilmesini sağlar.
 * Stack'e middleware ekleme, çıkarma, sıralama ve çalıştırma işlemlerini tanımlar.
 *
 * Özellikler:
 * - Middleware stack yönetimi
 * - Öncelik bazlı sıralama
 * - Grup bazlı middleware yönetimi
 * - Conditional middleware çalıştırma
 *
 * @package Framework\Core\Middleware
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface MiddlewareStackInterface
{
    /**
     * Stack'e middleware ekler.
     *
     * @param MiddlewareInterface $middleware Eklenecek middleware
     * @return static
     */
    public function add(MiddlewareInterface $middleware): static;

    /**
     * Stack'in başına middleware ekler.
     *
     * @param MiddlewareInterface $middleware Eklenecek middleware
     * @return static
     */
    public function prepend(MiddlewareInterface $middleware): static;

    /**
     * Birden fazla middleware ekler.
     *
     * @param MiddlewareInterface[] $middlewares Eklenecek middleware'ler
     * @return static
     */
    public function addMany(array $middlewares): static;

    /**
     * Middleware'leri gruplar.
     *
     * @param string $group Grup adı
     * @param MiddlewareInterface[] $middlewares Gruptaki middleware'ler
     * @return static
     */
    public function group(string $group, array $middlewares): static;

    /**
     * Belirli bir gruptaki middleware'leri döndürür.
     *
     * @param string $group Grup adı
     * @return array<MiddlewareInterface>
     */
    public function getGroup(string $group): array;

    /**
     * Stack'teki tüm middleware'leri döndürür.
     *
     * @return array<MiddlewareInterface>
     */
    public function getAll(): array;

    /**
     * Stack'i öncelik sırasına göre sıralar.
     *
     * @return static
     */
    public function sort(): static;

    /**
     * Stack'i temizler.
     *
     * @return static
     */
    public function clear(): static;

    /**
     * Stack'in boş olup olmadığını kontrol eder.
     *
     * @return bool Stack boşsa true
     */
    public function isEmpty(): bool;

    /**
     * Stack'teki middleware sayısını döndürür.
     *
     * @return int Middleware sayısı
     */
    public function count(): int;
}