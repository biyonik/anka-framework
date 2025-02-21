<?php

declare(strict_types=1);

namespace Framework\Core\Event\Contracts;

/**
 * Listener arayüzü.
 *
 * Bu arayüz, olay dinleyicilerin uygulaması gereken metotları tanımlar.
 *
 * @package Framework\Core\Event
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ListenerInterface
{
    /**
     * Dinleyicinin handle edebileceği olay tipi veya tipleri.
     *
     * @return string|array<string> Olay tipi veya tipleri
     */
    public static function getSubscribedEvents(): string|array;

    /**
     * Olayı işler.
     *
     * @param EventInterface $event İşlenen olay
     * @return void
     */
    public function handle(EventInterface $event): void;

    /**
     * Listener'ın öncelik değerini döndürür.
     * Düşük değerler daha yüksek önceliğe sahiptir.
     *
     * @return int Öncelik değeri
     */
    public function getPriority(): int;

    /**
     * Listener'ın propagasyonu durdurması gerekip gerekmediğini belirtir.
     * True dönerse, bu listener'dan sonraki listener'lar çalıştırılmaz.
     *
     * @return bool Propagasyon durdurulacaksa true
     */
    public function stopsPropagation(): bool;
}