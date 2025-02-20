<?php

declare(strict_types=1);

namespace Framework\Core\Http\Request\Traits;

use Framework\Core\Http\Message\Uri;
use InvalidArgumentException;

/**
 * HTTP Request işlemleri için ortak metodları içeren trait.
 * 
 * Bu trait, Request sınıflarında kullanılmak üzere temel request 
 * işlemlerini ve yardımcı metodları sağlar.
 * 
 * @package Framework\Core\Http
 * @subpackage Request\Traits
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
trait RequestTrait
{
    /**
     * Request başlangıç zamanı.
     */
    protected float $requestTime;

    /**
     * Route parametreleri.
     * 
     * @var array<string,mixed>
     */
    protected array $routeParams = [];

    /**
     * Cache'lenmiş input değerleri.
     * 
     * @var array<string,mixed>|null
     */
    protected ?array $cachedInput = null;

    /**
     * Cache'lenmiş JSON değerleri.
     * 
     * @var array<string,mixed>|null
     */
    protected ?array $cachedJson = null;

    /**
     * Request'in başlangıç zamanını ayarlar.
     */
    protected function initRequestTime(): void
    {
        $this->requestTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isXhr(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * {@inheritdoc}
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type');
        return str_contains((string)$contentType, 'application/json');
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure(): bool
    {
        if (isset($_SERVER['HTTPS'])) {
            return $_SERVER['HTTPS'] !== 'off';
        }

        return $this->header('X-Forwarded-Proto') === 'https';
    }

    /**
     * {@inheritdoc}
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }

    /**
     * {@inheritdoc}
     */
    public function getIp(bool $checkProxy = true): string
    {
        if ($checkProxy) {
            $proxiedIp = $this->header('X-Forwarded-For');
            if ($proxiedIp) {
                $ips = explode(',', $proxiedIp);
                return trim($ips[0]);
            }

            $proxiedIp = $this->header('X-Real-IP');
            if ($proxiedIp) {
                return trim($proxiedIp);
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTime(): float
    {
        return $this->requestTime;
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->getParsedBody()[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $key, mixed $default = null): mixed
    {
        if ($this->getMethod() !== 'POST') {
            return $default;
        }

        return $this->getParsedBody()[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->cachedInput === null) {
            $this->cachedInput = $this->getParsedBody() ?? [];
        }

        return $this->cachedInput[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function json(string $key, mixed $default = null): mixed
    {
        if ($this->cachedJson === null) {
            $content = (string)$this->getBody();
            $this->cachedJson = json_decode($content, true) ?? [];
        }

        return $this->cachedJson[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $headers = $this->getHeader($key);
        if (empty($headers)) {
            return $default;
        }

        return $headers[0];
    }

    /**
     * {@inheritdoc}
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->getCookieParams()[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function url(bool $withQuery = true): string
    {
        $uri = $this->getUri();
        $url = $uri->getPath();

        if ($withQuery && $uri->getQuery() !== '') {
            $url .= '?' . $uri->getQuery();
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function fullUrl(bool $withQuery = true): string
    {
        $uri = $this->getUri();
        $url = $uri->getScheme() . '://' . $uri->getAuthority() . $uri->getPath();

        if ($withQuery && $uri->getQuery() !== '') {
            $url .= '?' . $uri->getQuery();
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function path(): string
    {
        return $this->getUri()->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function userAgent(): ?string
    {
        return $this->header('User-Agent');
    }

    /**
     * {@inheritdoc}
     */
    public function referer(): ?string
    {
        return $this->header('Referer');
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(): array
    {
        $accepts = $this->header('Accept');
        if (!$accepts) {
            return [];
        }

        return array_map('trim', explode(',', $accepts));
    }

    /**
     * {@inheritdoc}
     */
    public function accepts_json(): bool
    {
        $accepts = $this->accepts();
        return in_array('application/json', $accepts) || 
               in_array('*/*', $accepts);
    }

    /**
     * {@inheritdoc}
     */
    public function getElapsedTime(): float
    {
        return microtime(true) - $this->requestTime;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParam(string $name, mixed $value): static
    {
        $clone = clone $this;
        $clone->routeParams[$name] = $value;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(mixed $uri, bool $preserveHost = false): static
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        return parent::withUri($uri, $preserveHost);
    }
}