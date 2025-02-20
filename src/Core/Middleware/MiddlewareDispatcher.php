<?php

declare(strict_types=1);

namespace Framework\Core\Middleware;

use Framework\Core\Middleware\Interfaces\{MiddlewareInterface, MiddlewareStackInterface};
use Framework\Core\Middleware\Handlers\RequestHandler;
use Framework\Core\Http\Response\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Closure;

/**
 * Middleware sisteminin ana orkestratörü.
 *
 * Bu sınıf, middleware sistemini yönetir ve request/response döngüsünü kontrol eder.
 * Middleware'lerin yüklenmesi, gruplandırılması ve çalıştırılmasından sorumludur.
 *
 * Özellikler:
 * - Middleware stack yönetimi
 * - Grup bazlı middleware çalıştırma
 * - Dinamik middleware ekleme
 * - Error handling
 *
 * @package Framework\Core\Middleware
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class MiddlewareDispatcher
{
    /**
     * Middleware stack.
     */
    protected MiddlewareStackInterface $stack;

    /**
     * Fallback handler.
     *
     * @var callable
     */
    protected $fallbackHandler;

    /**
     * Error handler.
     *
     * @var callable|null
     */
    protected $errorHandler;

    /**
     * Constructor.
     *
     * @param MiddlewareStackInterface|null $stack Middleware stack
     * @param callable|null $fallbackHandler Fallback handler
     */
    public function __construct(
        ?MiddlewareStackInterface $stack = null,
        ?callable $fallbackHandler = null
    ) {
        $this->stack = $stack ?? new MiddlewareStack();
        $this->fallbackHandler = $fallbackHandler ?? fn() => new Response();
    }

    /**
     * Request'i işler ve response üretir.
     *
     * @param ServerRequestInterface $request İşlenecek request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $handler = new RequestHandler(
                $this->stack->getAll(),
                $this->fallbackHandler
            );

            return $handler->handle($request);
        } catch (\Throwable $e) {
            if ($this->errorHandler) {
                return call_user_func($this->errorHandler, $e, $request);
            }
            throw $e;
        }
    }

    /**
     * Belirli bir grup için request'i işler.
     *
     * @param string $group Grup adı
     * @param ServerRequestInterface $request İşlenecek request
     * @return ResponseInterface
     */
    public function handleGroup(string $group, ServerRequestInterface $request): ResponseInterface
    {
        $groupStack = $this->stack->forGroup($group);
        $dispatcher = new self($groupStack, $this->fallbackHandler);

        if ($this->errorHandler) {
            $dispatcher->setErrorHandler($this->errorHandler);
        }

        return $dispatcher->handle($request);
    }

    /**
     * Middleware ekler.
     *
     * @param MiddlewareInterface $middleware Eklenecek middleware
     * @return static
     */
    public function add(MiddlewareInterface $middleware): static
    {
        $this->stack->add($middleware);
        return $this;
    }

    /**
     * Stack'in başına middleware ekler.
     *
     * @param MiddlewareInterface $middleware Eklenecek middleware
     * @return static
     */
    public function prepend(MiddlewareInterface $middleware): static
    {
        $this->stack->prepend($middleware);
        return $this;
    }

    /**
     * Birden fazla middleware ekler.
     *
     * @param array<MiddlewareInterface> $middlewares Eklenecek middleware'ler
     * @return static
     */
    public function addMany(array $middlewares): static
    {
        $this->stack->addMany($middlewares);
        return $this;
    }

    /**
     * Middleware'leri gruplar.
     *
     * @param string $group Grup adı
     * @param array<MiddlewareInterface> $middlewares Gruptaki middleware'ler
     * @return static
     */
    public function group(string $group, array $middlewares): static
    {
        $this->stack->group($group, $middlewares);
        return $this;
    }

    /**
     * Closure'ı middleware olarak ekler.
     *
     * @param Closure $handler Middleware handler'ı
     * @param int $priority Öncelik değeri
     * @return static
     */
    public function addClosure(Closure $handler, int $priority = 0): static
    {
        $middleware = new class($handler, $priority) implements MiddlewareInterface {
            private Closure $handler;
            private int $priority;

            public function __construct(Closure $handler, int $priority)
            {
                $this->handler = $handler;
                $this->priority = $priority;
            }

            public function process(
                ServerRequestInterface $request,
                \Psr\Http\Server\RequestHandlerInterface $handler
            ): ResponseInterface {
                return call_user_func($this->handler, $request, $handler);
            }

            public function shouldRun(ServerRequestInterface $request): bool
            {
                return true;
            }

            public function getPriority(): int
            {
                return $this->priority;
            }
        };

        return $this->add($middleware);
    }

    /**
     * Fallback handler'ı ayarlar.
     *
     * @param callable $handler Fallback handler
     * @return static
     */
    public function setFallbackHandler(callable $handler): static
    {
        $this->fallbackHandler = $handler;
        return $this;
    }

    /**
     * Error handler'ı ayarlar.
     *
     * @param callable $handler Error handler
     * @return static
     */
    public function setErrorHandler(callable $handler): static
    {
        $this->errorHandler = $handler;
        return $this;
    }

    /**
     * Stack'i temizler.
     *
     * @return static
     */
    public function clearStack(): static
    {
        $this->stack->clear();
        return $this;
    }

    /**
     * Middleware stack'i döndürür.
     *
     * @return MiddlewareStackInterface
     */
    public function getStack(): MiddlewareStackInterface
    {
        return $this->stack;
    }

    /**
     * Stack'in boş olup olmadığını kontrol eder.
     *
     * @return bool Stack boşsa true
     */
    public function isEmpty(): bool
    {
        return $this->stack->isEmpty();
    }

    /**
     * Stack'teki middleware sayısını döndürür.
     *
     * @return int Middleware sayısı
     */
    public function count(): int
    {
        return $this->stack->count();
    }
}