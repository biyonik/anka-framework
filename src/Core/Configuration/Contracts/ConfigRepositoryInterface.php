<?php

declare(strict_types=1);

namespace Framework\Core\Configuration\Contracts;

/**
 * Konfigürasyon depo arayüzü.
 *
 * Bu arayüz, ConfigurationInterface'i genişleterek daha fazla özellik ekler.
 * Çevre bazlı konfigürasyon yönetimi, şifreleme ve önbellekleme işlemlerini entegre eder.
 *
 * @package Framework\Core\Configuration
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ConfigRepositoryInterface extends ConfigurationInterface
{
    /**
     * Çevre yöneticisi nesnesini döndürür.
     *
     * @return EnvironmentInterface Çevre yöneticisi
     */
    public function getEnvironment(): EnvironmentInterface;

    /**
     * Çevre yöneticisi nesnesini ayarlar.
     *
     * @param EnvironmentInterface $environment Çevre yöneticisi
     * @return self Akıcı arayüz için
     */
    public function setEnvironment(EnvironmentInterface $environment): self;

    /**
     * Konfigürasyon yükleyici nesnesini döndürür.
     *
     * @return ConfigLoaderInterface Konfigürasyon yükleyici
     */
    public function getLoader(): ConfigLoaderInterface;

    /**
     * Konfigürasyon yükleyici nesnesini ayarlar.
     *
     * @param ConfigLoaderInterface $loader Konfigürasyon yükleyici
     * @return self Akıcı arayüz için
     */
    public function setLoader(ConfigLoaderInterface $loader): self;

    /**
     * Önbellek nesnesini döndürür.
     *
     * @return ConfigCacheInterface|null Önbellek nesnesi
     */
    public function getCache(): ?ConfigCacheInterface;

    /**
     * Önbellek nesnesini ayarlar.
     *
     * @param ConfigCacheInterface|null $cache Önbellek nesnesi
     * @return self Akıcı arayüz için
     */
    public function setCache(?ConfigCacheInterface $cache): self;

    /**
     * Şifreleme nesnesini döndürür.
     *
     * @return EncryptedConfigInterface|null Şifreleme nesnesi
     */
    public function getEncrypter(): ?EncryptedConfigInterface;

    /**
     * Şifreleme nesnesini ayarlar.
     *
     * @param EncryptedConfigInterface|null $encrypter Şifreleme nesnesi
     * @return self Akıcı arayüz için
     */
    public function setEncrypter(?EncryptedConfigInterface $encrypter): self;

    /**
     * Konfigürasyon verilerini belirtilen dosyadan yükler.
     *
     * @param string $path Konfigürasyon dosyasının yolu
     * @param bool $merge Mevcut verilerle birleştirilsin mi?
     * @return self Akıcı arayüz için
     */
    public function loadFromFile(string $path, bool $merge = true): self;

    /**
     * Konfigürasyon verilerini bir dizinden yükler.
     *
     * @param string $directory Konfigürasyon dizin yolu
     * @param string $extension Dosya uzantısı
     * @param bool $merge Mevcut verilerle birleştirilsin mi?
     * @return self Akıcı arayüz için
     */
    public function loadFromDirectory(string $directory, string $extension = 'php', bool $merge = true): self;

    /**
     * Önbelleği yeniler.
     *
     * @return bool İşlem başarılıysa true
     */
    public function refreshCache(): bool;

    /**
     * Belirtilen anahtarın şifreli değerini döndürür.
     *
     * @param string $key Konfigürasyon anahtarı
     * @return string|null Şifreli değer
     */
    public function getEncrypted(string $key): ?string;

    /**
     * Belirtilen anahtara şifreli bir değer atar.
     *
     * @param string $key Konfigürasyon anahtarı
     * @param mixed $value Şifrelenecek değer
     * @return void
     */
    public function setEncrypted(string $key, mixed $value): void;
}