<?php

declare(strict_types=1);

namespace Framework\Domain;

use Framework\Domain\Contracts\DomainServiceInterface;

/**
 * Soyut Domain Service sınıfı.
 *
 * Domain service'ler için temel implementasyon sağlayan soyut sınıf.
 *
 * @package Framework\Domain
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractDomainService implements DomainServiceInterface
{
    /**
     * Service'in domain'i.
     *
     * @var string
     */
    protected string $domain;

    /**
     * {@inheritdoc}
     */
    public function getDomain(): string
    {
        if (!isset($this->domain)) {
            // Sınıf adından domain adını çıkar
            // Örnek: UserDomainService -> User
            $className = basename(str_replace('\\', '/', get_class($this)));
            $this->domain = str_replace('DomainService', '', $className);
        }

        return $this->domain;
    }

    /**
     * Domain'i ayarlar.
     *
     * @param string $domain Domain adı
     * @return self Akıcı arayüz için
     */
    protected function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }
}