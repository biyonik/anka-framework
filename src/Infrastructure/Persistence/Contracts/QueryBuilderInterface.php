<?php

declare(strict_types=1);

namespace Framework\Infrastructure\Persistence\Contracts;

/**
 * SQL sorgu oluşturucu arayüzü.
 * 
 * Bu arayüz, SQL sorgularını nesne yönelimli ve akıcı bir şekilde
 * oluşturmak için gerekli metodları tanımlar. Select, insert, update,
 * delete işlemleri ve çeşitli sorgu koşulları için metotlar içerir.
 * 
 * @package Framework\Infrastructure\Persistence
 * @subpackage Contracts
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface QueryBuilderInterface
{
    /**
     * SELECT ifadesini ayarlar.
     * 
     * @param string|array<string> $columns Seçilecek kolonlar
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function select(string|array $columns): self;

    /**
     * FROM ifadesini ayarlar.
     * 
     * @param string $table Sorgulanacak tablo adı
     * @param string|null $alias Tablo için takma ad
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function from(string $table, ?string $alias = null): self;

    /**
     * JOIN ifadesi ekler.
     * 
     * @param string $table Katılım yapılacak tablo
     * @param string $condition Katılım koşulu
     * @param string $type Katılım tipi (INNER, LEFT, RIGHT)
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function join(string $table, string $condition, string $type = 'INNER'): self;

    /**
     * WHERE koşulu ekler.
     * 
     * @param string $condition Koşul ifadesi
     * @param array<mixed> $bindings Bağlanacak parametreler
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function where(string $condition, array $bindings = []): self;

    /**
     * GROUP BY ifadesi ekler.
     * 
     * @param string|array<string> $columns Gruplanacak kolonlar
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function groupBy(string|array $columns): self;

    /**
     * HAVING koşulu ekler.
     * 
     * @param string $condition Koşul ifadesi
     * @param array<mixed> $bindings Bağlanacak parametreler
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function having(string $condition, array $bindings = []): self;

    /**
     * ORDER BY ifadesi ekler.
     * 
     * @param string $column Sıralanacak kolon
     * @param string $direction Sıralama yönü (ASC veya DESC)
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function orderBy(string $column, string $direction = 'ASC'): self;

    /**
     * LIMIT ifadesini ayarlar.
     * 
     * @param int $limit Maksimum kayıt sayısı
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function limit(int $limit): self;

    /**
     * OFFSET ifadesini ayarlar.
     * 
     * @param int $offset Atlanacak kayıt sayısı
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function offset(int $offset): self;

    /**
     * SQL sorgusunu oluşturur.
     * 
     * @return string Oluşturulan SQL sorgusu
     */
    public function toSql(): string;

    /**
     * Sorguyu çalıştırır ve sonuçları döndürür.
     * 
     * @return array<int, array<string, mixed>> Sorgu sonuçları
     */
    public function execute(): array;

    /**
     * INSERT sorgusu oluşturur ve çalıştırır.
     * 
     * @param array<string, mixed> $data Eklenecek veriler
     * @return string|false Eklenen kaydın ID'si
     */
    public function insert(array $data): string|false;

    /**
     * UPDATE sorgusu oluşturur ve çalıştırır.
     * 
     * @param array<string, mixed> $data Güncellenecek veriler
     * @return int Etkilenen kayıt sayısı
     */
    public function update(array $data): int;

    /**
     * DELETE sorgusu oluşturur ve çalıştırır.
     * 
     * @return int Silinen kayıt sayısı
     */
    public function delete(): int;

    /**
     * Query Builder'ı sıfırlar.
     * 
     * @return self Akıcı arayüz için instance'ı döndürür
     */
    public function reset(): self;
}