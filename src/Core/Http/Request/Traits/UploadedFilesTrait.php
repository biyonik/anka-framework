<?php

declare(strict_types=1);

namespace Framework\Core\Http\Request\Traits;

use Framework\Core\Http\Message\UploadedFile;
use InvalidArgumentException;

/**
 * Yüklenen dosyaların yönetimini sağlayan trait.
 * 
 * Bu trait, HTTP request'leri ile yüklenen dosyaların işlenmesi ve
 * yönetimi için gerekli metodları sağlar.
 * 
 * @package Framework\Core\Http
 * @subpackage Request\Traits
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
trait UploadedFilesTrait
{
    /**
     * Cache'lenmiş normalleştirilmiş dosyalar.
     * 
     * @var array<string,UploadedFile>|null
     */
    protected ?array $normalizedFiles = null;

    /**
     * {@inheritdoc}
     */
    public function file(string $key): ?UploadedFile
    {
        if ($this->normalizedFiles === null) {
            $this->normalizedFiles = $this->normalizeFiles($_FILES);
        }

        return $this->normalizedFiles[$key] ?? null;
    }

    /**
     * Yüklenen dosyaları normalize eder.
     * 
     * @param array<string,mixed> $files $_FILES array
     * @return array<string,UploadedFile> Normalize edilmiş dosyalar
     */
    protected function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($this->isFileArray($value)) {
                $normalized[$key] = $this->createUploadedFile($value);
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = $this->normalizeNestedFiles($value);
                continue;
            }
        }

        return $normalized;
    }

    /**
     * İç içe dosya yapısını normalize eder.
     * 
     * @param array<string,mixed> $files Dosya array'i
     * @return array<string,UploadedFile|array> Normalize edilmiş dosyalar
     */
    protected function normalizeNestedFiles(array $files): array|UploadedFile
    {
        if ($this->isFileArray($files)) {
            return $this->createUploadedFile($files);
        }

        $normalized = [];
        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = $this->normalizeNestedFiles($value);
            }
        }

        return $normalized;
    }

    /**
     * Array'in bir dosya array'i olup olmadığını kontrol eder.
     * 
     * @param array<string,mixed> $file Kontrol edilecek array
     */
    protected function isFileArray(array $file): bool
    {
        $keys = ['tmp_name', 'name', 'type', 'size', 'error'];
        return count(array_intersect_key(array_flip($keys), $file)) === count($keys);
    }

    /**
     * Yeni bir UploadedFile instance'ı oluşturur.
     * 
     * @param array<string,mixed> $file Dosya bilgileri
     */
    protected function createUploadedFile(array $file): UploadedFile
    {
        if (is_array($file['tmp_name'])) {
            return $this->normalizeNestedFiles($file);
        }

        return new UploadedFile(
            $file['tmp_name'],
            (int) $file['size'],
            (int) $file['error'],
            $file['name'],
            $file['type']
        );
    }

    /**
     * Birden çok dosyayı gruplar.
     * 
     * @param array<string,mixed> $files Dosya array'i
     * @return array<string,array> Gruplanmış dosyalar
     */
    protected function groupFiles(array $files): array
    {
        $grouped = [];

        foreach ($files as $key => $value) {
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'name' => [],
                    'type' => [],
                    'tmp_name' => [],
                    'error' => [],
                    'size' => []
                ];
            }

            $this->groupFileValue($grouped[$key], $value);
        }

        return $grouped;
    }

    /**
     * Dosya değerlerini gruplar.
     * 
     * @param array<string,array> $grouped Grup array'i
     * @param mixed $value Dosya değeri
     */
    protected function groupFileValue(array &$grouped, mixed $value): void
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (!isset($grouped[$k])) {
                    $grouped[$k] = [];
                }
                $this->groupFileValue($grouped[$k], $v);
            }
            return;
        }

        $grouped[] = $value;
    }
}