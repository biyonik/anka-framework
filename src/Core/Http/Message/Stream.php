<?php

declare(strict_types=1);

namespace Framework\Core\Http\Message;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * PSR-7 uyumlu Stream implementasyonu.
 * 
 * Bu sınıf, HTTP mesajlarının body'sini temsil eden stream yapısını sağlar.
 * PHP'nin native stream işlevlerini kullanarak dosya ve bellek tabanlı
 * streamleri yönetir.
 * 
 * Özellikler:
 * - Dosya tabanlı stream desteği
 * - Bellek tabanlı (temp) stream desteği
 * - Seekable kontrolleri
 * - Read/Write mod kontrolleri
 * 
 * @package Framework\Core\Http
 * @subpackage Message
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Stream implements StreamInterface
{
    /**
     * Stream resource'u.
     * 
     * @var resource|null
     */
    protected $stream;

    /**
     * Stream boyutu.
     */
    protected ?int $size = null;

    /**
     * Stream'in seekable olup olmadığı.
     */
    protected bool $seekable = false;

    /**
     * Stream'in readable olup olmadığı.
     */
    protected bool $readable = false;

    /**
     * Stream'in writable olup olmadığı.
     */
    protected bool $writable = false;

    /**
     * Stream URI'si.
     */
    protected ?string $uri = null;

    /**
     * Constructor.
     * 
     * @param resource|string|null $content Stream içeriği veya resource
     * @param string $mode Dosya modu
     * @throws InvalidArgumentException İçerik geçersizse
     */
    public function __construct(mixed $content = '', string $mode = 'r+b')
    {
        if (is_string($content)) {
            $resource = fopen('php://temp', $mode);
            if ($resource === false) {
                throw new RuntimeException('Temp stream oluşturulamadı');
            }
            if ($content !== '') {
                fwrite($resource, $content);
                fseek($resource, 0);
            }
            $this->stream = $resource;
        } elseif (is_resource($content)) {
            $this->stream = $content;
        } elseif ($content !== null) {
            throw new InvalidArgumentException(
                'Stream içeriği string, resource veya null olmalıdır'
            );
        }

        if ($this->stream !== null) {
            $this->determineStreamProperties();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if ($this->stream === null) {
            return;
        }

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        $this->size = null;
        $this->uri = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if ($this->stream === null) {
            return null;
        }

        $stats = fstat($this->stream);
        if ($stats === false) {
            return null;
        }

        $this->size = $stats['size'];
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if ($this->stream === null) {
            throw new RuntimeException('Stream detach edilmiş');
        }

        $position = ftell($this->stream);
        if ($position === false) {
            throw new RuntimeException('Stream pozisyonu alınamadı');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        if ($this->stream === null) {
            return true;
        }

        return feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new RuntimeException('Stream seekable değil');
        }

        if ($this->stream === null) {
            throw new RuntimeException('Stream detach edilmiş');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Stream seek işlemi başarısız');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $string): int
    {
        if (!$this->writable) {
            throw new RuntimeException('Stream writable değil');
        }

        if ($this->stream === null) {
            throw new RuntimeException('Stream detach edilmiş');
        }

        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException('Stream write işlemi başarısız');
        }

        $this->size = null;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream readable değil');
        }

        if ($this->stream === null) {
            throw new RuntimeException('Stream detach edilmiş');
        }

        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new RuntimeException('Stream read işlemi başarısız');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream readable değil');
        }

        if ($this->stream === null) {
            throw new RuntimeException('Stream detach edilmiş');
        }

        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Stream contents alınamadı');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(?string $key = null): mixed
    {
        if ($this->stream === null) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    /**
     * Stream özelliklerini belirler.
     */
    protected function determineStreamProperties(): void
    {
        $meta = $this->getMetadata();

        $this->seekable = $meta['seekable'] ?? false;
        $this->readable = (bool)preg_match('/[r+]/', $meta['mode']);
        $this->writable = (bool)preg_match('/[wxa+]/', $meta['mode']);
        $this->uri = $meta['uri'] ?? null;
    }
}