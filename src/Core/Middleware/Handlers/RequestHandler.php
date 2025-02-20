<?php

declare(strict_types=1);

namespace Framework\Core\Middleware\Handlers;

use Framework\Core\Middleware\Interfaces\{RequestHandlerInterface, MiddlewareInterface};
use Framework\Core\Http\Response\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use InvalidArgumentException;

/**
 * Request handler'ın temel implementasyonu.
 *
 * Bu sınıf, middleware zincirini yönetir ve her bir middleware'i sırayla çalıştırır.
 * Zincirin sonunda fallback handler çalışır ve nihai response üretilir.
 *
 * @package Framework\Core\Middleware
 * @subpackage Handlers
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Middleware stack'i.
     *
     * @var array<MiddlewareInterface>
     */
    protected array $middlewares = [];

    /**
     * Stack pozisyonu.
     */
    protected int $currentPosition = 0;

    /**
     * Fallback handler.
     *
     * @var callable
     */
    protected $fallbackHandler;

    /**
     * Constructor.
     *
     * @param array<MiddlewareInterface> $middlewares Başlangıç middleware'leri
     * @param callable|null $fallbackHandler Fallback handler
     */
    public function __construct(array $middlewares = [], ?callable $fallbackHandler = null)
    {
        $this->middlewares = array_values($middlewares);
        $this->fallbackHandler = $fallbackHandler ?? fn() => new Response();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Eğer çalıştırılacak middleware kalmadıysa fallback'i çalıştır
        if (!isset($this->middlewares[$this->currentPosition])) {
            return call_user_func($this->fallbackHandler, $request);
        }

        $middleware = $this->middlewares[$this->currentPosition];
        $this->currentPosition++;

        // Middleware'in çalışıp çalışmayacağını kontrol et
        if (!$middleware->shouldRun($request)) {
            return $this->handle($request);
        }

        // Middleware'i çalıştır
        return $middleware->process($request, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function add(MiddlewareInterface $middleware): static
    {
        $new = clone $this;
        $new->middlewares[] = $middleware;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddlewares(array $middlewares): static
    {
        $new = clone $this;
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
            $new->middlewares[] = $middleware;
        }
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function clearMiddlewares(): static
    {
        $new = clone $this;
        $new->middlewares = [];
        $new->currentPosition = 0;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function setFallbackHandler(callable $handler): static
    {
        $new = clone $this;
        $new->fallbackHandler = $handler;
        return $new;
    }

    /**
     * Handler'ı belirli bir pozisyondan yeniden başlatır.
     *
     * @param int $position Başlangıç pozisyonu
     * @return static
     */
    public function reset(int $position = 0): static
    {
        $new = clone $this;
        $new->currentPosition = $position;
        return $new;
    }

    /**
     * Stack'e middleware'i öne ekler.
     *
     * @param MiddlewareInterface $middleware Eklenecek middleware
     * @return static
     */
    public function prepend(MiddlewareInterface $middleware): static
    {
        $new = clone $this;
        array_unshift($new->middlewares, $middleware);
        return $new;
    }

    /**
     * Stack'ten middleware çıkarır.
     *
     * @param MiddlewareInterface $middleware Çıkarılacak middleware
     * @return static
     */
    public function remove(MiddlewareInterface $middleware): static
    {
        $new = clone $this;
        $new->middlewares = array_filter(
            $new->middlewares,
            fn($item) => $item !== $middleware
        );
        return $new;
    }

    /**
     * Mevcut handler'ın klonunu oluşturur.
     */
    public function __clone()
    {
        $this->currentPosition = 0;
    }
}