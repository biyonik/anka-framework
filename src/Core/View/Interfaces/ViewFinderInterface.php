<?php

declare(strict_types=1);

namespace Framework\Core\View\Interfaces;

/**
 * View dosyalarını bulan arayüz.
 * 
 * Bu arayüz, view dosyalarının konumlarını belirlemek için kullanılır.
 * View path'lerini yönetme, view dosyalarını bulma ve view namespace'lerini
 * yönetmek için işlevler içerir.
 * 
 * @package Framework\Core\View
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ViewFinderInterface
{
    /**
     * View için dosya yolunu bulur.
     * 
     * @param string $view View adı
     * @return string Dosya yolu
     * @throws \InvalidArgumentException View bulunamazsa
     */
    public function find(string $view): string;

    /**
     * View path'i ekler.
     * 
     * @param string $path View klasör yolu
     * @return static
     */
    public function addPath(string $path): static;

    /**
     * View namespace'i ekler.
     * 
     * @param string $namespace Namespace adı
     * @param string|array<string> $paths View klasör yolu veya yolları
     * @return static
     */
    public function addNamespace(string $namespace, string|array $paths): static;

    /**
     * View için namespace'i ve adını ayırır.
     * 
     * @param string $view View adı
     * @return array{string, string} [namespace, view]
     */
    public function parseNamespaceSegments(string $view): array;

    /**
     * View yollarını döndürür.
     * 
     * @return array<string> View klasör yolları
     */
    public function getPaths(): array;

    /**
     * Tüm view namespace'lerini döndürür.
     * 
     * @return array<string,array<string>> Namespace => paths
     */
    public function getNamespaces(): array;

    /**
     * Namespace için view yollarını döndürür.
     * 
     * @param string $namespace Namespace adı
     * @return array<string> View klasör yolları
     */
    public function getPathsForNamespace(string $namespace): array;

    /**
     * View'ın var olup olmadığını kontrol eder.
     * 
     * @param string $view View adı
     * @return bool View varsa true
     */
    public function exists(string $view): bool;

    /**
     * Önbelleği temizler.
     * 
     * @return void
     */
    public function flushCache(): void;
}