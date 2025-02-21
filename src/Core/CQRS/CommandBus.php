<?php

declare(strict_types=1);

namespace Framework\Core\CQRS;

use Framework\Core\CQRS\Contracts\CommandBusInterface;
use Framework\Core\CQRS\Contracts\CommandHandlerInterface;
use Framework\Core\CQRS\Contracts\CommandInterface;
use Framework\Core\CQRS\Exceptions\CommandHandlerNotFoundException;
use Framework\Core\CQRS\Exceptions\CommandValidationException;
use Framework\Core\Event\Contracts\EventDispatcherInterface;
use Framework\Core\Event\GenericEvent;
use Throwable;

/**
 * Command Bus sınıfı.
 *
 * Bu sınıf, Command'leri ilgili Handler'lara yönlendiren
 * Command Bus bileşenini implemente eder.
 *
 * @package Framework\Core\CQRS
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class CommandBus implements CommandBusInterface
{
    /**
     * Command handler'lar.
     *
     * @var array<string, CommandHandlerInterface>
     */
    protected array $handlers = [];

    /**
     * Command bus middleware'leri.
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
    public function dispatch(CommandInterface $command): mixed
    {
        // Command'i işlemeden önce olay yayınla
        $this->dispatchEvent('command.dispatched', $command);

        try {
            // Command'i validate et
            $this->validateCommand($command);

            // Command için handler bul
            $handler = $this->findHandler($command);

            // Middleware'lerle handler'ı sar
            $next = $this->decorateWithMiddlewares($handler, $command);

            // Command'i işle
            $result = $next($command);

            // Başarılı işlemeden sonra olay yayınla
            $this->dispatchEvent('command.succeeded', $command, ['result' => $result]);

            return $result;
        } catch (Throwable $e) {
            // Hata durumunda olay yayınla
            $this->dispatchEvent('command.failed', $command, [
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
    public function registerHandler(string $commandType, CommandHandlerInterface|string $handler): self
    {
        // Eğer handler bir sınıf adıysa, instance oluştur
        if (is_string($handler)) {
            $handler = new $handler();
        }

        // Handler'ın geçerli bir CommandHandlerInterface implementasyonu olduğunu kontrol et
        if (!$handler instanceof CommandHandlerInterface) {
            throw new \InvalidArgumentException(
                sprintf('Handler must implement %s', CommandHandlerInterface::class)
            );
        }

        // Handler'ı kaydet
        $this->handlers[$commandType] = $handler;

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

        if (!is_subclass_of($handlerClass, CommandHandlerInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Handler class %s must implement %s', $handlerClass, CommandHandlerInterface::class)
            );
        }

        // Handler'ın işleyebileceği command tipini al
        $commandType = $handlerClass::getCommandType();

        // Handler'ı kaydet
        return $this->registerHandler($commandType, $handlerClass);
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
     * Command için handler bulur.
     *
     * @param CommandInterface $command İşlenecek command
     * @return CommandHandlerInterface Handler instance'ı
     * @throws CommandHandlerNotFoundException Handler bulunamazsa
     */
    protected function findHandler(CommandInterface $command): CommandHandlerInterface
    {
        $commandType = $command->getType();

        if (!isset($this->handlers[$commandType])) {
            throw new CommandHandlerNotFoundException(
                sprintf('No handler found for command %s', $commandType)
            );
        }

        $handler = $this->handlers[$commandType];

        if (!$handler->canHandle($command)) {
            throw new CommandHandlerNotFoundException(
                sprintf('Handler %s cannot handle command %s', get_class($handler), $commandType)
            );
        }

        return $handler;
    }

    /**
     * Command'i validate eder.
     *
     * @param CommandInterface $command Validate edilecek command
     * @return void
     * @throws CommandValidationException Validation hatası durumunda
     */
    protected function validateCommand(CommandInterface $command): void
    {
        $validationResult = $command->validate();

        if ($validationResult->hasErrors()) {
            throw new CommandValidationException(
                $validationResult->getErrors(),
                'Command validation failed'
            );
        }
    }

    /**
     * Handler'ı middleware'lerle sarar.
     *
     * @param CommandHandlerInterface $handler Command handler
     * @param CommandInterface $command İşlenecek command
     * @return callable Middleware'lerle sarılmış handler
     */
    protected function decorateWithMiddlewares(CommandHandlerInterface $handler, CommandInterface $command): callable
    {
        // Base handler fonksiyonu
        $core = fn(CommandInterface $cmd) => $handler->handle($cmd);

        // Middleware'leri tersten ekle
        $chain = array_reduce(
            array_reverse($this->middlewares),
            function (callable $next, callable $middleware) {
                return function (CommandInterface $command) use ($middleware, $next) {
                    return $middleware($command, $next);
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
     * @param CommandInterface $command İlgili command
     * @param array<string, mixed> $additionalData Ek veriler
     * @return void
     */
    protected function dispatchEvent(
        string $eventName,
        CommandInterface $command,
        array $additionalData = []
    ): void {
        if ($this->eventDispatcher === null) {
            return;
        }

        $eventData = array_merge(
            [
                'command_type' => $command->getType(),
                'command_id' => $command->getCommandId(),
                'command_data' => $command->toArray()
            ],
            $additionalData
        );

        $event = new GenericEvent($eventName, $eventData);
        $this->eventDispatcher->dispatch($event);
    }
}