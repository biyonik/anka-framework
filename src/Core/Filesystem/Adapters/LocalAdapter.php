<?php

declare(strict_types=1);

namespace Framework\Core\Filesystem\Adapters;

use Framework\Core\Filesystem\Contracts\FilesystemInterface;
use Framework\Core\Filesystem\Exception\{
    FileNotFoundException,
    UnableToWriteFileException,
    UnableToDeleteFileException,
    UnableToCopyFileException,
    UnableToMoveFileException,
    UnableToCreateDirectoryException,
    UnableToDeleteDirectoryException
};

/**
 * Yerel dosya sistemi için filesystem adapter'ı.
 *
 * Bu sınıf, yerel dosya sistemi üzerinde temel dosya operasyonlarını
 * gerçekleştirir. FilesystemInterface'i implemente eder.
 *
 * @package Framework\Core\Filesystem
 * @subpackage Adapters
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
readonly class LocalAdapter implements FilesystemInterface
{
    /**
     * Constructor.
     *
     * @param string $root Kök dizin
     */
    public function __construct(
        private string $root
    ) {
        $this->ensureDirectoryExists($root);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        return file_exists($this->fullPath($path));
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): string
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException("File not found at path: {$path}");
        }

        $contents = file_get_contents($this->fullPath($path));

        if ($contents === false) {
            throw new FileNotFoundException("Unable to read file at path: {$path}");
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $contents): bool
    {
        $fullPath = $this->fullPath($path);
        $directory = dirname($fullPath);

        $this->ensureDirectoryExists($directory);

        // Eğer içerik Stringable ise, toString() metodunu kullan
        if ($contents instanceof \Stringable) {
            $contents = (string) $contents;
        }

        // Resource ise içeriği oku
        if (is_resource($contents)) {
            $contents = stream_get_contents($contents);
            if ($contents === false) {
                throw new UnableToWriteFileException("Unable to read from resource");
            }
        }

        if (file_put_contents($fullPath, $contents) === false) {
            throw new UnableToWriteFileException("Unable to write to file at path: {$path}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function append(string $path, string $contents): bool
    {
        $fullPath = $this->fullPath($path);
        $directory = dirname($fullPath);

        $this->ensureDirectoryExists($directory);

        if (file_put_contents($fullPath, $contents, FILE_APPEND) === false) {
            throw new UnableToWriteFileException("Unable to append to file at path: {$path}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string|array $paths): bool
    {
        $paths = is_array($paths) ? $paths : [$paths];
        $success = true;

        foreach ($paths as $path) {
            try {
                if (!$this->exists($path)) {
                    continue;
                }

                if (!unlink($this->fullPath($path))) {
                    throw new UnableToDeleteFileException("Unable to delete file at path: {$path}");
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $from, string $to): bool
    {
        if (!$this->exists($from)) {
            throw new FileNotFoundException("Source file not found at path: {$from}");
        }

        $directory = dirname($this->fullPath($to));
        $this->ensureDirectoryExists($directory);

        if (!copy($this->fullPath($from), $this->fullPath($to))) {
            throw new UnableToCopyFileException("Unable to copy file from {$from} to {$to}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $from, string $to): bool
    {
        if (!$this->exists($from)) {
            throw new FileNotFoundException("Source file not found at path: {$from}");
        }

        $directory = dirname($this->fullPath($to));
        $this->ensureDirectoryExists($directory);

        if (!rename($this->fullPath($from), $this->fullPath($to))) {
            throw new UnableToMoveFileException("Unable to move file from {$from} to {$to}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function size(string $path): int
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException("File not found at path: {$path}");
        }

        $size = filesize($this->fullPath($path));

        if ($size === false) {
            throw new FileNotFoundException("Unable to get file size at path: {$path}");
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): int
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException("File not found at path: {$path}");
        }

        $time = filemtime($this->fullPath($path));

        if ($time === false) {
            throw new FileNotFoundException("Unable to get last modified time at path: {$path}");
        }

        return $time;
    }

    /**
     * {@inheritdoc}
     */
    public function makeDirectory(string $path): bool
    {
        $fullPath = $this->fullPath($path);

        if (is_dir($fullPath)) {
            return true;
        }

        if (!mkdir($fullPath, 0755, true) && !is_dir($fullPath)) {
            throw new UnableToCreateDirectoryException("Unable to create directory at path: {$path}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $directory): bool
    {
        $fullPath = $this->fullPath($directory);

        if (!is_dir($fullPath)) {
            return false;
        }

        $items = new \FilesystemIterator($fullPath);

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($directory . DIRECTORY_SEPARATOR . $item->getBasename());
            } else {
                $this->delete($directory . DIRECTORY_SEPARATOR . $item->getBasename());
            }
        }

        if (!rmdir($fullPath)) {
            throw new UnableToDeleteDirectoryException("Unable to delete directory at path: {$directory}");
        }

        return true;
    }

    /**
     * Tam dosya yolunu oluşturur.
     *
     * @param string $path Göreceli yol
     * @return string Tam yol
     */
    protected function fullPath(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Dizinin var olduğundan emin olur.
     *
     * @param string $directory Dizin yolu
     * @throws UnableToCreateDirectoryException
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new UnableToCreateDirectoryException("Unable to create directory: {$directory}");
            }
        }
    }
}