<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence;

use Framework\Infrastructure\Persistence\Contracts\ConnectionManagerInterface;
use Framework\Infrastructure\Persistence\Contracts\QueryBuilderInterface;
use Framework\Infrastructure\Persistence\Exceptions\DatabaseException;
use PDO;
use PDOException;

/**
 * SQL sorgu oluşturucu sınıfı.
 * 
 * Bu sınıf, SQL sorgularını güvenli ve nesne yönelimli bir şekilde oluşturmak için kullanılır.
 * Metot zincirleme (method chaining) desteği ile akıcı bir arayüz sunar.
 * 
 * Örnek Kullanım:
 * ```php
 * $query = (new QueryBuilder($connectionManager))
 *     ->select(['id', 'name', 'email'])
 *     ->from('users')
 *     ->where('status = ?', [1])
 *     ->orderBy('created_at', 'DESC')
 *     ->limit(10);
 *
 * $results = $query->execute();
 * ```
 * 
 * @package Framework\Infrastructure\Persistence
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class QueryBuilder implements QueryBuilderInterface
{
    /**
     * Sorgu bileşenleri.
     * 
     * @var array<string> $columns
     * @var array<string> $joins
     * @var array<string> $where
     * @var array<mixed> $whereBindings
     * @var array<string> $groupBy
     * @var array<string> $having
     * @var array<mixed> $havingBindings
     * @var array<string> $orderBy
     * @var array<string, mixed> $values
     */
    private array $columns = ['*'];
    private string $from = '';
    private array $joins = [];
    private array $where = [];
    private array $whereBindings = [];
    private array $groupBy = [];
    private array $having = [];
    private array $havingBindings = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $values = [];

    /**
     * Constructor.
     * 
     * @param ConnectionManagerInterface $connectionManager Veritabanı bağlantı yöneticisi
     */
    public function __construct(
        private readonly ConnectionManagerInterface $connectionManager
    ) {}

    /**
     * {@inheritdoc}
     */
    public function select(string|array $columns): self
    {
        $this->columns = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function from(string $table, ?string $alias = null): self
    {
        $this->from = $alias ? sprintf('%s AS %s', $table, $alias) : $table;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function join(string $table, string $condition, string $type = 'INNER'): self
    {
        $this->joins[] = sprintf('%s JOIN %s ON %s', strtoupper($type), $table, $condition);
        return $this;
    }

    /**
     * Left join kısayolu.
     * 
     * @param string $table Katılım yapılacak tablo
     * @param string $condition Katılım koşulu
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function leftJoin(string $table, string $condition): self
    {
        return $this->join($table, $condition, 'LEFT');
    }

    /**
     * Right join kısayolu.
     * 
     * @param string $table Katılım yapılacak tablo
     * @param string $condition Katılım koşulu
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function rightJoin(string $table, string $condition): self
    {
        return $this->join($table, $condition, 'RIGHT');
    }

    /**
     * {@inheritdoc}
     */
    public function where(string $condition, array $bindings = []): self
    {
        $this->where[] = $condition;
        $this->whereBindings = array_merge($this->whereBindings, $bindings);
        return $this;
    }

    /**
     * OR WHERE koşulu ekler.
     * 
     * @param string $condition Koşul ifadesi
     * @param array<mixed> $bindings Bağlanacak parametreler
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function orWhere(string $condition, array $bindings = []): self
    {
        if (empty($this->where)) {
            return $this->where($condition, $bindings);
        }

        $lastIndex = count($this->where) - 1;
        $this->where[$lastIndex] = sprintf('(%s OR %s)', $this->where[$lastIndex], $condition);
        $this->whereBindings = array_merge($this->whereBindings, $bindings);
        
        return $this;
    }

    /**
     * AND WHERE koşulu ekler.
     * 
     * @param string $condition Koşul ifadesi
     * @param array<mixed> $bindings Bağlanacak parametreler
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function andWhere(string $condition, array $bindings = []): self
    {
        return $this->where($condition, $bindings);
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy(string|array $columns): self
    {
        $this->groupBy = array_merge(
            $this->groupBy,
            is_array($columns) ? $columns : [$columns]
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function having(string $condition, array $bindings = []): self
    {
        $this->having[] = $condition;
        $this->havingBindings = array_merge($this->havingBindings, $bindings);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }
        
        $this->orderBy[] = sprintf('%s %s', $column, $direction);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    /**
     * Bir sayfadaki öğe sayısı ve sayfa numarasına göre
     * LIMIT ve OFFSET değerlerini ayarlar.
     * 
     * @param int $page Sayfa numarası (1'den başlar)
     * @param int $perPage Sayfa başına öğe sayısı
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function paginate(int $page, int $perPage): self
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        
        $this->limit($perPage);
        $this->offset(($page - 1) * $perPage);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toSql(): string
    {
        $parts = ['SELECT', implode(', ', $this->columns)];
        $parts[] = 'FROM ' . $this->from;

        if (!empty($this->joins)) {
            $parts[] = implode(' ', $this->joins);
        }

        if (!empty($this->where)) {
            $parts[] = 'WHERE ' . implode(' AND ', $this->where);
        }

        if (!empty($this->groupBy)) {
            $parts[] = 'GROUP BY ' . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $parts[] = 'HAVING ' . implode(' AND ', $this->having);
        }

        if (!empty($this->orderBy)) {
            $parts[] = 'ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $parts[] = 'LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $parts[] = 'OFFSET ' . $this->offset;
        }

        return implode(' ', $parts);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): array
    {
        try {
            $sql = $this->toSql();
            $bindings = array_merge($this->whereBindings, $this->havingBindings);

            $statement = $this->connectionManager->prepare($sql);
            $statement->execute($bindings);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw DatabaseException::queryError(
                $e->getMessage(),
                $this->toSql(),
                array_merge($this->whereBindings, $this->havingBindings),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Sorguyu çalıştırır ve ilk sonucu döndürür.
     * 
     * @return array<string, mixed>|null İlk sonuç veya sonuç yoksa null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->execute();
        
        return $results[0] ?? null;
    }

    /**
     * Belirli bir kolon için değerleri tek bir dizi olarak döndürür.
     * 
     * @param string $column Dönüştürülecek kolon
     * @return array<mixed> Kolon değerleri
     */
    public function pluck(string $column): array
    {
        $this->select([$column]);
        $results = $this->execute();
        
        return array_column($results, $column);
    }

    /**
     * Sorgu sonucunda kaç satır olduğunu sayar.
     * 
     * @return int Sonuç sayısı
     */
    public function count(): int
    {
        $originalColumns = $this->columns;
        $originalLimit = $this->limit;
        $originalOffset = $this->offset;
        
        $this->columns = ['COUNT(*) as total_count'];
        $this->limit = null;
        $this->offset = null;
        
        $result = $this->first();
        
        // Orijinal değerleri geri yükle
        $this->columns = $originalColumns;
        $this->limit = $originalLimit;
        $this->offset = $originalOffset;
        
        return (int) ($result['total_count'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $data): string|false
    {
        try {
            $this->values = $data;
            $sql = $this->buildInsertQuery();
            $values = array_values($this->values);

            $statement = $this->connectionManager->prepare($sql);
            $statement->execute($values);

            return $this->connectionManager->lastInsertId();
        } catch (PDOException $e) {
            throw DatabaseException::queryError(
                $e->getMessage(),
                $sql ?? '',
                $values ?? [],
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * INSERT sorgusu için bulk insert yapar.
     * 
     * @param array<int, array<string, mixed>> $dataSet Eklenecek veri kümesi
     * @return int Eklenen kayıt sayısı
     */
    public function bulkInsert(array $dataSet): int
    {
        if (empty($dataSet)) {
            return 0;
        }

        try {
            // İlk elemanın anahtarlarını kullanarak kolonları belirle
            $columns = array_keys($dataSet[0]);
            $placeholders = [];
            $values = [];

            // Her veri seti için yer tutucular oluştur
            foreach ($dataSet as $data) {
                $rowPlaceholders = [];
                
                foreach ($columns as $column) {
                    $rowPlaceholders[] = '?';
                    $values[] = $data[$column] ?? null;
                }
                
                $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
            }

            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $this->from,
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $statement = $this->connectionManager->prepare($sql);
            $statement->execute($values);

            return $statement->rowCount();
        } catch (PDOException $e) {
            throw DatabaseException::queryError(
                $e->getMessage(),
                $sql ?? '',
                $values ?? [],
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data): int
    {
        try {
            $this->values = $data;
            $sql = $this->buildUpdateQuery();
            
            // Önce update değerlerini, sonra where parametrelerini ekle
            $values = array_merge(
                array_values($this->values),
                $this->whereBindings
            );

            $statement = $this->connectionManager->prepare($sql);
            $statement->execute($values);

            return $statement->rowCount();
        } catch (PDOException $e) {
            throw DatabaseException::queryError(
                $e->getMessage(),
                $sql ?? '',
                $values ?? [],
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): int
    {
        try {
            $sql = $this->buildDeleteQuery();

            $statement = $this->connectionManager->prepare($sql);
            $statement->execute($this->whereBindings);

            return $statement->rowCount();
        } catch (PDOException $e) {
            throw DatabaseException::queryError(
                $e->getMessage(),
                $sql ?? '',
                $this->whereBindings,
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Insert/Update işlemleri için değer ekler.
     * 
     * @param array<string, mixed> $values Eklenecek/güncellenecek değerler
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function values(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): self
    {
        $this->columns = ['*'];
        $this->from = '';
        $this->joins = [];
        $this->where = [];
        $this->whereBindings = [];
        $this->groupBy = [];
        $this->having = [];
        $this->havingBindings = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->values = [];

        return $this;
    }

    /**
     * Insert sorgusu için SQL oluşturur.
     * 
     * @return string SQL sorgusu
     */
    private function buildInsertQuery(): string
    {
        $columns = array_keys($this->values);
        $placeholders = array_fill(0, count($columns), '?');

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->from,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
    }

    /**
     * Update sorgusu için SQL oluşturur.
     * 
     * @return string SQL sorgusu
     */
    private function buildUpdateQuery(): string
    {
        $set = array_map(
            fn($column) => sprintf('%s = ?', $column),
            array_keys($this->values)
        );

        $sql = sprintf(
            'UPDATE %s SET %s',
            $this->from,
            implode(', ', $set)
        );

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }

    /**
     * Delete sorgusu için SQL oluşturur.
     * 
     * @return string SQL sorgusu
     */
    private function buildDeleteQuery(): string
    {
        $sql = sprintf('DELETE FROM %s', $this->from);

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }

    /**
     * SQL sorgusunu string olarak göstermek için.
     * 
     * @return string SQL sorgusu
     */
    public function __toString(): string
    {
        return $this->toSql();
    }
}