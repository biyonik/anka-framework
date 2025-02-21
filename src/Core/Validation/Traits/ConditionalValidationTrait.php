<?php

declare(strict_types=1);

namespace Framework\Core\Validation\Traits;

use Framework\Core\Validation\ValidationResult;

/**
 * Koşullu ve çapraz alan doğrulama özellikleri için trait
 *
 * @package Framework\Core\Validation
 * @subpackage Traits
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
trait ConditionalValidationTrait
{
    /**
     * Koşullu doğrulama kuralları
     *
     * @var array<array{field: string, value: mixed, callback: callable}>
     */
    protected array $conditionalRules = [];

    /**
     * Çapraz alan doğrulama kuralları
     *
     * @var array<callable>
     */
    protected array $crossValidators = [];

    /**
     * Performans optimizasyonu için önbellekleme desteği
     *
     * @var array<string, ValidationResult>
     */
    private array $validationCache = [];

    /**
     * Önbellek boyut limiti - performans ve bellek dengesi için
     *
     * @var int
     */
    private int $cacheLimit = 50;

    /**
     * Koşullu doğrulama için dinamik kural ekleme
     *
     * @param string $field Kontrol edilecek alan
     * @param mixed $value Karşılaştırılacak değer
     * @param callable $callback Doğrulama fonksiyonu
     * @return static
     */
    public function when(string $field, mixed $value, callable $callback): static
    {
        $this->conditionalRules[] = [
            'field' => $field,
            'value' => $value,
            'callback' => $callback
        ];
        return $this;
    }

    /**
     * Çapraz alan doğrulaması ekler
     *
     * @param callable $crossValidator Çapraz alan doğrulama fonksiyonu
     * @return static
     */
    public function crossValidate(callable $crossValidator): static
    {
        $this->crossValidators[] = $crossValidator;
        return $this;
    }

    /**
     * Önbellekleme destekli doğrulama
     *
     * @param array<string, mixed> $data Doğrulanacak veri
     * @return ValidationResult
     */
    public function validateWithCache(array $data): ValidationResult
    {
        try {
            // Önbellekleme için benzersiz anahtar oluştur
            $cacheKey = md5(json_encode($data, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            // JSON hata verirse önbellekleme yapmadan devam et
            return $this->validate($data);
        }

        // Önbellekten kontrol et
        if (isset($this->validationCache[$cacheKey])) {
            return $this->validationCache[$cacheKey];
        }

        // Doğrulamayı yap
        $result = $this->validate($data);

        // Önbellek yönetimi - boyut limitini kontrol et
        if (count($this->validationCache) >= $this->cacheLimit) {
            // En eski girdiyi temizle (FIFO)
            array_shift($this->validationCache);
        }

        // Sonucu önbelleğe ekle
        $this->validationCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Önbellek boyut limitini ayarla
     *
     * @param int $limit Önbellek boyut limiti
     * @return static
     */
    public function setCacheLimit(int $limit): static
    {
        $this->cacheLimit = max(1, $limit);
        return $this;
    }

    /**
     * Önbelleği temizle
     *
     * @return static
     */
    public function clearCache(): static
    {
        $this->validationCache = [];
        return $this;
    }
}