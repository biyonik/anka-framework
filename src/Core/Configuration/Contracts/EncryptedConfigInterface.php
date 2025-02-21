<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Contracts;

/**
 * Şifreli konfigürasyon yönetim arayüzü.
 *
 * Bu arayüz, hassas konfigürasyon verilerinin şifrelenmesi ve
 * şifresinin çözülmesi işlemlerini sağlar.
 *
 * @package Framework\Core\Configuration
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface EncryptedConfigInterface
{
    /**
     * Bir konfigürasyon değerini şifreler.
     *
     * @param mixed $value Şifrelenecek değer
     * @return string Şifrelenmiş değer
     */
    public function encrypt(mixed $value): string;

    /**
     * Şifrelenmiş bir konfigürasyon değerinin şifresini çözer.
     *
     * @param string $encrypted Şifrelenmiş değer
     * @return mixed Çözülmüş değer
     */
    public function decrypt(string $encrypted): mixed;

    /**
     * Bir değerin şifrelenmiş olup olmadığını kontrol eder.
     *
     * @param mixed $value Kontrol edilecek değer
     * @return bool Değer şifreliyse true
     */
    public function isEncrypted(mixed $value): bool;

    /**
     * Şifreleme anahtarını ayarlar.
     *
     * @param string $key Şifreleme anahtarı
     * @return void
     */
    public function setKey(string $key): void;
}