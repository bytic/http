<?php

namespace Nip\Http\Kernel;

use Exception;
use Nip\Application\ApplicationInterface;
use Nip\Dispatcher\ActionDispatcherMiddleware;
use Nip\Http\Kernel\Event\ExceptionEvent;
use Nip\Http\Kernel\Event\RequestEvent;
use Nip\Http\Kernel\Event\ResponseEvent;
use Nip\Http\Kernel\Event\TerminateEvent;
use Nip\Http\Kernel\Traits\HandleExceptionsTrait;
use Nip\Http\ServerMiddleware\Dispatcher;
use Nip\Http\ServerMiddleware\Traits\HasServerMiddleware;
use Nip\Router\Middleware\RouteResolverMiddleware;
use Nip\Router\Router;
use Nip\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

/**
 * Class Kernel
 * @package Nip\Http\Kernel
 */
class Kernel implements KernelInterface
{
    use Traits\HandleExceptions;
    use Traits\HasApplication;
    use Traits\HasEventDispatcher;
    use Traits\HasRequestStack;

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
     * Get the route middleware groups.
     *
     * @return array
     */
    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }

    /**
     * Register a middleware group.
     *
     * @param string $name
     * @param array $middleware
     * @return $this
     */
    public function middlewareGroup(string $name, array $middleware)
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    /**
     * Get the route middleware.
     *
     * @return array
     */
    public function getRouteMiddleware(): array
    {
        return $this->routeMiddleware;
    }

    /**
     * Register a route middleware.
     *
     * @param string $name
     * @param string $middleware
     * @return $this
     */
    public function routeMiddleware(string $name, string $middleware)
    {
        $this->routeMiddleware[$name] = $middleware;

        return $this;
    }

    /**
     * Get a middleware instance by name.
     *
     * @param string $name
     * @return string|null
     */
    public function getMiddleware(string $name): ?string
    {
        return $this->routeMiddleware[$name] ?? null;
    }

    /**
     * Get the middleware instances for a group.
     *
     * @param string $group
     * @return array
     */
    public function getMiddlewareGroup(string $group): array
    {
        return $this->middlewareGroups[$group] ?? [];
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param SymfonyRequest $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(
        SymfonyRequest $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true
    ): Response {
        // Push request to stack for tracking
        $this->pushRequest($request);
        
        try {
            $this->getApplication()->share('request', $request);
            
            // Dispatch REQUEST event (Symfony way)
            $event = new RequestEvent($request, $type);
            $this->dispatchEvent($event, KernelEvents::REQUEST);
            
            // If a response was set during the REQUEST event, return it
            if ($event->hasResponse()) {
                return $this->filterResponse($event->getResponse(), $request, $type);
            }
            
            // Handle via middleware (backward compatible)
            $response = $this->handleRaw($request, $type);
            
            // Filter response through event system
            return $this->filterResponse($response, $request, $type);
        } catch (Exception $e) {
            return $this->handleThrowable($e, $request, $type, $catch);
        } catch (Throwable $e) {
            return $this->handleThrowable($e, $request, $type, $catch);
        } finally {
            // Pop request from stack
            $this->popRequest();
        }
    }

    /**
     * Handles a request to convert it to a response.
     *
     * @param Request $request A Request instance
     * @param int $type The type of the request
     *
     * @return Response A Response instance
     *
     * @throws \LogicException       If one of the listener does not behave as expected
     * @throws NotFoundHttpException When controller cannot be found
     */
    protected function handleRaw(Request $request, $type = HttpKernelInterface::MAIN_REQUEST)
    {
        return (
        new Dispatcher($this->middleware, $this->getApplication()->getContainer())
        )->dispatch($request);
    }

    /**
     * Terminates the request/response cycle.
     *
     * Should be called after sending the response to the client.
     *
     * @param Request $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response): void
    {
        // Dispatch TERMINATE event (Symfony way)
        $event = new TerminateEvent($request, $response);
        $this->dispatchEvent($event, KernelEvents::TERMINATE);
        
        // Terminate middleware (backward compatible)
        $this->terminateMiddleware($request, $response);
        
        // Terminate application
        $this->getApplication()->terminate();
    }

    /**
     * Filters a Response object.
     *
     * @param Response $response
     * @param Request $request
     * @param int $type
     * @return Response
     */
    protected function filterResponse(Response $response, Request $request, int $type): Response
    {
        // Dispatch RESPONSE event (Symfony way)
        $event = new ResponseEvent($request, $response, $type);
        $this->dispatchEvent($event, KernelEvents::RESPONSE);

        return $event->getResponse();
    }

    /**
     * Handles a throwable by trying to convert it to a Response.
     *
     * @param Throwable $e
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     * @throws Throwable
     */
    protected function handleThrowable(Throwable $e, Request $request, int $type, bool $catch): Response
    {
        // Dispatch EXCEPTION event (Symfony way)
        $event = new ExceptionEvent($request, $e, $type);
        $this->dispatchEvent($event, KernelEvents::EXCEPTION);

        // If a response was set during the EXCEPTION event, return it
        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request, $type);
        }

        // If the exception was modified, use the new one
        $e = $event->getThrowable();

        // Report and render exception (backward compatible)
        $this->reportException($e);
        $response = $this->renderException($request, $e);

        return $this->filterResponse($response, $request, $type);
    }

    public function postRouting()
    {
    }
}
