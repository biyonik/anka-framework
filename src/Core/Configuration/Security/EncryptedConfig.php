<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Security;

use Framework\Core\Configuration\Contracts\EncryptedConfigInterface;
use RuntimeException;

/**
 * Şifreli konfigürasyon yönetimi.
 *
 * Bu sınıf, hassas konfigürasyon verilerinin şifrelenmesi ve şifre çözülmesi işlemlerini sağlar.
 *
 * @package Framework\Core\Configuration
 * @subpackage Security
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class EncryptedConfig implements EncryptedConfigInterface
{
    /**
     * Şifreleme anahtarı.
     *
     * @var string
     */
    protected string $key;

    /**
     * Şifreli değerlerin öneki.
     *
     * @var string
     */
    protected string $prefix = 'encrypted:';

    /**
     * Şifreleme algoritması.
     *
     * @var string
     */
    protected string $cipher = 'aes-256-cbc';

    /**
     * Constructor.
     *
     * @param string|null $key Şifreleme anahtarı
     */
    public function __construct(?string $key = null)
    {
        if ($key !== null) {
            $this->setKey($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(mixed $value): string
    {
        if (!isset($this->key)) {
            throw new RuntimeException('Şifreleme anahtarı ayarlanmamış');
        }

        // Değeri serialize et
        $serialized = serialize($value);

        // Rastgele IV (Initialization Vector) oluştur
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));

        // Şifrele
        $encrypted = openssl_encrypt(
            $serialized,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        if ($encrypted === false) {
            throw new RuntimeException('Şifreleme işlemi başarısız oldu: ' . openssl_error_string());
        }

        // IV ve şifrelenmiş veriyi birleştir ve base64 ile kodla
        $result = base64_encode($iv . $encrypted);

        // Şifreli değer olduğunu belirtmek için prefix ekle
        return $this->prefix . $result;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $encrypted): mixed
    {
        if (!isset($this->key)) {
            throw new RuntimeException('Şifreleme anahtarı ayarlanmamış');
        }

        // Prefix'i kontrol et ve kaldır
        if (!$this->isEncrypted($encrypted)) {
            throw new RuntimeException('Şifrelenmemiş değer');
        }

        $encrypted = substr($encrypted, strlen($this->prefix));

        // Base64 decode et
        $decoded = base64_decode($encrypted);

        if ($decoded === false) {
            throw new RuntimeException('Geçersiz base64 değeri');
        }

        // IV uzunluğunu al
        $ivLength = openssl_cipher_iv_length($this->cipher);

        // IV ve şifrelenmiş veriyi ayır
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        // Şifreyi çöz
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        if ($decrypted === false) {
            throw new RuntimeException('Şifre çözme işlemi başarısız oldu: ' . openssl_error_string());
        }

        // Deserialize et
        try {
            return unserialize($decrypted, array('allowed_classes' => false));
        } catch (\Throwable $e) {
            throw new RuntimeException('Deserialize işlemi başarısız oldu: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isEncrypted(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, $this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function setKey(string $key): void
    {
        // Anahtarın doğru uzunlukta olmasını sağla
        $this->key = hash('sha256', $key, true);
    }

    /**
     * Şifreli değerlerin önekini ayarlar.
     *
     * @param string $prefix Önek
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Şifreleme algoritmasını ayarlar.
     *
     * @param string $cipher Algoritma
     * @return self
     *
     * @throws RuntimeException Algoritma desteklenmiyorsa
     */
    public function setCipher(string $cipher): self
    {
        if (!in_array($cipher, openssl_get_cipher_methods(), true)) {
            throw new RuntimeException(sprintf('Desteklenmeyen şifreleme algoritması: %s', $cipher));
        }

        $this->cipher = $cipher;
        return $this;
    }

    /**
     * Şifreli değerlerin önekini döndürür.
     *
     * @return string Önek
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Şifreleme algoritmasını döndürür.
     *
     * @return string Algoritma
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }
}