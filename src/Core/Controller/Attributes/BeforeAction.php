<?php

declare(strict_types=1);

namespace Framework\Core\Controller\Attributes;

use Attribute;

/**
 * Controller action'larından önce çalışacak metodları tanımlayan attribute.
 * 
 * Bu attribute, controller metodlarına uygulanarak, belirli action'lardan
 * önce otomatik olarak çalışacak metodları belirler. Belirli action'lar
 * için filtreleme imkanı da sunar.
 * 
 * @package Framework\Core\Controller
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * Örnek Kullanım:
 * ```php
 * #[BeforeAction('validateUser', only: ['show', 'edit', 'update'])]
 * public function show($id) { ... }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class BeforeAction
{
    /**
     * Constructor.
     * 
     * @param string $method Çağrılacak metod adı
     * @param array<string> $only Sadece bu action'lar için çalışır
     * @param array<string> $except Bu action'lar hariç hepsi için çalışır
     */
    public function __construct(
        private string $method,
        private array $only = [],
        private array $except = []
    ) {}

    /**
     * Metod adını döndürür.
     * 
     * @return string Metod adı
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Action için çalışıp çalışmayacağını kontrol eder.
     * 
     * @param string $action Kontrol edilecek action
     * @return bool Action için çalışacaksa true
     */
    public function shouldRunForAction(string $action): bool
    {
        // except içinde varsa çalışma
        if (in_array($action, $this->except)) {
            return false;
        }
        
        // only boşsa veya action only içindeyse çalış
        return empty($this->only) || in_array($action, $this->only);
    }

    /**
     * Only action listesini döndürür.
     * 
     * @return array<string> Only action listesi
     */
    public function getOnly(): array
    {
        return $this->only;
    }

    /**
     * Except action listesini döndürür.
     * 
     * @return array<string> Except action listesi
     */
    public function getExcept(): array
    {
        return $this->except;
    }
}