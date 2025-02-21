<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence;

use Framework\Infrastructure\Persistence\Contracts\ConnectionManagerInterface;
use Framework\Infrastructure\Persistence\Exceptions\DatabaseException;
use PDO;
use PDOException;

/**
 * Veritabanı bağlantı yöneticisi.
 * 
 * Bu sınıf, veritabanı bağlantılarını yönetir ve çeşitli veritabanları 
 * için bağlantı oluşturma, transaction yönetimi gibi işlevleri sağlar.
 * Lazy connection özelliği ve connection pooling için temel yapı içerir.
 * 
 * @package Framework\Infrastructure\Persistence
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * Aktif veritabanı bağlantısı.
     */
    protected ?PDO $connection = null;

    /**
     * Veritabanı konfigürasyonu.
     * 
     * @var array<string,mixed>
     */
    protected array $config;

    /**
     * Bağlantı seçenekleri.
     * 
     * @var array<mixed>
     */
    protected array $options;

    /**
     * Constructor.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @param array<mixed> $options PDO seçenekleri
     */
    public function __construct(array $config, array $options = [])
    {
        $this->config = $config;
        
        // Varsayılan seçenekleri ayarla
        $this->options = array_merge([
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ], $options);
        
        // MySQL için UTF-8 ayarları
        if (($config['driver'] ?? 'mysql') === 'mysql') {
            $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = 
                'SET NAMES ' . ($config['charset'] ?? 'utf8mb4') . 
                ' COLLATE ' . ($config['collation'] ?? 'utf8mb4_unicode_ci');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = $this->connect($this->config);
        }

        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(array $config): PDO
    {
        try {
            $dsn = $this->createDsn($config);
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;

            return new PDO($dsn, $username, $password, $this->options);
        } catch (PDOException $e) {
            throw DatabaseException::connectionError(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->getConnection()->beginTransaction();
        } catch (PDOException $e) {
            throw DatabaseException::transactionError(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        try {
            return $this->getConnection()->commit();
        } catch (PDOException $e) {
            throw DatabaseException::transactionError(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): bool
    {
        try {
            return $this->getConnection()->rollBack();
        } catch (PDOException $e) {
            throw DatabaseException::transactionError(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function inTransaction(): bool
    {
        return $this->isConnected() && $this->getConnection()->inTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->getConnection()->lastInsertId($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(string $query): \PDOStatement
    {
        try {
            return $this->getConnection()->prepare($query);
        } catch (PDOException $e) {
            throw DatabaseException::queryError(
                $e->getMessage(),
                $query,
                [],
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Bağlantı için DSN string'i oluşturur.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @return string DSN string'i
     */
    protected function createDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';

        return match ($driver) {
            'mysql' => $this->createMysqlDsn($config),
            'pgsql' => $this->createPgsqlDsn($config),
            'sqlite' => $this->createSqliteDsn($config),
            'sqlsrv' => $this->createSqlsrvDsn($config),
            default => throw new DatabaseException(
                sprintf('Desteklenmeyen veritabanı sürücüsü: %s', $driver)
            ),
        };
    }

    /**
     * MySQL için DSN string'i oluşturur.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @return string DSN string'i
     */
    protected function createMysqlDsn(array $config): string
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port}";

        if ($database !== '') {
            $dsn .= ";dbname={$database}";
        }

        $dsn .= ";charset={$charset}";

        // Unix socket bağlantısı
        if (isset($config['unix_socket']) && $config['unix_socket'] !== '') {
            $dsn = "mysql:unix_socket={$config['unix_socket']}";

            if ($database !== '') {
                $dsn .= ";dbname={$database}";
            }
        }

        return $dsn;
    }

    /**
     * PostgreSQL için DSN string'i oluşturur.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @return string DSN string'i
     */
    protected function createPgsqlDsn(array $config): string
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '5432';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8';

        $dsn = "pgsql:host={$host};port={$port}";

        if ($database !== '') {
            $dsn .= ";dbname={$database}";
        }

        if ($charset !== '') {
            $dsn .= ";options='--client_encoding={$charset}'";
        }

        return $dsn;
    }

    /**
     * SQLite için DSN string'i oluşturur.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @return string DSN string'i
     */
    protected function createSqliteDsn(array $config): string
    {
        $database = $config['database'] ?? ':memory:';

        return "sqlite:{$database}";
    }

    /**
     * SQL Server için DSN string'i oluşturur.
     * 
     * @param array<string,mixed> $config Bağlantı konfigürasyonu
     * @return string DSN string'i
     */
    protected function createSqlsrvDsn(array $config): string
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '1433';
        $database = $config['database'] ?? '';

        $dsn = "sqlsrv:Server={$host}";

        if ($port !== '') {
            $dsn .= ",{$port}";
        }

        if ($database !== '') {
            $dsn .= ";Database={$database}";
        }

        return $dsn;
    }
}