<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence;

use Framework\Infrastructure\Persistence\Contracts\ConnectionManagerInterface;
use Framework\Infrastructure\Persistence\Contracts\EntityManagerInterface;
use Framework\Infrastructure\Persistence\Contracts\RepositoryInterface;
use Framework\Infrastructure\Persistence\Exceptions\DatabaseException;
use InvalidArgumentException;
use Throwable;

/**
 * Entity Manager sınıfı.
 *
 * Unit of Work pattern'i kullanarak, veritabanı işlemlerini yönetir.
 * Repository'leri oluşturur ve değişiklikleri koordine eder.
 *
 * @package Framework\Infrastructure\Persistence
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class EntityManager implements EntityManagerInterface
{
    /**
     * Kaydedilecek entity'ler.
     *
     * @var array<int, object>
     */
    private array $persistQueue = [];

    /**
     * Silinecek entity'ler.
     *
     * @var array<int, object>
     */
    private array $removeQueue = [];

    /**
     * Repository önbelleği.
     *
     * @var array<string, RepositoryInterface>
     */
    private array $repositories = [];

    /**
     * Constructor.
     *
     * @param ConnectionManagerInterface $connectionManager Veritabanı bağlantı yöneticisi
     * @param QueryBuilderFactory $queryBuilderFactory QueryBuilder fabrikası
     */
    public function __construct(
        private readonly ConnectionManagerInterface $connectionManager,
        private readonly QueryBuilderFactory $queryBuilderFactory
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $repositoryClass): mixed
    {
        if (!isset($this->repositories[$repositoryClass])) {
            if (!class_exists($repositoryClass)) {
                throw new InvalidArgumentException(
                    sprintf('Repository sınıfı bulunamadı: %s', $repositoryClass)
                );
            }

            if (!is_subclass_of($repositoryClass, RepositoryInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Repository sınıfı %s arayüzünü implement etmelidir: %s',
                        RepositoryInterface::class,
                        $repositoryClass
                    )
                );
            }

            $queryBuilder = $this->queryBuilderFactory->create($this->connectionManager);
            $this->repositories[$repositoryClass] = new $repositoryClass($queryBuilder);
        }

        return $this->repositories[$repositoryClass];
    }

    /**
     * {@inheritdoc}
     */
    public function persist(object $entity): void
    {
        $this->persistQueue[spl_object_id($entity)] = $entity;

        // Entity hem silinecek hem de kaydedilecek listesindeyse, silme listesinden çıkar
        if (isset($this->removeQueue[spl_object_id($entity)])) {
            unset($this->removeQueue[spl_object_id($entity)]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(object $entity): void
    {
        $this->removeQueue[spl_object_id($entity)] = $entity;

        // Entity hem kaydedilecek hem de silinecek listesindeyse, kaydetme listesinden çıkar
        if (isset($this->persistQueue[spl_object_id($entity)])) {
            unset($this->persistQueue[spl_object_id($entity)]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): int
    {
        $affectedRows = 0;

        if (empty($this->persistQueue) && empty($this->removeQueue)) {
            return $affectedRows;
        }

        $inTransaction = $this->inTransaction();

        try {
            // Eğer henüz bir transaction başlatılmamışsa, başlat
            if (!$inTransaction) {
                $this->beginTransaction();
            }

            // Önce silme işlemlerini yap
            foreach ($this->removeQueue as $entity) {
                $affectedRows += $this->processRemoval($entity);
            }

            // Sonra kaydetme işlemlerini yap
            foreach ($this->persistQueue as $entity) {
                $affectedRows += $this->processPersistence($entity);
            }

            // Eğer transaction'ı biz başlattıysak, commit yap
            if (!$inTransaction) {
                $this->commit();
            }

            // Kuyrukları temizle
            $this->removeQueue = [];
            $this->persistQueue = [];

            return $affectedRows;
        } catch (Throwable $e) {
            // Eğer transaction'ı biz başlattıysak, rollback yap
            if (!$inTransaction && $this->inTransaction()) {
                $this->rollback();
            }

            throw $e instanceof DatabaseException ? $e : new DatabaseException(
                sprintf('Flush işlemi sırasında hata: %s', $e->getMessage()),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): bool
    {
        return $this->connectionManager->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return $this->connectionManager->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): bool
    {
        return $this->connectionManager->rollback();
    }

    /**
     * {@inheritdoc}
     */
    public function inTransaction(): bool
    {
        return $this->connectionManager->inTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(callable $callback): mixed
    {
        $inTransaction = $this->inTransaction();

        if (!$inTransaction) {
            $this->beginTransaction();
        }

        try {
            $result = $callback($this);

            if (!$inTransaction) {
                $this->commit();
            }

            return $result;
        } catch (Throwable $e) {
            if (!$inTransaction && $this->inTransaction()) {
                $this->rollback();
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->persistQueue = [];
        $this->removeQueue = [];
    }

    /**
     * Entity'yi silme işlemini yapar.
     *
     * @param object $entity Silinecek entity
     * @return int Etkilenen kayıt sayısı
     */
    private function processRemoval(object $entity): int
    {
        $repository = $this->findRepositoryForEntity($entity);

        if ($repository === null) {
            return 0;
        }

        $result = $repository->delete($entity);

        return $result ? 1 : 0;
    }

    /**
     * Entity'yi kaydetme işlemini yapar.
     *
     * @param object $entity Kaydedilecek entity
     * @return int Etkilenen kayıt sayısı
     */
    private function processPersistence(object $entity): int
    {
        $repository = $this->findRepositoryForEntity($entity);

        if ($repository === null) {
            return 0;
        }

        // ID değeri yoksa oluştur, varsa güncelle
        $method = $this->getEntityId($entity) ? 'update' : 'create';
        $result = $repository->$method($entity);

        return $result ? 1 : 0;
    }

    /**
     * Entity için uygun repository'yi bulur.
     *
     * @param object $entity İncelenecek entity
     * @return RepositoryInterface|null Bulunan repository
     */
    private function findRepositoryForEntity(object $entity): ?RepositoryInterface
    {
        $entityClass = get_class($entity);

        // Repository sınıfını bulmak için convention-based yaklaşım
        $repositoryClass = str_replace('\\Domain\\Entities\\', '\\Infrastructure\\Persistence\\Repositories\\', $entityClass) . 'Repository';

        if (class_exists($repositoryClass)) {
            return $this->getRepository($repositoryClass);
        }

        // Alt sınıflarda özelleştirme için
        return $this->resolveRepositoryForEntity($entity);
    }

    /**
     * Entity için özel repository çözümlemesi.
     * Alt sınıflar tarafından override edilebilir.
     *
     * @param object $entity İncelenecek entity
     * @return RepositoryInterface|null Bulunan repository
     */
    protected function resolveRepositoryForEntity(object $entity): ?RepositoryInterface
    {
        return null;
    }

    /**
     * Entity'nin ID'sini döndürür.
     *
     * @param object $entity İncelenecek entity
     * @return int|string|null Entity ID'si
     */
    private function getEntityId(object $entity): int|string|null
    {
        // getId metodu varsa kullan
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        // id property'si varsa kullan
        if (property_exists($entity, 'id')) {
            return $entity->id;
        }

        return null;
    }
}