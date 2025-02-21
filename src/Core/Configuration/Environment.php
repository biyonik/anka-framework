<?php

declare(strict_types=1);

namespace Framework\Core\Configuration;

use Framework\Core\Configuration\Contracts\EnvironmentInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Çevre (environment) yönetim sınıfı.
 *
 * Bu sınıf, .env dosyalarını okuyarak veya doğrudan PHP çevre değişkenlerini
 * kullanarak uygulama çevre verilerini yönetir.
 *
 * @package Framework\Core\Configuration
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Environment implements EnvironmentInterface
{
    /**
     * Çevre değişkenlerini tutan array.
     *
     * @var array<string, mixed>
     */
    protected array $variables = [];

    /**
     * Mevcut çevre adı.
     *
     * @var string
     */
    protected string $environment;

    /**
     * Constructor.
     *
     * @param string $environment Varsayılan çevre adı
     */
    public function __construct(string $environment = 'production')
    {
        $this->environment = $environment;

        // PHP'nin kendi çevre değişkenlerini yükle
        $this->variables = $_ENV;

        // APP_ENV veya ENVIRONMENT değişkeni varsa, çevre adını güncelle
        if (isset($_ENV['APP_ENV'])) {
            $this->environment = $_ENV['APP_ENV'];
        } elseif (isset($_ENV['ENVIRONMENT'])) {
            $this->environment = $_ENV['ENVIRONMENT'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->variables[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value): void
    {
        $this->variables[$key] = $value;

        // Eğer çevre değişkeni APP_ENV veya ENVIRONMENT ise, çevre adını da güncelle
        if ($key === 'APP_ENV' || $key === 'ENVIRONMENT') {
            $this->environment = (string) $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return isset($this->variables[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function is(string|array $environment): bool
    {
        if (is_array($environment)) {
            return in_array($this->environment, $environment, true);
        }

        return $this->environment === $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('Çevre değişkenleri dosyası bulunamadı: %s', $path));
        }

        // .env dosyasını satır satır oku
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException(sprintf('Çevre değişkenleri dosyası okunamadı: %s', $path));
        }

        foreach ($lines as $line) {
            // Yorum satırlarını atla
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Anahtar ve değeri ayır
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Tırnak işaretlerini temizle
                $value = trim($value, '"\'');

                // Değeri PHP çevre değişkenlerine ekle
                $this->variables[$key] = $value;

                // Ayrıca putenv() ile PHP çevre değişkenine ekle (opsiyonel)
                putenv("{$key}={$value}");
            }
        }

        // APP_ENV veya ENVIRONMENT değişkeni varsa, çevre adını güncelle
        if (isset($this->variables['APP_ENV'])) {
            $this->environment = $this->variables['APP_ENV'];
        } elseif (isset($this->variables['ENVIRONMENT'])) {
            $this->environment = $this->variables['ENVIRONMENT'];
        }
    }
}