<?php

declare(strict_types=1);

namespace Framework\Core\Application\Bootstrap;

use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Uygulama bootstrap sürecindeki adımları tanımlayan arayüz.
 * 
 * Bu arayüz, uygulama başlangıcında yapılması gereken işlemleri tanımlar.
 * Her bootstrap sınıfı, belirli bir başlangıç adımını gerçekleştirir.
 * 
 * @package Framework\Core\Application
 * @subpackage Bootstrap
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface BootstrapInterface
{
    /**
     * Bootstrap adımını çalıştırır.
     * 
     * @param ApplicationInterface $app Uygulama instance'ı
     * @return void
     */
    public function bootstrap(ApplicationInterface $app): void;

    /**
     * Bootstrap adımının önceliğini döndürür.
     * Düşük sayı, yüksek öncelik anlamına gelir.
     * 
     * @return int Öncelik değeri
     */
    public function getPriority(): int;

    /**
     * Bootstrap adımının çalışıp çalışmayacağını kontrol eder.
     * 
     * @param ApplicationInterface $app Uygulama instance'ı
     * @return bool Çalışacaksa true
     */
    public function shouldRun(ApplicationInterface $app): bool;

    /**
     * Bootstrap adımının environment bazlı çalışıp çalışmayacağını kontrol eder.
     * 
     * @param string $environment Çevre adı
     * @return bool Belirtilen çevrede çalışacaksa true
     */
    public function runsInEnvironment(string $environment): bool;
}