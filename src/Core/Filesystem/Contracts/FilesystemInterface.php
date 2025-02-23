<?php

declare(strict_types=1);

namespace Framework\Core\Filesystem\Contracts;

/**
 * Filesystem operasyonları için temel arayüz.
 *
 * Bu interface, dosya sistemi üzerinde yapılabilecek temel
 * işlemleri tanımlar. Farklı dosya sistemleri (local, ftp vb.)
 * için ortak bir arayüz sağlar.
 *
 * @package Framework\Core\Filesystem
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface FilesystemInterface
{
    /**
     * Dosya var mı kontrol eder.
     *
     * @param string $path Dosya yolu
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Dosya içeriğini okur.
     *
     * @param string $path Dosya yolu
     * @return string
     * @throws \Framework\Core\Filesystem\Exception\FileNotFoundException
     */
    public function get(string $path): string;

    /**
     * Dosyaya içerik yazar.
     *
     * @param string $path Dosya yolu
     * @param string|resource|\Stringable $contents İçerik
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToWriteFileException
     */
    public function put(string $path, string|resource|\Stringable $contents): bool;

    /**
     * Dosyaya içerik ekler.
     *
     * @param string $path Dosya yolu
     * @param string $contents İçerik
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToWriteFileException
     */
    public function append(string $path, string $contents): bool;

    /**
     * Dosyayı siler.
     *
     * @param string|array<string> $paths Dosya yolu veya yolları
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToDeleteFileException
     */
    public function delete(string|array $paths): bool;

    /**
     * Dosyayı kopyalar.
     *
     * @param string $from Kaynak yol
     * @param string $to Hedef yol
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToCopyFileException
     */
    public function copy(string $from, string $to): bool;

    /**
     * Dosyayı taşır.
     *
     * @param string $from Kaynak yol
     * @param string $to Hedef yol
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToMoveFileException
     */
    public function move(string $from, string $to): bool;

    /**
     * Dosyanın boyutunu döndürür.
     *
     * @param string $path Dosya yolu
     * @return int
     * @throws \Framework\Core\Filesystem\Exception\FileNotFoundException
     */
    public function size(string $path): int;

    /**
     * Dosyanın son değiştirilme zamanını döndürür.
     *
     * @param string $path Dosya yolu
     * @return int Unix timestamp
     * @throws \Framework\Core\Filesystem\Exception\FileNotFoundException
     */
    public function lastModified(string $path): int;

    /**
     * Klasör oluşturur.
     *
     * @param string $path Klasör yolu
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToCreateDirectoryException
     */
    public function makeDirectory(string $path): bool;

    /**
     * Klasörü siler.
     *
     * @param string $directory Klasör yolu
     * @return bool
     * @throws \Framework\Core\Filesystem\Exception\UnableToDeleteDirectoryException
     */
    public function deleteDirectory(string $directory): bool;
}