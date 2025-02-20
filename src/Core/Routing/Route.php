<?php

declare(strict_types=1);

namespace Framework\Core\Routing;

use Framework\Core\Routing\Interfaces\RouteInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Framework\Core\Http\Response\Response;
use InvalidArgumentException;
use RuntimeException;
use Closure;

/**
 * Tekil bir route'u temsil eden sınıf.
 *
 * Bu sınıf, bir HTTP route'unun tüm özelliklerini ve davranışlarını içerir.
 * URL pattern eşleştirme, parametre yakalama, middleware entegrasyonu ve
 * handler çalıştırma gibi temel işlevleri sağlar.
 *
 * @package Framework\Core\Routing
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Route implements RouteInterface
{
    /**
     * HTTP metodları.
     *
     * @var array<string>
     */
    protected array $methods = [];

    /**
     * Route pattern'i.
     */
    protected string $pattern = '';

    /**
     * Derlenmiş pattern.
     */
    protected string $compiledPattern = '';

    /**
     * Route handler'ı.
     *
     * @var callable|array|string
     */
    protected $handler;

    /**
     * Route adı.
     */
    protected ?string $name = null;

    /**
     * Route middleware'leri.
     *
     * @var array<string>
     */
    protected array $middleware = [];

    /**
     * Route parametreleri.
     *
     * @var array<string,string>
     */
    protected array $parameters = [];

    /**
     * Route prefix'i.
     */
    protected string $prefix = '';

    /**
     * Route domain'i.
     */
    protected ?string $domain = null;

    /**
     * Route parametresi pattern'leri.
     *
     * @var array<string,string>
     */
    protected array $wheres = [];

    /**
     * Pattern'deki parametre isimleri.
     *
     * @var array<string>
     */
    protected array $paramNames = [];

    /**
     * Constructor.
     *
     * @param array<string> $methods HTTP metodları
     * @param string $pattern Route pattern'i
     * @param callable|array|string $handler Route handler'ı
     * @param string|null $name Route adı
     */
    public function __construct(
        array $methods,
        string $pattern,
        callable|array|string $handler,
        ?string $name = null
    ) {
        $this->methods = array_map('strtoupper', $methods);
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern(): string
    {
        return $this->prefix . $this->pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): callable|array|string
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function name(string $name): static
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function middleware(string|array $middleware): static
    {
        $clone = clone $this;
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        $clone->middleware = array_merge($this->middleware, $middleware);
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function where(string $name, string $pattern): static
    {
        $clone = clone $this;
        $clone->wheres[$name] = $pattern;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function whereArray(array $parameters): static
    {
        $clone = clone $this;
        foreach ($parameters as $name => $pattern) {
            $clone->wheres[$name] = $pattern;
        }
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * {@inheritdoc}
     */
    public function domain(string $domain): static
    {
        $clone = clone $this;
        $clone->domain = $domain;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function prefix(string $prefix): static
    {
        $clone = clone $this;
        $clone->prefix = $prefix;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledPattern(): string
    {
        if (empty($this->compiledPattern)) {
            $this->compile();
        }

        return $this->compiledPattern;
    }

    /**
     * Pattern'i derler.
     *
     * @return static
     */
    protected function compile(): static
    {
        $pattern = $this->getPattern();

        // Parametre isimlerini topla
        preg_match_all('/{([^}]+)}/', $pattern, $matches);
        $this->paramNames = $matches[1];

        // Her bir parametre için pattern uygula
        $compiledPattern = preg_replace_callback(
            '/{([^}]+)}/',
            function ($match) {
                $name = $match[1];
                // Özel pattern var mı kontrol et
                return '(?P<' . $name . '>' . ($this->wheres[$name] ?? '[^/]+') . ')';
            },
            $pattern
        );

        // Pattern'i regex'e çevir
        $this->compiledPattern = '#^' . $compiledPattern . '$#';

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(string $path): bool
    {
        $compiledPattern = $this->getCompiledPattern();

        return preg_match($compiledPattern, $path, $this->parameters) === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request, array $parameters = []): ResponseInterface
    {
        // Parametreleri ayarla
        if (!empty($parameters)) {
            $this->parameters = $parameters;
        }

        // Route parametrelerini request'e ekle
        foreach ($this->parameters as $name => $value) {
            if (is_string($name)) {
                $request = $request->withAttribute($name, $value);
            }
        }

        // Handler bir Closure ise doğrudan çalıştır
        if ($this->handler instanceof Closure) {
            $response = call_user_func($this->handler, $request, ...$this->getParamValues());

            // Eğer response zaten bir ResponseInterface ise direkt döndür
            if ($response instanceof ResponseInterface) {
                return $response;
            }

            // String veya array dönüş değerini response'a çevir
            if (is_string($response)) {
                return new Response(200, ['Content-Type' => 'text/html'], $response);
            }

            if (is_array($response)) {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
            }

            // Diğer tüm durumlar için boş response
            return new Response();
        }

        // Handler bir Controller metodu ise
        if (is_array($this->handler) || is_string($this->handler)) {
            // String controller#method formatını array'e çevir
            if (is_string($this->handler) && strpos($this->handler, '#') !== false) {
                [$controller, $method] = explode('#', $this->handler);
                $this->handler = [$controller, $method];
            }

            // Controller sınıfını oluştur
            if (is_array($this->handler) && is_string($this->handler[0])) {
                $controller = new $this->handler[0]();
                $method = $this->handler[1];

                $response = $controller->$method($request, ...$this->getParamValues());

                // Response döndürme mantığı aynı
                if ($response instanceof ResponseInterface) {
                    return $response;
                }

                if (is_string($response)) {
                    return new Response(200, ['Content-Type' => 'text/html'], $response);
                }

                if (is_array($response)) {
                    return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
                }

                return new Response();
            }
        }

        // Handler çalıştırılamadı
        throw new RuntimeException("Could not execute route handler");
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl(array $parameters = []): string
    {
        $path = $this->getPattern();

        // Parametre isimlerini regex ile bul
        preg_match_all('/{([^}]+)}/', $path, $matches);

        // Her bir parametreyi değiştir
        foreach ($matches[0] as $index => $match) {
            $name = $matches[1][$index];

            if (!isset($parameters[$name])) {
                throw new InvalidArgumentException("Missing parameter '$name' for route '{$this->name}'");
            }

            $path = str_replace($match, (string) $parameters[$name], $path);
            unset($parameters[$name]);
        }

        // Kalan parametreleri query string olarak ekle
        if (!empty($parameters)) {
            $path .= '?' . http_build_query($parameters);
        }

        return $path;
    }

    /**
     * Route handler'ı için parametre değerlerini döndürür.
     *
     * @return array<mixed> Parametre değerleri
     */
    protected function getParamValues(): array
    {
        $values = [];

        foreach ($this->paramNames as $name) {
            $values[] = $this->parameters[$name] ?? null;
        }

        return $values;
    }

    /**
     * Route'un belirlenen HTTP metodu ile çalışıp çalışmayacağını kontrol eder.
     *
     * @param string $method Kontrol edilecek HTTP metodu
     * @return bool HTTP metodu destekleniyorsa true
     */
    public function supportsMethod(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods);
    }
}