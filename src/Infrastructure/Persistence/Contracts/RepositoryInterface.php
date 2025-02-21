<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence\Contracts;

/**
 * Repository arayüzü.
 * 
 * Bu arayüz, repository deseni için gerekli temel metotları tanımlar.
 * Veri kaynağına erişim için standart bir arayüz sağlar.
 * 
 * @package Framework\Infrastructure\Persistence
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @template T
 */
interface RepositoryInterface
{
    /**
     * Tüm kayıtları döndürür.
     * 
     * @return array<int, T> Tüm kayıtlar
     */
    public function findAll(): array;

    /**
     * ID'ye göre bir kayıt döndürür.
     * 
     * @param int|string $id Kayıt ID'si
     * @return T|null Bulunan kayıt veya null
     */
    public function findById(int|string $id): mixed;

    /**
     * Belirli kriterlere uyan kayıtları döndürür.
     * 
     * @param array<string, mixed> $criteria Arama kriterleri
     * @return array<int, T> Bulunan kayıtlar
     */
    public function findBy(array $criteria): array;

    /**
     * Belirli kriterlere uyan tek bir kayıt döndürür.
     * 
     * @param array<string, mixed> $criteria Arama kriterleri
     * @return T|null Bulunan kayıt veya null
     */
    public function findOneBy(array $criteria): mixed;

    /**
     * Yeni bir kayıt ekler.
     * 
     * @param T $entity Eklenecek varlık
     * @return T|bool Eklenen varlık veya başarısızsa false
     */
    public function create(mixed $entity): mixed;

    /**
     * Bir kaydı günceller.
     * 
     * @param T $entity Güncellenecek varlık
     * @return T|bool Güncellenen varlık veya başarısızsa false
     */
    public function update(mixed $entity): mixed;

    /**
     * Bir kaydı siler.
     * 
     * @param T|int|string $entity Silinecek varlık veya ID
     * @return bool Başarılı ise true
     */
    public function delete(mixed $entity): bool;

    /**
     * Tablo adını döndürür.
     * 
     * @return string Tablo adı
     */
    public function getTableName(): string;
}