<?php

declare(strict_types=1);

namespace Framework\Core\Controller\Interfaces;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Framework\Core\Application\Interfaces\ApplicationInterface;

/**
 * Controller'ların davranışlarını tanımlayan arayüz.
 * 
 * Bu arayüz, framework'ün controller'ları için temel davranışları tanımlar.
 * Request işleme, response oluşturma, middleware yönetimi ve before/after
 * action hooks gibi temel işlevleri içerir.
 * 
 * @package Framework\Core\Controller
 * @subpackage Interfaces
 * @author [Ahmet ALTUN]
 * @version 1.0.0
 * @since 1.0.0
 */
interface ControllerInterface
{
    /**
     * Controller'ı başlatır ve uygulamayı inject eder.
     * 
     * @param ApplicationInterface $app Uygulama instance'ı
     * @return void
     */
    public function initialize(ApplicationInterface $app): void;

    /**
     * Request'i işleyip response döndürür.
     * 
     * @param ServerRequestInterface $request İşlenecek request
     * @param string $action Çağrılacak action
     * @param array<mixed> $params Action'a geçirilecek parametreler
     * @return ResponseInterface
     */
    public function handleRequest(ServerRequestInterface $request, string $action, array $params = []): ResponseInterface;

    /**
     * Before action hook'unu çalıştırır.
     * 
     * @param ServerRequestInterface $request İşlenecek request
     * @param string $action Çağrılacak action
     * @return ServerRequestInterface|false Devam etmek için request, durdurmak için false
     */
    public function beforeAction(ServerRequestInterface $request, string $action): ServerRequestInterface|false;

    /**
     * After action hook'unu çalıştırır.
     * 
     * @param ResponseInterface $response Oluşturulan response
     * @param string $action Çağrılan action
     * @return ResponseInterface Düzenlenmiş response
     */
    public function afterAction(ResponseInterface $response, string $action): ResponseInterface;

    /**
     * Middleware'leri döndürür.
     * 
     * @return array<string> Middleware listesi
     */
    public function getMiddleware(): array;

    /**
     * Action için middleware'leri döndürür.
     * 
     * @param string $action Action adı
     * @return array<string> Middleware listesi
     */
    public function getActionMiddleware(string $action): array;

    /**
     * Uygulamayı döndürür.
     * 
     * @return ApplicationInterface Uygulama instance'ı
     */
    public function getApplication(): ApplicationInterface;
}