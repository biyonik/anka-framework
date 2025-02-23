<?php

declare(strict_types=1);

namespace Framework\Core\Filesystem\Adapters;

use Framework\Core\Filesystem\Contracts\FilesystemInterface;
use Framework\Core\Filesystem\Exception\{
    FilesystemException,
    FileNotFoundException,
    UnableToWriteFileException,
    UnableToDeleteFileException,
    UnableToCopyFileException,
    UnableToMoveFileException,
    UnableToCreateDirectoryException,
    UnableToDeleteDirectoryException
};

/**
 * FTP dosya sistemi için filesystem adapter'ı.
 *
 * Bu sınıf, FTP protokolü üzerinden dosya operasyonlarını
 * gerçekleştirir. FilesystemInterface'i implemente eder.
 *
 * @package Framework\Core\Filesystem
 * @subpackage Adapters
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class FtpAdapter implements FilesystemInterface
{
    /**
     * FTP bağlantısı.
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param string $host FTP sunucusu
     * @param string $username Kullanıcı adı
     * @param string $password Şifre
     * @param int $port Port numarası
     * @param bool $ssl SSL kullanılsın mı
     * @param int $timeout Timeout süresi
     * @param bool $passive Pasif mod kullanılsın mı
     */
    public function __construct(
        private readonly string $host,
        private readonly string $username,
        private readonly string $password,
        private readonly int    $port = 21,
        private readonly bool   $ssl = false,
        private readonly int    $timeout = 30,
        private readonly bool $passive = true
    ) {
        $this->connect();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        try {
            $size = ftp_size($this->connection, $path);
            return $size !== -1;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): string
    {
        $tempFile = tmpfile();

        if ($tempFile === false) {
            throw new UnableToWriteFileException("Unable to create temporary file");
        }

        try {
            $tempPath = stream_get_meta_data($tempFile)['uri'];

            if (!ftp_get($this->connection, $tempPath, $path, FTP_BINARY)) {
                throw new FileNotFoundException("File not found at path: {$path}");
            }

            $contents = file_get_contents($tempPath);

            if ($contents === false) {
                throw new FileNotFoundException("Unable to read file at path: {$path}");
            }

            return $contents;
        } finally {
            fclose($tempFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $contents): bool
    {
        $tempFile = tmpfile();

        if ($tempFile === false) {
            throw new UnableToWriteFileException("Unable to create temporary file");
        }

        try {
            $tempPath = stream_get_meta_data($tempFile)['uri'];

            // İçeriği geçici dosyaya yaz
            if ($contents instanceof \Stringable) {
                $contents = (string) $contents;
            }

            if (is_resource($contents)) {
                $contents = stream_get_contents($contents);
                if ($contents === false) {
                    throw new UnableToWriteFileException("Unable to read from resource");
                }
            }

            if (file_put_contents($tempPath, $contents) === false) {
                throw new UnableToWriteFileException("Unable to write to temporary file");
            }

            // FTP sunucusuna yükle
            if (!ftp_put($this->connection, $path, $tempPath, FTP_BINARY)) {
                throw new UnableToWriteFileException("Unable to write file at path: {$path}");
            }

            return true;
        } finally {
            fclose($tempFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function append(string $path, string $contents): bool
    {
        // FTP'de doğrudan append olmadığı için, mevcut içeriği oku ve yeni içerikle birlikte yaz
        $currentContent = $this->exists($path) ? $this->get($path) : '';
        return $this->put($path, $currentContent . $contents);
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

                if (!ftp_delete($this->connection, $path)) {
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
        // FTP'de doğrudan copy olmadığı için, dosyayı indirip tekrar yükle
        $content = $this->get($from);
        return $this->put($to, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $from, string $to): bool
    {
        if (!ftp_rename($this->connection, $from, $to)) {
            throw new UnableToMoveFileException("Unable to move file from {$from} to {$to}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function size(string $path): int
    {
        $size = ftp_size($this->connection, $path);

        if ($size === -1) {
            throw new FileNotFoundException("File not found at path: {$path}");
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): int
    {
        $time = ftp_mdtm($this->connection, $path);

        if ($time === -1) {
            throw new FileNotFoundException("File not found at path: {$path}");
        }

        return $time;
    }

    /**
     * {@inheritdoc}
     */
    public function makeDirectory(string $path): bool
    {
        if (!ftp_mkdir($this->connection, $path)) {
            throw new UnableToCreateDirectoryException("Unable to create directory at path: {$path}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $directory): bool
    {
        try {
            $contents = ftp_nlist($this->connection, $directory);

            if ($contents === false) {
                throw new UnableToDeleteDirectoryException("Unable to list directory contents");
            }

            foreach ($contents as $item) {
                $item = basename($item);
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $path = $directory . '/' . $item;

                if ($this->isDirectory($path)) {
                    $this->deleteDirectory($path);
                } else {
                    $this->delete($path);
                }
            }

            if (!ftp_rmdir($this->connection, $directory)) {
                throw new UnableToDeleteDirectoryException("Unable to delete directory at path: {$directory}");
            }

            return true;
        } catch (\Exception $e) {
            throw new UnableToDeleteDirectoryException(
                "Unable to delete directory at path: {$directory}",
                0,
                $e
            );
        }
    }

    /**
     * Destructor - FTP bağlantısını kapat
     */
    public function __destruct()
    {
        if ($this->connection) {
            ftp_close($this->connection);
        }
    }

    /**
     * FTP bağlantısını başlatır.
     *
     * @throws FilesystemException
     */
    private function connect(): void
    {
        // SSL bağlantısı için
        if ($this->ssl) {
            $this->connection = ftp_ssl_connect($this->host, $this->port, $this->timeout);
        } else {
            $this->connection = ftp_connect($this->host, $this->port, $this->timeout);
        }

        if ($this->connection === false) {
            throw new FilesystemException("Unable to connect to FTP server: {$this->host}");
        }

        if (!ftp_login($this->connection, $this->username, $this->password)) {
            throw new FilesystemException("Unable to login to FTP server");
        }

        if ($this->passive) {
            ftp_pasv($this->connection, true);
        }
    }

    /**
     * Yolun bir dizin olup olmadığını kontrol eder.
     */
    private function isDirectory(string $path): bool
    {
        try {
            $pwd = ftp_pwd($this->connection);
            if (ftp_chdir($this->connection, $path)) {
                ftp_chdir($this->connection, $pwd);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}