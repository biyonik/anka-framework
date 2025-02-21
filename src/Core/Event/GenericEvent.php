<?php

declare(strict_types=1);

namespace Framework\Core\Event;

/**
 * Genel amaçlı olay sınıfı.
 *
 * Bu sınıf, özel bir event sınıfı oluşturmadan kullanılabilecek genel bir olay tipidir.
 *
 * @package Framework\Core\Event
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class GenericEvent extends AbstractEvent
{
    /**
     * Olay adı.
     */
    protected string $name;

    /**
     * Constructor.
     *
     * @param string $name Olay adı
     * @param array<string, mixed> $data Olay verileri
     */
    public function __construct(string $name, array $data = [])
    {
        parent::__construct($data);
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Olay adını değiştirir.
     *
     * @param string $name Yeni olay adı
     * @return self Akıcı arayüz için
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Olaya veri ekler veya mevcut veriyi günceller.
     *
     * @param string $key Veri anahtarı
     * @param mixed $value Veri değeri
     * @return self Akıcı arayüz için
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Olaydan veri kaldırır.
     *
     * @param string $key Kaldırılacak veri anahtarı
     * @return self Akıcı arayüz için
     */
    public function remove(string $key): self
    {
        if ($this->has($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Olayın tüm verilerini yeni verilerle değiştirir.
     *
     * @param array<string, mixed> $data Yeni veriler
     * @return self Akıcı arayüz için
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Mevcut verilere yeni veriler ekler, varolan anahtarların değerleri güncellenir.
     *
     * @param array<string, mixed> $data Eklenecek veriler
     * @return self Akıcı arayüz için
     */
    public function addData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
}