<?php

declare(strict_types=1);

namespace Framework\Core\View\Interfaces;

/**
 * View engine'lerin davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz, view engine'ler için temel davranışları tanımlar.
 * Engine'ler view şablonlarını render etmek, önbelleğini yönetmek
 * ve view bileşenlerini işlemekten sorumludur.
 * 
 * @package Framework\Core\View
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ViewEngineInterface
{
    /**
     * View şablonunu render eder.
     * 
     * @param string $path Şablon dosya yolu
     * @param array<string,mixed> $data Şablona geçirilecek veriler
     * @return string Render edilmiş içerik
     */
    public function render(string $path, array $data = []): string;

    /**
     * Şablonun var olup olmadığını kontrol eder.
     * 
     * @param string $path Şablon dosya yolu
     * @return bool Şablon varsa true
     */
    public function exists(string $path): bool;

    /**
     * View'a global veri ekler.
     * 
     * @param string $key Veri anahtarı
     * @param mixed $value Veri değeri
     * @return static
     */
    public function share(string $key, mixed $value): static;

    /**
     * View'a birden fazla global veri ekler.
     * 
     * @param array<string,mixed> $data Eklenecek veriler
     * @return static
     */
    public function shareMany(array $data): static;

    /**
     * Global olarak paylaşılan veriyi döndürür.
     * 
     * @param string $key Veri anahtarı
     * @param mixed $default Varsayılan değer
     * @return mixed Veri değeri
     */
    public function getShared(string $key, mixed $default = null): mixed;

    /**
     * Tüm global verileri döndürür.
     * 
     * @return array<string,mixed> Global veriler
     */
    public function getAllShared(): array;

    /**
     * Önbelleği temizler.
     * 
     * @return void
     */
    public function flushCache(): void;

    /**
     * View bileşenini render eder.
     * 
     * @param string $component Bileşen adı
     * @param array<string,mixed> $data Bileşene geçirilecek veriler
     * @return string Render edilmiş bileşen
     */
    public function renderComponent(string $component, array $data = []): string;

    public function getFinder(): ViewFinderInterface;
}