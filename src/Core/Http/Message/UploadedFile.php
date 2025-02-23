<?php

declare(strict_types=1);

namespace Framework\Core\Http\Message;

use Psr\Http\Message\{UploadedFileInterface, StreamInterface};
use InvalidArgumentException;
use RuntimeException;

/**
 * HTTP ile yüklenen dosyaları temsil eden sınıf.
 * 
 * Bu sınıf, PSR-7 UploadedFileInterface'ini implemente eder ve
 * yüklenen dosyaların yönetimi için gerekli metodları sağlar.
 * Dosya taşıma, stream oluşturma ve hata yönetimi özelliklerini içerir.
 * 
 * @package Framework\Core\Http
 * @subpackage Message
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * PHP upload error messages.
     */
    private const ERROR_MESSAGES = [
        UPLOAD_ERR_OK => 'Dosya başarıyla yüklendi.',
        UPLOAD_ERR_INI_SIZE => 'Dosya boyutu php.ini\'deki upload_max_filesize değerini aşıyor.',
        UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu HTML formundaki MAX_FILE_SIZE değerini aşıyor.',
        UPLOAD_ERR_PARTIAL => 'Dosya sadece kısmen yüklendi.',
        UPLOAD_ERR_NO_FILE => 'Hiçbir dosya yüklenmedi.',
        UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör eksik.',
        UPLOAD_ERR_CANT_WRITE => 'Dosya diske yazılamadı.',
        UPLOAD_ERR_EXTENSION => 'Bir PHP eklentisi dosya yüklemesini durdurdu.'
    ];

    /**
     * Dosya stream'i.
     */
    private ?StreamInterface $stream = null;

    /**
     * Dosya taşındı mı?
     */
    private bool $moved = false;

    /**
     * Constructor.
     * 
     * @param string|null $file Dosya yolu
     * @param int $size Dosya boyutu
     * @param int $error Upload hata kodu
     * @param string|null $clientFilename Orijinal dosya adı
     * @param string|null $clientMediaType Dosya MIME tipi
     */
    public function __construct(
        private ?string $file,
        private int $size,
        private int $error,
        private ?string $clientFilename = null,
        private ?string $clientMediaType = null
    ) {
        $this->validateError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        $this->validateActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        if ($this->file === null) {
            throw new RuntimeException('Dosya bulunamadı');
        }

        $this->stream = new Stream(fopen($this->file, 'r'));
        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo(string $targetPath): void
    {
        $this->validateActive();

        if (empty($targetPath)) {
            throw new InvalidArgumentException('Hedef yol boş olamaz');
        }

        if ($this->file === null) {
            throw new RuntimeException('Dosya bulunamadı');
        }

        $targetDirectory = dirname($targetPath);
        if (!is_dir($targetDirectory)) {
            throw new RuntimeException('Hedef dizin mevcut değil');
        }

        if (!is_writable($targetDirectory)) {
            throw new RuntimeException('Hedef dizine yazma izni yok');
        }

        // PHP'nin uploaded_file fonksiyonunu kullan
        if (PHP_SAPI === 'cli') {
            if (!rename($this->file, $targetPath)) {
                throw new RuntimeException('Dosya taşınamadı');
            }
        } else {
            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException('Dosya taşınamadı');
            }
        }

        $this->moved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * Dosya yükleme hata kodunu kontrol eder.
     * 
     * @param int $error Hata kodu
     * @throws InvalidArgumentException Geçersiz hata kodu
     */
    private function validateError(int $error): void
    {
        if (!isset(self::ERROR_MESSAGES[$error])) {
            throw new InvalidArgumentException('Geçersiz hata kodu');
        }
    }

    /**
     * Dosyanın aktif olup olmadığını kontrol eder.
     * 
     * @throws RuntimeException Dosya taşınmışsa
     */
    private function validateActive(): void
    {
        if ($this->moved) {
            throw new RuntimeException('Dosya daha önce taşındı');
        }
    }

    /**
     * Hata mesajını döndürür.
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGES[$this->error] ?? 'Bilinmeyen hata';
    }

    /**
     * Dosyanın başarıyla yüklenip yüklenmediğini kontrol eder.
     */
    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * Dosya uzantısını döndürür.
     */
    public function getExtension(): ?string
    {
        if ($this->clientFilename === null) {
            return null;
        }

        return pathinfo($this->clientFilename, PATHINFO_EXTENSION);
    }

    /**
     * Dosyanın belirtilen uzantılardan birine sahip olup olmadığını kontrol eder.
     * 
     * @param array<string> $extensions İzin verilen uzantılar
     */
    public function hasValidExtension(array $extensions): bool
    {
        $extension = $this->getExtension();
        if ($extension === null) {
            return false;
        }

        return in_array(strtolower($extension), array_map('strtolower', $extensions), true);
    }
}