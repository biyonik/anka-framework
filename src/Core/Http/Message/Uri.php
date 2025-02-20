<?php

declare(strict_types=1);

namespace Framework\Core\Http\Message;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

/**
 * PSR-7 uyumlu URI implementasyonu.
 * 
 * Bu sınıf, HTTP URI'lerini temsil eder ve manipüle eder.
 * RFC 3986 standartlarına uygun olarak URI bileşenlerini yönetir.
 * Immutable bir yapı sunar.
 * 
 * Özellikler:
 * - URI bileşenlerinin (scheme, host, port vs.) yönetimi
 * - RFC 3986 standardına uygun encoding/decoding
 * - Path normalizasyonu
 * - Query string manipülasyonu
 * - Authority bileşenlerinin yönetimi
 * 
 * @package Framework\Core\Http
 * @subpackage Message
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Uri implements UriInterface
{
    /**
     * Standart portlar.
     * 
     * @var array<string,int>
     */
    protected const STANDARD_PORTS = [
        'http'  => 80,
        'https' => 443,
        'ftp'   => 21,
    ];

    /**
     * URI şeması (http, https vs.).
     */
    protected string $scheme = '';

    /**
     * Kullanıcı bilgisi.
     */
    protected string $userInfo = '';

    /**
     * Host bilgisi.
     */
    protected string $host = '';

    /**
     * Port numarası.
     */
    protected ?int $port = null;

    /**
     * URI path'i.
     */
    protected string $path = '';

    /**
     * Query string.
     */
    protected string $query = '';

    /**
     * Fragment (hash).
     */
    protected string $fragment = '';

    /**
     * Constructor.
     * 
     * @param string $uri URI string
     */
    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new InvalidArgumentException('URI ayrıştırılamadı');
            }

            $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
            $this->userInfo = $parts['user'] ?? '';
            if (isset($parts['pass'])) {
                $this->userInfo .= ':' . $parts['pass'];
            }
            $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
            $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
            $this->path = $parts['path'] ?? '';
            $this->query = $parts['query'] ?? '';
            $this->fragment = $parts['fragment'] ?? '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null && !$this->isStandardPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->isStandardPort() ? null : $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme(string $scheme): static
    {
        $scheme = strtolower($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;
        $new->port = $new->filterPort($new->port);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo(string $user, ?string $password = null): static
    {
        $info = $user;
        if ($password !== null && $password !== '') {
            $info .= ':' . $password;
        }

        if ($this->userInfo === $info) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $info;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost(string $host): static
    {
        $host = strtolower($host);
        if ($this->host === $host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort(?int $port): static
    {
        $port = $this->filterPort($port);
        if ($this->port === $port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath(string $path): static
    {
        if ($this->path === $path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $this->filterPath($path);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery(string $query): static
    {
        if ($this->query === $query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $this->filterQueryAndFragment($query);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment(string $fragment): static
    {
        if ($this->fragment === $fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $this->filterQueryAndFragment($fragment);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $uri = '';

        // Scheme
        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        // Authority
        $authority = $this->getAuthority();
        if ($authority !== '' || $this->scheme === 'file') {
            $uri .= '//' . $authority;
        }

        // Path
        $uri .= $this->path;

        // Query
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        // Fragment
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * Port numarasını filtreler.
     * 
     * @param int|null $port Port numarası
     * @return int|null Filtrelenmiş port
     * @throws InvalidArgumentException Port geçersizse
     */
    protected function filterPort(?int $port): ?int
    {
        if ($port === null) {
            return null;
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(
                'Port numarası 1-65535 arasında olmalıdır'
            );
        }

        return $port;
    }

    /**
     * Path'i filtreler.
     * 
     * @param string $path Path
     * @return string Filtrelenmiş path
     */
    protected function filterPath(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::class . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            fn(array $matches) => rawurlencode($matches[0]),
            $path
        );
    }

    /**
     * Query ve fragment'ı filtreler.
     * 
     * @param string $str Query veya fragment
     * @return string Filtrelenmiş string
     */
    protected function filterQueryAndFragment(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::class . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            fn(array $matches) => rawurlencode($matches[0]),
            $str
        );
    }

    /**
     * Mevcut port numarasının standart olup olmadığını kontrol eder.
     * 
     * @return bool Standart ise true
     */
    protected function isStandardPort(): bool
    {
        if ($this->port === null) {
            return true;
        }

        return isset(self::STANDARD_PORTS[$this->scheme]) 
            && $this->port === self::STANDARD_PORTS[$this->scheme];
    }
}