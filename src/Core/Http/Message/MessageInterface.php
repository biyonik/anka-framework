<?php

declare(strict_types=1);

namespace Framework\Core\Http\Message;

use Psr\Http\Message\MessageInterface as PsrMessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP mesajlarının temel davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz, PSR-7 MessageInterface'ini implemente eder ve HTTP mesajlarının
 * (request ve response) ortak özelliklerini tanımlar. HTTP protokol sürümü,
 * headerlar ve body gibi temel bileşenleri yönetir.
 * 
 * Özellikler:
 * - HTTP protokol versiyonu yönetimi
 * - Header yönetimi (ekleme, silme, alma)
 * - Body yönetimi (StreamInterface)
 * - Immutable yapı (withXXX metodları)
 * 
 * @package Framework\Core\Http
 * @subpackage Message
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface MessageInterface extends PsrMessageInterface
{
    /**
     * Mesajın body'sini string olarak döndürür.
     * 
     * Bu metod StreamInterface'in __toString() metodunu kullanır
     * ve tüm içeriği okur. Büyük içerikler için dikkatli kullanılmalıdır.
     * 
     * @return string Mesajın body içeriği
     */
    public function getBodyAsString(): string;

    /**
     * Mesajın body'sini array olarak döndürür.
     * 
     * JSON içeriği parse ederek array'e çevirir. İçerik JSON
     * değilse veya parse edilemezse boş array döner.
     * 
     * @return array<mixed> Parse edilmiş JSON içeriği
     */
    public function getBodyAsArray(): array;

    /**
     * Belirtilen header'ın ilk değerini döndürür.
     * 
     * @param string $name Header adı
     * @param mixed $default Header bulunamazsa dönecek değer
     * @return mixed Header değeri veya default değer
     */
    public function getHeaderLine(string $name, mixed $default = null): mixed;

    /**
     * Header'ın var olup olmadığını kontrol eder.
     * Case-insensitive kontrol yapar.
     * 
     * @param string $name Kontrol edilecek header adı
     * @return bool Header varsa true
     */
    public function hasHeader(string $name): bool;
}