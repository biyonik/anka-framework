<?php

declare(strict_types=1);

namespace Framework\Core\Controller;

use Framework\Core\Controller\Interfaces\ControllerInterface;
use Framework\Core\Controller\Attributes\{BeforeAction, Middleware};
use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Http\Response\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;
use InvalidArgumentException;

/**
 * Controller'lar için temel soyut sınıf.
 * 
 * Bu sınıf, framework'ün controller'ları için temel davranışları sağlar.
 * ControllerInterface'i implement eder ve yaygın işlevler için default
 * implementasyonlar sunar.
 * 
 * @package Framework\Core\Controller
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * Uygulama instance'ı.
     */
    protected ApplicationInterface $app;

    /**
     * Class reflection.
     */
    protected ?ReflectionClass $reflection = null;

    /**
     * Middleware'ler.
     * 
     * @var array<string,array<string>>
     */
    protected array $middleware = [];

    /**
     * BeforeAction hookları.
     * 
     * @var array<string,array<BeforeAction>>
     */
    protected array $beforeActions = [];

    /**
     * {@inheritdoc}
     */
    public function initialize(ApplicationInterface $app): void
    {
        $this->app = $app;
        $this->loadAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(ServerRequestInterface $request, string $action, array $params = []): ResponseInterface
    {
        // Metod var mı kontrol et
        if (!method_exists($this, $action)) {
            throw new InvalidArgumentException(sprintf('Action "%s" does not exist', $action));
        }

        // BeforeAction hook'unu çalıştır
        $filteredRequest = $this->beforeAction($request, $action);
        if ($filteredRequest === false) {
            return new Response(403, [], 'Forbidden');
        }

        // Action'ı çalıştır
        $response = call_user_func_array([$this, $action], $params);

        // Response değilse dönüştür
        if (!$response instanceof ResponseInterface) {
            $response = $this->convertToResponse($response);
        }

        // AfterAction hook'unu çalıştır
        return $this->afterAction($response, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction(ServerRequestInterface $request, string $action): ServerRequestInterface|false
    {
        // Sınıf seviyesindeki beforeAction'ları çalıştır
        if (method_exists($this, 'beforeAnyAction')) {
            $result = $this->beforeAnyAction($request, $action);
            if ($result === false) {
                return false;
            }
            $request = $result instanceof ServerRequestInterface ? $result : $request;
        }

        // Action özel beforeAction'ları çalıştır
        $beforeActions = $this->getBeforeActionsForAction($action);
        foreach ($beforeActions as $beforeAction) {
            $method = $beforeAction->getMethod();
            if (method_exists($this, $method)) {
                $result = $this->$method($request, $action);
                if ($result === false) {
                    return false;
                }
                $request = $result instanceof ServerRequestInterface ? $result : $request;
            }
        }

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction(ResponseInterface $response, string $action): ResponseInterface
    {
        // Sınıf seviyesindeki afterAction'ları çalıştır
        if (method_exists($this, 'afterAnyAction')) {
            $result = $this->afterAnyAction($response, $action);
            $response = $result instanceof ResponseInterface ? $result : $response;
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddleware(): array
    {
        return $this->middleware['*'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getActionMiddleware(string $action): array
    {
        $actionMiddleware = $this->middleware[$action] ?? [];
        $globalMiddleware = $this->getMiddleware();

        return array_merge($globalMiddleware, $actionMiddleware);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplication(): ApplicationInterface
    {
        return $this->app;
    }

    /**
     * Reflection instance'ını döndürür.
     * 
     * @return ReflectionClass Controller reflection'ı
     */
    protected function getReflection(): ReflectionClass
    {
        if ($this->reflection === null) {
            $this->reflection = new ReflectionClass($this);
        }

        return $this->reflection;
    }

    /**
     * Controller attribute'larını yükler.
     * 
     * @return void
     */
    protected function loadAttributes(): void
    {
        $reflection = $this->getReflection();

        // Sınıf middleware'lerini yükle
        $this->loadClassMiddleware($reflection);

        // Metod middleware'lerini yükle
        $this->loadMethodMiddleware($reflection);

        // BeforeAction'ları yükle
        $this->loadBeforeActions($reflection);
    }

    /**
     * Sınıf middleware'lerini yükler.
     * 
     * @param ReflectionClass $reflection Sınıf reflection'ı
     * @return void
     */
    protected function loadClassMiddleware(ReflectionClass $reflection): void
    {
        $attributes = $reflection->getAttributes(Middleware::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var Middleware */
            $middleware = $attribute->newInstance();
            $this->registerMiddleware($middleware, '*');
        }
    }

    /**
     * Metod middleware'lerini yükler.
     * 
     * @param ReflectionClass $reflection Sınıf reflection'ı
     * @return void
     */
    protected function loadMethodMiddleware(ReflectionClass $reflection): void
    {
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Middleware::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                /** @var Middleware */
                $middleware = $attribute->newInstance();
                $this->registerMiddleware($middleware, $method->getName());
            }
        }
    }

    /**
     * BeforeAction'ları yükler.
     * 
     * @param ReflectionClass $reflection Sınıf reflection'ı
     * @return void
     */
    protected function loadBeforeActions(ReflectionClass $reflection): void
    {
        // Sınıf seviyesindeki BeforeAction'lar
        $classAttributes = $reflection->getAttributes(BeforeAction::class, ReflectionAttribute::IS_INSTANCEOF);
        foreach ($classAttributes as $attribute) {
            /** @var BeforeAction */
            $beforeAction = $attribute->newInstance();
            $this->registerBeforeAction($beforeAction, '*');
        }

        // Metod seviyesindeki BeforeAction'lar
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(BeforeAction::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                /** @var BeforeAction */
                $beforeAction = $attribute->newInstance();
                $this->registerBeforeAction($beforeAction, $method->getName());
            }
        }
    }

    /**
     * Middleware'i kaydeder.
     * 
     * @param Middleware $middleware Middleware attribute'u
     * @param string $action Action adı
     * @return void
     */
    protected function registerMiddleware(Middleware $middleware, string $action): void
    {
        if (!isset($this->middleware[$action])) {
            $this->middleware[$action] = [];
        }

        foreach ($middleware->getMiddleware() as $middlewareName) {
            $this->middleware[$action][] = $middlewareName;
        }
    }

    /**
     * BeforeAction'ı kaydeder.
     * 
     * @param BeforeAction $beforeAction BeforeAction attribute'u
     * @param string $action Action adı
     * @return void
     */
    protected function registerBeforeAction(BeforeAction $beforeAction, string $action): void
    {
        if (!isset($this->beforeActions[$action])) {
            $this->beforeActions[$action] = [];
        }

        $this->beforeActions[$action][] = $beforeAction;
    }

    /**
     * Action için BeforeAction'ları döndürür.
     * 
     * @param string $action Action adı
     * @return array<BeforeAction> BeforeAction listesi
     */
    protected function getBeforeActionsForAction(string $action): array
    {
        $beforeActions = [];

        // Global BeforeAction'ları ekle
        if (isset($this->beforeActions['*'])) {
            foreach ($this->beforeActions['*'] as $beforeAction) {
                if ($beforeAction->shouldRunForAction($action)) {
                    $beforeActions[] = $beforeAction;
                }
            }
        }

        // Action özel BeforeAction'ları ekle
        if (isset($this->beforeActions[$action])) {
            $beforeActions = array_merge($beforeActions, $this->beforeActions[$action]);
        }

        return $beforeActions;
    }

    /**
     * Herhangi bir değeri ResponseInterface'e dönüştürür.
     * 
     * @param mixed $result Dönüştürülecek değer
     * @return ResponseInterface
     */
    protected function convertToResponse(mixed $result): ResponseInterface
    {
        // String ise HTML response
        if (is_string($result)) {
            return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $result);
        }

        // Array veya object ise JSON response
        if (is_array($result) || is_object($result)) {
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($result));
        }

        // Diğer durumlarda boş response
        return new Response();
    }
}