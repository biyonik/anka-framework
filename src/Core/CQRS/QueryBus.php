<?php

declare(strict_types=1);

namespace Framework\Core\CQRS;

use Framework\Core\CQRS\Contracts\QueryBusInterface;
use Framework\Core\CQRS\Contracts\QueryHandlerInterface;
use Framework\Core\CQRS\Contracts\QueryInterface;
use Framework\Core\CQRS\Exceptions\QueryHandlerNotFoundException;
use Framework\Core\CQRS\Exceptions\QueryValidationException;
use Framework\Core\Event\Contracts\EventDispatcherInterface;
use Framework\Core\Event\GenericEvent;
use Throwable;

/**
 * Query Bus sınıfı.
 *
 * Bu sınıf, Query'leri ilgili Handler'lara yönlendiren
 * Query Bus bileşenini implemente eder.
 *
 * @package Framework\Core\CQRS
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class QueryBus implements QueryBusInterface
{
    /**
     * Query handler'lar.
     *
     * @var array<string, QueryHandlerInterface>
     */
    protected array $handlers = [];

    /**
     * Query bus middleware'leri.
     *
     * @var array<callable>
     */
    protected array $middlewares = [];

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface|null $eventDispatcher Event dispatcher
     */
    public function __construct(
        protected ?EventDispatcherInterface $eventDispatcher = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public function dispatch(QueryInterface $query): mixed
    {
        // Query'i işlemeden önce olay yayınla
        $this->dispatchEvent('query.dispatched', $query);

        try {
            // Query'i validate et
            $this->validateQuery($query);

            // Query için handler bul
            $handler = $this->findHandler($query);

            // Middleware'lerle handler'ı sar
            $next = $this->decorateWithMiddlewares($handler, $query);

            // Query'i işle
            $result = $next($query);

            // Başarılı işlemeden sonra olay yayınla
            $this->dispatchEvent('query.succeeded', $query, ['result' => $result]);

            return $result;
        } catch (Throwable $e) {
            // Hata durumunda olay yayınla
            $this->dispatchEvent('query.failed', $query, [
                'exception' => $e,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerHandler(string $queryType, QueryHandlerInterface|string $handler): self
    {
        // Eğer handler bir sınıf adıysa, instance oluştur
        if (is_string($handler)) {
            $handler = new $handler();
        }

        // Handler'ın geçerli bir QueryHandlerInterface implementasyonu olduğunu kontrol et
        if (!$handler instanceof QueryHandlerInterface) {
            throw new \InvalidArgumentException(
                sprintf('Handler must implement %s', QueryHandlerInterface::class)
            );
        }

        // Handler'ı kaydet
        $this->handlers[$queryType] = $handler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerHandlerClass(string $handlerClass): self
    {
        if (!class_exists($handlerClass)) {
            throw new \InvalidArgumentException(
                sprintf('Handler class %s does not exist', $handlerClass)
            );
        }

        if (!is_subclass_of($handlerClass, QueryHandlerInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Handler class %s must implement %s', $handlerClass, QueryHandlerInterface::class)
            );
        }

        // Handler'ın işleyebileceği query tipini al
        $queryType = $handlerClass::getQueryType();

        // Handler'ı kaydet
        return $this->registerHandler($queryType, $handlerClass);
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddleware(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Query için handler bulur.
     *
     * @param QueryInterface $query İşlenecek query
     * @return QueryHandlerInterface Handler instance'ı
     * @throws QueryHandlerNotFoundException Handler bulunamazsa
     */
    protected function findHandler(QueryInterface $query): QueryHandlerInterface
    {
        $queryType = $query->getType();

        if (!isset($this->handlers[$queryType])) {
            throw new QueryHandlerNotFoundException(
                sprintf('No handler found for query %s', $queryType)
            );
        }

        $handler = $this->handlers[$queryType];

        if (!$handler->canHandle($query)) {
            throw new QueryHandlerNotFoundException(
                sprintf('Handler %s cannot handle query %s', get_class($handler), $queryType)
            );
        }

        return $handler;
    }

    /**
     * Query'i validate eder.
     *
     * @param QueryInterface $query Validate edilecek query
     * @return void
     * @throws QueryValidationException Validation hatası durumunda
     */
    protected function validateQuery(QueryInterface $query): void
    {
        $validationResult = $query->validate();

        if ($validationResult->hasErrors()) {
            throw new QueryValidationException(
                $validationResult->getErrors(),
                'Query validation failed'
            );
        }
    }

    /**
     * Handler'ı middleware'lerle sarar.
     *
     * @param QueryHandlerInterface $handler Query handler
     * @param QueryInterface $query İşlenecek query
     * @return callable Middleware'lerle sarılmış handler
     */
    protected function decorateWithMiddlewares(QueryHandlerInterface $handler, QueryInterface $query): callable
    {
        // Base handler fonksiyonu
        $core = fn(QueryInterface $qry) => $handler->handle($qry);

        // Middleware'leri tersten ekle
        $chain = array_reduce(
            array_reverse($this->middlewares),
            function (callable $next, callable $middleware) {
                return function (QueryInterface $query) use ($middleware, $next) {
                    return $middleware($query, $next);
                };
            },
            $core
        );

        return $chain;
    }

    /**
     * Event dispatcher mevcutsa olayı yayınlar.
     *
     * @param string $eventName Olay adı
     * @param QueryInterface $query İlgili query
     * @param array<string, mixed> $additionalData Ek veriler
     * @return void
     */
    protected function dispatchEvent(
        string $eventName,
        QueryInterface $query,
        array $additionalData = []
    ): void {
        if ($this->eventDispatcher === null) {
            return;
        }

        $eventData = array_merge(
            [
                'query_type' => $query->getType(),
                'query_parameters' => $query->getParameters()
            ],
            $additionalData
        );

        $event = new GenericEvent($eventName, $eventData);
        $this->eventDispatcher->dispatch($event);
    }
}