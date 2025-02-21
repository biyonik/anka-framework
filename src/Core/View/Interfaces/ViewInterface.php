<?php

declare(strict_types=1);

namespace Framework\Core\View\Interfaces;

/**
 * View nesnelerinin davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz, framework'ün view nesneleri için temel davranışları tanımlar.
 * View nesnesinin veri yönetimi, render edilmesi ve içerik manipülasyonu
 * işlevlerini içerir.
 * 
 * @package Framework\Core\View
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ViewInterface
{
    /**
     * View adını döndürür.
     * 
     * @return string View adı
     */
    public function getName(): string;

    /**
     * View verilerini döndürür.
     * 
     * @return array<string,mixed> View verileri
     */
    public function getData(): array;

    /**
     * View'a veri ekler.
     * 
     * @param string $key Veri anahtarı
     * @param mixed $value Veri değeri
     * @return static
     */
    public function with(string $key, mixed $value): static;

    /**
     * View'a birden fazla veri ekler.
     * 
     * @param array<string,mixed> $data Eklenecek veriler
     * @return static
     */
    public function withMany(array $data): static;

    /**
     * View'ı render eder ve içeriğini döndürür.
     * 
     * @return string Render edilmiş içerik
     */
    public function render(): string;

    /**
     * View layout'unu ayarlar.
     * 
     * @param string $layout Layout adı
     * @return static
     */
    public function withLayout(string $layout): static;

    /**
     * View layout'unu döndürür.
     * 
     * @return string|null Layout adı
     */
    public function getLayout(): ?string;

    /**
     * View'ı başka bir view içerisine ekler (nest).
     * 
     * @param string $section Eklenecek bölüm adı
     * @return static
     */
    public function section(string $section): static;

    /**
     * Global olarak paylaşılan bir veriyi döndürür.
     * 
     * @param string $key Veri anahtarı
     * @param mixed $default Varsayılan değer
     * @return mixed Veri değeri
     */
    public function shared(string $key, mixed $default = null): mixed;

    /**
     * View for toString (echo için).
     * 
     * @return string Render edilmiş içerik
     */
    public function __toString(): string;
}