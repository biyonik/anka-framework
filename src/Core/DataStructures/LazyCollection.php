<?php

declare(strict_types=1);

namespace Framework\Core\DataStructures;

use Framework\Core\DataStructures\Contracts\LazyCollectionInterface;
use Framework\Core\DataStructures\Contracts\CollectionInterface;

/**
 * Lazy Collection sınıfı.
 *
 * Lazy (tembel) değerlendirme yapan koleksiyon.
 * Operasyonlar zincirlendiğinde, sonuçlar gerçekten ihtiyaç duyulana kadar hesaplanmaz.
 *
 * @package Framework\Core\DataStructures
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 *
 * @template T
 * @implements LazyCollectionInterface<T>
 */
class LazyCollection implements LazyCollectionInterface
{
    /**
     * Iterator'ı oluşturacak fonksiyon.
     *
     * @var callable(): \Iterator<int|string, T>
     */
    private $source;

    /**
     * Constructor.
     *
     * @param iterable<int|string, T>|callable(): \Iterator<int|string, T> $source Veri kaynağı
     */
    public function __construct(iterable|callable $source)
    {
        $this->source = $this->makeIteratorFactory($source);
    }

    /**
     * Veri kaynağından iterator factory oluşturur.
     *
     * @param iterable<int|string, T>|callable(): \Iterator<int|string, T> $source Veri kaynağı
     * @return callable(): \Iterator<int|string, T> Iterator factory
     */
    private function makeIteratorFactory(iterable|callable $source): callable
    {
        if (is_callable($source)) {
            return static function () use ($source): \Iterator {
                $result = $source();

                if ($result instanceof \Iterator) {
                    return $result;
                }

                if ($result instanceof \IteratorAggregate) {
                    return $result->getIterator();
                }

                return new \ArrayIterator(is_array($result) ? $result : iterator_to_array($result));
            };
        }

        if ($source instanceof \Iterator) {
            return static fn(): \Iterator => $source;
        }

        if ($source instanceof \IteratorAggregate) {
            return static fn(): \Iterator => $source->getIterator();
        }

        if (is_array($source)) {
            return static fn(): \Iterator => new \ArrayIterator($source);
        }

        return static fn(): \Iterator => new \ArrayIterator(iterator_to_array($source));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return ($this->source)();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this as $_) {
            $count++;
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $callback): static
    {
        $source = $this->source;

        return new static(static function () use ($source, $callback): \Generator {
            $iterator = $source();
            $index = 0;

            foreach ($iterator as $key => $value) {
                yield $key => $callback($value, is_int($key) ? $index++ : $key);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callback): static
    {
        $source = $this->source;

        return new static(static function () use ($source, $callback): \Generator {
            $iterator = $source();
            $index = 0;

            foreach ($iterator as $key => $value) {
                if ($callback($value, is_int($key) ? $index++ : $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function find(callable $callback, mixed $default = null): mixed
    {
        $index = 0;

        foreach ($this as $key => $value) {
            if ($callback($value, is_int($key) ? $index++ : $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function take(int $limit): static
    {
        if ($limit <= 0) {
            return new static([]);
        }

        $source = $this->source;

        return new static(static function () use ($source, $limit): \Generator {
            $iterator = $source();
            $count = 0;

            foreach ($iterator as $key => $value) {
                yield $key => $value;

                if (++$count >= $limit) {
                    break;
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $count): static
    {
        if ($count <= 0) {
            return clone $this;
        }

        $source = $this->source;

        return new static(static function () use ($source, $count): \Generator {
            $iterator = $source();
            $skipped = 0;

            foreach ($iterator as $key => $value) {
                if ($skipped++ < $count) {
                    continue;
                }

                yield $key => $value;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toCollection(): CollectionInterface
    {
        return new Collection($this->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function any(callable $callback): bool
    {
        $index = 0;

        foreach ($this as $key => $value) {
            if ($callback($value, is_int($key) ? $index++ : $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function all(callable $callback): bool
    {
        $index = 0;

        foreach ($this as $key => $value) {
            if (!$callback($value, is_int($key) ? $index++ : $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $result = $initial;
        $index = 0;

        foreach ($this as $key => $value) {
            $result = $callback($result, $value, is_int($key) ? $index++ : $key);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function consume(): void
    {
        foreach ($this as $_) {
            // Tüketim için boş döngü
        }
    }

    /**
     * {@inheritdoc}
     */
    public function each(callable $callback): void
    {
        $index = 0;

        foreach ($this as $key => $value) {
            $callback($value, is_int($key) ? $index++ : $key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function chunk(int $size): static
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Size must be greater than zero');
        }

        $source = $this->source;

        return new static(static function () use ($source, $size): \Generator {
            $iterator = $source();
            $chunk = [];
            $i = 0;

            foreach ($iterator as $key => $value) {
                $chunk[$key] = $value;
                $i++;

                if ($i >= $size) {
                    yield $chunk;
                    $chunk = [];
                    $i = 0;
                }
            }

            if (!empty($chunk)) {
                yield $chunk;
            }
        });
    }

    /**
     * Bir dizi değeri tembel koleksiyona dönüştürür.
     *
     * @template U
     * @param array<int|string, U> $items Dizi
     * @return static<U> Tembel koleksiyon
     */
    public static function fromArray(array $items): static
    {
        return new static($items);
    }

    /**
     * Bir fonksiyonu tembel koleksiyona dönüştürür.
     *
     * @template U
     * @param callable(): iterable<int|string, U> $callback Fonksiyon
     * @return static<U> Tembel koleksiyon
     */
    public static function fromCallable(callable $callback): static
    {
        return new static($callback);
    }

    /**
     * Tembel koleksiyonu ImmutableCollection'a dönüştürür.
     *
     * @return ImmutableCollection<T> Değiştirilemez koleksiyon
     */
    public function toImmutableCollection(): ImmutableCollection
    {
        return new ImmutableCollection($this->toArray());
    }

    /**
     * Tembel koleksiyonu Map'e dönüştürür.
     *
     * @return Map<int|string, T> Map
     */
    public function toMap(): Map
    {
        return new Map($this->toArray());
    }

    /**
     * Sonsuz bir sayı dizisi oluşturur.
     *
     * @param int $start Başlangıç değeri
     * @param int $step Adım değeri
     * @return static<int> Tembel koleksiyon
     */
    public static function range(int $start = 0, int $step = 1): static
    {
        return new static(static function () use ($start, $step): \Generator {
            $i = $start;

            while (true) {
                yield $i;
                $i += $step;
            }
        });
    }

    /**
     * Belirli bir aralıkta sayı dizisi oluşturur.
     *
     * @param int $start Başlangıç değeri
     * @param int $end Bitiş değeri (dahil)
     * @param int $step Adım değeri
     * @return static<int> Tembel koleksiyon
     */
    public static function rangeClosed(int $start, int $end, int $step = 1): static
    {
        if ($step === 0) {
            throw new \InvalidArgumentException('Step cannot be zero');
        }

        return new static(static function () use ($start, $end, $step): \Generator {
            if ($step > 0) {
                for ($i = $start; $i <= $end; $i += $step) {
                    yield $i;
                }
            } else {
                for ($i = $start; $i >= $end; $i += $step) {
                    yield $i;
                }
            }
        });
    }
}