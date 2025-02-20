<?php

declare(strict_types=1);

namespace Framework\Core\Http\Message;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * HTTP mesajları için temel implementasyon.
 * 
 * Bu sınıf, PSR-7 MessageInterface'inin temel implementasyonunu sağlar.
 * Request ve Response sınıfları için ortak davranışları içerir.
 * Immutable bir yapı sunar.
 * 
 * @package Framework\Core\Http
 * @subpackage Message
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * HTTP protokol versiyonu.
     */
    protected string $protocolVersion = '1.1';

    /**
     * HTTP headers.
     * 
     * @var array<string,array<string>>
     */
    protected array $headers = [];

    /**
     * Header isimleri için case mapping.
     * 
     * @var array<string,string>
     */
    protected array $headerNames = [];

    /**
     * Mesaj body'si.
     */
    protected StreamInterface $body;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion(string $version): static
    {
        if (!in_array($version, ['1.0', '1.1', '2.0', '2', '3.0', '3'])) {
            throw new InvalidArgumentException(
                'HTTP protokol versiyonu geçersiz. Desteklenen versiyonlar: 1.0, 1.1, 2.0, 2, 3.0, 3'
            );
        }

        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name): array
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return [];
        }

        $header = $this->headerNames[$normalized];
        return $this->headers[$header];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine(string $name, mixed $default = null): mixed
    {
        $value = $this->getHeader($name);
        if (empty($value)) {
            return $default;
        }

        return implode(', ', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader(string $name, $value): static
    {
        $normalized = strtolower($name);
        $value = $this->normalizeHeaderValue($value);

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader(string $name, $value): static
    {
        $normalized = strtolower($name);
        $value = $this->normalizeHeaderValue($value);

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $new->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name] = $value;
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name): static
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $new = clone $this;
        unset(
            $new->headers[$this->headerNames[$normalized]],
            $new->headerNames[$normalized]
        );

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyAsString(): string
    {
        return (string) $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getBodyAsArray(): array
    {
        $content = $this->getBodyAsString();
        if (empty($content)) {
            return [];
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $data;
    }

    /**
     * Header değerini normalize eder.
     * 
     * @param mixed $value Header değeri
     * @return array<string> Normalize edilmiş değer
     * @throws InvalidArgumentException Değer normalize edilemezse
     */
    protected function normalizeHeaderValue(mixed $value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $normalized = [];
        foreach ($value as $item) {
            if ((!is_numeric($item) && !is_string($item)) || strlen((string) $item) === 0) {
                throw new InvalidArgumentException(
                    'Header değerleri string veya numeric olmalıdır'
                );
            }
            $normalized[] = (string) $item;
        }

        return $normalized;
    }
}