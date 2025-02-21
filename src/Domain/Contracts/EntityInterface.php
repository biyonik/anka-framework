<?php

declare(strict_types=1);

namespace Framework\Domain\Contracts;

/**
 * Entity arayüzü.
 *
 * Domain entity'lerin uygulaması gereken temel arayüz.
 * Entity'ler, kimliği olan ve yaşam döngüsü boyunca değişebilen domain nesneleridir.
 *
 * @package Framework\Domain
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface EntityInterface
{
    /**
     * Entity'nin benzersiz kimliğini döndürür.
     *
     * @return mixed Benzersiz kimlik
     */
    public function getId(): mixed;

    /**
     * Entity'nin benzersiz kimliğini ayarlar.
     *
     * @param mixed $id Benzersiz kimlik
     * @return self Akıcı arayüz için
     */
    public function setId(mixed $id): self;

    /**
     * Entity'nin eşitliğini kontrol eder.
     * İki entity, aynı sınıftan ve aynı ID'ye sahipse eşittir.
     *
     * @param EntityInterface|null $entity Karşılaştırılacak entity
     * @return bool Eşitlik durumu
     */
    public function equals(?EntityInterface $entity): bool;
}