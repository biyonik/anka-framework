<?php

declare(strict_types=1);

namespace Framework\Core\Middleware;

use Framework\Core\Middleware\Interfaces\{MiddlewareStackInterface, MiddlewareInterface};
use InvalidArgumentException;

/**
 * Middleware'lerin yığın (stack) olarak yönetilmesini sağlayan sınıf.
 *
 * Bu sınıf, middleware'lerin öncelikli ve gruplu olarak yönetilmesini sağlar.
 * Her middleware'in çalışma sırası ve koşulları bu stack üzerinden yönetilir.
 *
 * @package Framework\Core\Middleware
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class MiddlewareStack implements MiddlewareStackInterface
{
    /**
     * Middleware stack'i.
     *
     * @var array<MiddlewareInterface>
     */
    protected array $stack = [];

    /**
     * Gruplandırılmış middleware'ler.
     *
     * @var array<string,array<MiddlewareInterface>>
     */
    protected array $groups = [];

    /**
     * Stack'in sıralanmış olup olmadığı.
     */
    protected bool $sorted = true;

    /**
     * {@inheritdoc}
     */
    public function add(MiddlewareInterface $middleware): static
    {
        $this->stack[] = $middleware;
        $this->sorted = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(MiddlewareInterface $middleware): static
    {
        array_unshift($this->stack, $middleware);
        $this->sorted = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMany(array $middlewares): static
    {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Middleware must be an instance of %s, %s given',
                        MiddlewareInterface::class,
                        get_debug_type($middleware)
                    )
                );
            }
            $this->add($middleware);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $group, array $middlewares): static
    {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Middleware must be an instance of %s, %s given',
                        MiddlewareInterface::class,
                        get_debug_type($middleware)
                    )
                );
            }
        }

        $this->groups[$group] = $middlewares;
        $this->stack = array_merge($this->stack, $middlewares);
        $this->sorted = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup(string $group): array
    {
        return $this->groups[$group] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        if (!$this->sorted) {
            $this->sort();
        }
        return $this->stack;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(): static
    {
        if (!$this->sorted && !empty($this->stack)) {
            usort($this->stack, function (MiddlewareInterface $a, MiddlewareInterface $b) {
                return $a->getPriority() <=> $b->getPriority();
            });
            $this->sorted = true;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): static
    {
        $this->stack = [];
        $this->groups = [];
        $this->sorted = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->stack);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->stack);
    }

    /**
     * Belirli bir grup için stack oluşturur.
     *
     * @param string $group Grup adı
     * @return static Yeni stack instance'ı
     */
    public function forGroup(string $group): static
    {
        $stack = new static();
        if (isset($this->groups[$group])) {
            $stack->addMany($this->groups[$group]);
        }
        return $stack;
    }

    /**
     * Stack içindeki middleware'leri filtreler.
     *
     * @param callable $callback Filtreleme fonksiyonu
     * @return static Yeni stack instance'ı
     */
    public function filter(callable $callback): static
    {
        $stack = new static();
        $stack->addMany(array_filter($this->stack, $callback));
        return $stack;
    }

    /**
     * Stack'e birden fazla grup ekler.
     *
     * @param array<string,array<MiddlewareInterface>> $groups Grup array'i
     * @return static
     */
    public function addGroups(array $groups): static
    {
        foreach ($groups as $name => $middlewares) {
            $this->group($name, $middlewares);
        }
        return $this;
    }

    /**
     * Stack'ten bir middleware'i kaldırır.
     *
     * @param MiddlewareInterface $middleware Kaldırılacak middleware
     * @return static
     */
    public function remove(MiddlewareInterface $middleware): static
    {
        $this->stack = array_filter(
            $this->stack,
            fn($item) => $item !== $middleware
        );

        foreach ($this->groups as $name => $middlewares) {
            $this->groups[$name] = array_filter(
                $middlewares,
                fn($item) => $item !== $middleware
            );
        }

        return $this;
    }

    /**
     * Stack'ten bir grup kaldırır.
     *
     * @param string $group Kaldırılacak grup
     * @return static
     */
    public function removeGroup(string $group): static
    {
        if (isset($this->groups[$group])) {
            foreach ($this->groups[$group] as $middleware) {
                $this->remove($middleware);
            }
            unset($this->groups[$group]);
        }
        return $this;
    }
}