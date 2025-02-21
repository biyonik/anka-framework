<?php

declare(strict_types=1);

namespace Framework\Core\Controller;

use Framework\Core\Application\Interfaces\ApplicationInterface;
use Framework\Core\Http\Request\Interfaces\RequestInterface;
use Framework\Core\Http\Response\Interfaces\ResponseInterface;
use Framework\Core\Container\Interfaces\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Framework'ün temel controller sınıfı.
 * 
 * Bu sınıf, framework'ün controller'ları için temel işlevselliği sağlar.
 * View rendering, redirects, validation ve diğer ortak controller işlemleri
 * için yardımcı metodlar içerir.
 * 
 * @package Framework\Core\Controller
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
class Controller extends AbstractController
{
    /**
     * View render eder.
     * 
     * @param string $view View adı
     * @param array<string,mixed> $data View'a geçirilecek veriler
     * @param int $status HTTP status kodu
     * @return ResponseInterface
     */
    protected function view(string $view, array $data = [], int $status = 200): ResponseInterface
    {
        // TODO: View sistemi oluşturulduğunda implemente edilecek
        
        // Geçici olarak JSON response döndür
        return $this->json(['view' => $view, 'data' => $data], $status);
    }

    /**
     * JSON response oluşturur.
     * 
     * @param mixed $data JSON'a dönüştürülecek veri
     * @param int $status HTTP status kodu
     * @param int $options JSON encode options
     * @return ResponseInterface
     */
    protected function json(mixed $data, int $status = 200, int $options = 0): ResponseInterface
    {
        $responseFactory = $this->app->getContainer()->get('response.factory');
        return $responseFactory->createJsonResponse($data, $status);
    }

    /**
     * Redirect response oluşturur.
     * 
     * @param string $url Redirect URL'i
     * @param int $status HTTP status kodu
     * @return ResponseInterface
     */
    protected function redirect(string $url, int $status = 302): ResponseInterface
    {
        $responseFactory = $this->app->getContainer()->get('response.factory');
        return $responseFactory->createRedirectResponse($url, $status);
    }

    /**
     * Named route'a redirect oluşturur.
     * 
     * @param string $name Route adı
     * @param array<string,mixed> $parameters Route parametreleri
     * @param int $status HTTP status kodu
     * @return ResponseInterface
     */
    protected function redirectToRoute(string $name, array $parameters = [], int $status = 302): ResponseInterface
    {
        $url = $this->app->getRouter()->route($name, $parameters);
        return $this->redirect($url, $status);
    }

    /**
     * Request objesini döndürür.
     * 
     * @param ServerRequestInterface|null $request İsteğe bağlı request
     * @return ServerRequestInterface
     */
    protected function request(?ServerRequestInterface $request = null): ServerRequestInterface
    {
        if ($request !== null) {
            return $request;
        }
        
        return $this->app->getContainer()->get(RequestInterface::class);
    }

    /**
     * Container objesini döndürür.
     * 
     * @return ContainerInterface
     */
    protected function container(): ContainerInterface
    {
        return $this->app->getContainer();
    }

    /**
     * Servis çağırır.
     * 
     * @param string $id Servis ID'si
     * @param array<string,mixed> $parameters Servis parametreleri
     * @return mixed Servis
     */
    protected function service(string $id, array $parameters = []): mixed
    {
        return $this->container()->get($id, $parameters);
    }

    /**
     * Flash mesajı ekler.
     * 
     * @param string $type Mesaj tipi (success, error, warning, info)
     * @param string $message Mesaj
     * @return static
     */
    protected function flash(string $type, string $message): static
    {
        // TODO: Session sistemi oluşturulduğunda implemente edilecek
        return $this;
    }

    /**
     * Form validation yapar.
     * 
     * @param array<string,mixed> $data Validate edilecek veriler
     * @param array<string,mixed> $rules Validation kuralları
     * @return array<string,string>|true Hata mesajları veya başarılı ise true
     */
    protected function validate(array $data, array $rules): array|true
    {
        // TODO: Validation sistemi oluşturulduğunda implemente edilecek
        return true;
    }
}