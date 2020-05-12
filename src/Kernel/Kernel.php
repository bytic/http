<?php

namespace Nip\Http\Kernel;

use Exception;
use Nip\Application\ApplicationInterface;
use Nip\Dispatcher\ActionDispatcherMiddleware;
use Nip\Http\Kernel\Traits\HandleExceptionsTrait;
use Nip\Http\Response\Response;
use Nip\Http\ServerMiddleware\Dispatcher;
use Nip\Http\ServerMiddleware\Traits\HasServerMiddleware;
use Nip\Request;
use Nip\Router\Middleware\RouteResolverMiddleware;
use Nip\Router\Router;
use Nip\Session\Middleware\StartSession;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Class Kernel
 * @package Nip\Http\Kernel
 */
class Kernel implements KernelInterface
{
    use Traits\HandleExceptions;
    use Traits\HasApplication;

    use HasServerMiddleware;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param ApplicationInterface $app
     * @param Router $router
     */
    public function __construct(ApplicationInterface $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;

        $this->pushMiddleware(StartSession::class);
        $this->pushMiddleware(RouteResolverMiddleware::class);
        $this->pushMiddleware(ActionDispatcherMiddleware::class);
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param SymfonyRequest $request
     * @param int $type
     * @param bool $catch
     * @return ResponseInterface
     */
    public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            $this->getApplication()->share('request', $request);
            return $this->handleRaw($request, $type);
        } catch (Exception $e) {
            $this->reportException($e);
            $response = $this->renderException($request, $e);
        } catch (Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $response = $this->renderException($request, $e);
        }
//        event(new Events\RequestHandled($request, $response));
        return $response;
    }

    /**
     * Handles a request to convert it to a response.
     *
     * @param ServerRequestInterface $request A Request instance
     * @param int $type The type of the request
     *
     * @return ResponseInterface A Response instance
     *
     * @throws \LogicException       If one of the listener does not behave as expected
     * @throws NotFoundHttpException When controller cannot be found
     */
    protected function handleRaw(ServerRequestInterface $request, $type = self::MASTER_REQUEST)
    {
        return (
        new Dispatcher($this->middleware, $this->getApplication()->getContainer())
        )->dispatch($request);
    }
    /**
     * @param Request $request
     * @param Response $response
     */
    public function terminate(RequestInterface $request, ResponseInterface $response)
    {
        $this->terminateMiddleware($request, $response);
        $this->getApplication()->terminate();
    }

    public function postRouting()
    {
    }
}
