<?php

declare(strict_types=1);

namespace Framework\Core\Http\Response\Traits;

use InvalidArgumentException;
use RuntimeException;

/**
 * HTTP Response işlemleri için ortak metodları içeren trait.
 * 
 * Bu trait, Response sınıflarında kullanılmak üzere temel response
 * işlemlerini ve yardımcı metodları sağlar.
 * 
 * @package Framework\Core\Http
 * @subpackage Response\Traits
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
trait ResponseTrait
{
    /**
     * Response gönderildi mi?
     */
    protected bool $sent = false;

    /**
     * Cookie'ler.
     * 
     * @var array<string,array{
     *  value: string,
     *  options: array<string,mixed>
     * }>
     */
    protected array $cookies = [];

    /**
     * Status code açıklamaları.
     * 
     * @var array<int,string>
     */
    protected static array $phrases = [
        // 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    /**
     * {@inheritdoc}
     */
    public function withJson(mixed $data, int $status = 200, int $flags = 0, int $depth = 512): static
    {
        $json = json_encode($data, $flags, $depth);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON encode hatası: ' . json_last_error_msg());
        }

        $response = $this
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);

        $response->getBody()->write($json);
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withFile(string $path, ?string $name = null, ?string $contentType = null): static
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Dosya bulunamadı: ' . $path);
        }

        $contentType = $contentType ?? mime_content_type($path) ?? 'application/octet-stream';
        $name = $name ?? basename($path);

        $response = $this
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', 'inline; filename="' . $name . '"');

        $response->getBody()->write(file_get_contents($path));
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withDownload(string $path, ?string $name = null, ?string $contentType = null): static
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Dosya bulunamadı: ' . $path);
        }

        $contentType = $contentType ?? mime_content_type($path) ?? 'application/octet-stream';
        $name = $name ?? basename($path);

        $response = $this
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->withHeader('Content-Length', (string) filesize($path))
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');

        $response->getBody()->write(file_get_contents($path));
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withRedirect(string $url, int $status = 302): static
    {
        return $this
            ->withStatus($status)
            ->withHeader('Location', $url);
    }

    /**
     * {@inheritdoc}
     */
    public function withCookie(string $name, string $value, array $options = []): static
    {
        $new = clone $this;
        $new->cookies[$name] = [
            'value' => $value,
            'options' => $options
        ];
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutCookie(string $name, array $options = []): static
    {
        return $this->withCookie($name, '', array_merge($options, ['expires' => 1]));
    }

    /**
     * {@inheritdoc}
     */
    public function withCache(int|string $value): static
    {
        if (is_int($value)) {
            return $this->withHeader('Cache-Control', 'max-age=' . $value . ', public');
        }

        return $this->withHeader('Cache-Control', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withNoCache(): static
    {
        return $this
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    }

    /**
     * {@inheritdoc}
     */
    public function withCors(array $options = []): static
    {
        $defaults = [
            'allowedOrigins' => ['*'],
            'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowedHeaders' => ['*'],
            'exposedHeaders' => [],
            'maxAge' => 0,
            'allowCredentials' => false
        ];

        $options = array_merge($defaults, $options);
        $response = $this;

        // Basic CORS
        if (in_array('*', $options['allowedOrigins'])) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        } else {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if (in_array($origin, $options['allowedOrigins'])) {
                $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
                $response = $response->withHeader('Vary', 'Origin');
            }
        }

        // Methods
        if (!empty($options['allowedMethods'])) {
            $response = $response->withHeader(
                'Access-Control-Allow-Methods',
                implode(', ', $options['allowedMethods'])
            );
        }

        // Headers
        if (!empty($options['allowedHeaders'])) {
            $response = $response->withHeader(
                'Access-Control-Allow-Headers',
                implode(', ', $options['allowedHeaders'])
            );
        }

        // Exposed Headers
        if (!empty($options['exposedHeaders'])) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $options['exposedHeaders'])
            );
        }

        // Max Age
        if ($options['maxAge'] > 0) {
            $response = $response->withHeader(
                'Access-Control-Max-Age',
                (string) $options['maxAge']
            );
        }

        // Credentials
        if ($options['allowCredentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function send(): void
    {
        if ($this->sent) {
            throw new RuntimeException('Response zaten gönderildi');
        }

        // Send status line
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );
        header($statusLine, true);

        // Send headers
        foreach ($this->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        // Send cookies
        foreach ($this->cookies as $name => $cookie) {
            setcookie($name, $cookie['value'], $cookie['options']);
        }

        // Send body
        if ($this->getBody()->isSeekable()) {
            $this->getBody()->rewind();
        }
        
        echo $this->getBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        $this->sent = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Status code için açıklama döndürür.
     * 
     * @param int $code HTTP status code
     * @return string Açıklama
     */
    protected static function getPhrase(int $code): string
    {
        return static::$phrases[$code] ?? '';
    }
}