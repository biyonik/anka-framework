<?php

declare(strict_types=1);

namespace Framework\Core\Controller\Attributes;

use Framework\Core\Routing\Attributes\Middleware as RoutingMiddleware;
use Attribute;

/**
 * Controller'lara middleware atamak için kullanılan attribute.
 * 
 * Bu attribute, controller sınıflarına veya metodlarına uygulanarak
 * middleware tanımlamayı sağlar. Routing Middleware'ini extend eder
 * ve controller'lara özel ek özellikler ekler.
 * 
 * @package Framework\Core\Controller
 * @subpackage Attributes
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * Örnek Kullanım:
 * ```php
 * #[Middleware(['auth', 'throttle:60,1'])]
 * class UserController
 * {
 *     #[Middleware('verified')]
 *     public function dashboard() { ... }
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Middleware extends RoutingMiddleware
{
    /**
     * Constructor.
     * 
     * @param string|array<string> $middleware Middleware veya middleware listesi
     * @param array<string> $only Sadece bu action'lar için çalışır
     * @param array<string> $except Bu action'lar hariç hepsi için çalışır
     */
    public function __construct(
        string|array $middleware,
        private array $only = [],
        private array $except = []
    ) {
        parent::__construct($middleware);
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