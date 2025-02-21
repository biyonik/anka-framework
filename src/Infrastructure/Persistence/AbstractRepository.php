<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence;

use Framework\Infrastructure\Persistence\Contracts\QueryBuilderInterface;
use Framework\Infrastructure\Persistence\Contracts\RepositoryInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * Temel repository sınıfı.
 *
 * Bu sınıf, Repository pattern için temel implementasyonu sağlar.
 * Veritabanı işlemleri için QueryBuilder'ı kullanır ve entity mapping işlemlerini yönetir.
 *
 * @package Framework\Infrastructure\Persistence
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @implements RepositoryInterface<T>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Entity sınıfı adı.
     *
     * @var class-string<T>
     */
    protected string $entityClass;

    /**
     * Tablo adı.
     */
    protected string $tableName;

    /**
     * Birincil anahtar kolonu.
     */
    protected string $primaryKey = 'id';

    /**
     * Constructor.
     *
     * @param QueryBuilderInterface $queryBuilder Sorgu oluşturucu
     */
    public function __construct(
        protected QueryBuilderInterface $queryBuilder
    ) {
        $this->setupRepository();
    }

    /**
     * Repository'yi ayarlar.
     *
     * @return void
     */
    protected function setupRepository(): void
    {
        // Alt sınıflar override edebilir
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $results = $this->queryBuilder
            ->select('*')
            ->from($this->getTableName())
            ->execute();

        return $this->hydrateEntities($results);
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int|string $id): mixed
    {
        $result = $this->queryBuilder
            ->select('*')
            ->from($this->getTableName())
            ->where(sprintf('%s = ?', $this->primaryKey), [$id])
            ->first();

        if ($result === null) {
            return null;
        }

        return $this->hydrateEntity($result);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria): array
    {
        $query = $this->queryBuilder
            ->select('*')
            ->from($this->getTableName());

        $this->applyCriteriaToQuery($query, $criteria);

        $results = $query->execute();

        return $this->hydrateEntities($results);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria): mixed
    {
        $query = $this->queryBuilder
            ->select('*')
            ->from($this->getTableName());

        $this->applyCriteriaToQuery($query, $criteria);

        $result = $query->first();

        if ($result === null) {
            return null;
        }

        return $this->hydrateEntity($result);
    }

    /**
     * {@inheritdoc}
     */
    public function create(mixed $entity): mixed
    {
        $data = $this->extractEntityData($entity);

        $id = $this->queryBuilder
            ->from($this->getTableName())
            ->insert($data);

        if ($id === false) {
            return false;
        }

        // ID değerini entity'e ata
        if (method_exists($entity, 'setId')) {
            $entity->setId($id);
        } elseif (property_exists($entity, 'id')) {
            $entity->id = $id;
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function update(mixed $entity): mixed
    {
        $data = $this->extractEntityData($entity);
        $id = $this->getEntityId($entity);

        if ($id === null) {
            return false;
        }

        $affected = $this->queryBuilder
            ->from($this->getTableName())
            ->where(sprintf('%s = ?', $this->primaryKey), [$id])
            ->update($data);

        return $affected > 0 ? $entity : false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(mixed $entity): bool
    {
        $id = is_object($entity) ? $this->getEntityId($entity) : $entity;

        if ($id === null) {
            return false;
        }

        $affected = $this->queryBuilder
            ->from($this->getTableName())
            ->where(sprintf('%s = ?', $this->primaryKey), [$id])
            ->delete();

        return $affected > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Entity'nin ID'sini döndürür.
     *
     * @param T $entity İncelenecek entity
     * @return int|string|null Entity ID'si
     */
    protected function getEntityId(mixed $entity): int|string|null
    {
        // getId metodu varsa kullan
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        // id property'si varsa kullan
        if (property_exists($entity, 'id')) {
            return $entity->id;
        }

        // primaryKey ile belirtilen property varsa kullan
        if (property_exists($entity, $this->primaryKey)) {
            $reflection = new ReflectionProperty($entity, $this->primaryKey);
            $reflection->setAccessible(true);
            return $reflection->getValue($entity);
        }

        return null;
    }

    /**
     * Entity'den veri çıkarır.
     *
     * @param T $entity İncelenecek entity
     * @return array<string, mixed> Çıkarılan veriler
     */
    protected function extractEntityData(mixed $entity): array
    {
        $data = [];
        $reflection = new ReflectionClass($entity);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            // Static veya transient property'leri atla
            if ($property->isStatic() || $property->getAttributes('Transient')) {
                continue;
            }

            $property->setAccessible(true);
            $name = $property->getName();
            $value = $property->getValue($entity);

            // ID'yi, otomatik artan bir alan ise ve boş ise atla
            if ($name === $this->primaryKey && $value === null) {
                continue;
            }

            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * Veritabanı sonuçlarını entity'lere dönüştürür.
     *
     * @param array<int, array<string, mixed>> $results Veritabanı sonuçları
     * @return array<int, T> Entity listesi
     */
    protected function hydrateEntities(array $results): array
    {
        $entities = [];

        foreach ($results as $result) {
            $entities[] = $this->hydrateEntity($result);
        }

        return $entities;
    }

    /**
     * Veritabanı sonucunu entity'e dönüştürür.
     *
     * @param array<string, mixed> $data Veritabanı sonucu
     * @return T Entity
     */
    protected function hydrateEntity(array $data): mixed
    {
        $reflection = new ReflectionClass($this->entityClass);
        $entity = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            if (property_exists($entity, $key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($entity, $value);
            }
        }

        // Eğer varsa, entity'yi initialize etmek için metodu çağır
        if (method_exists($entity, '__wakeup')) {
            $entity->__wakeup();
        }

        return $entity;
    }

    /**
     * Sorguya arama kriterlerini uygular.
     *
     * @param QueryBuilderInterface $query Sorgu builder
     * @param array<string, mixed> $criteria Arama kriterleri
     * @return void
     */
    protected function applyCriteriaToQuery(QueryBuilderInterface $query, array $criteria): void
    {
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $placeholders = array_fill(0, count($value), '?');
                $query->where(
                    sprintf('%s IN (%s)', $field, implode(', ', $placeholders)),
                    $value
                );
            } else {
                $query->where(sprintf('%s = ?', $field), [$value]);
            }
        }
    }
}