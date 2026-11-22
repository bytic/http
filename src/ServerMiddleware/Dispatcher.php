<?php

namespace Nip\Http\ServerMiddleware;

use Middlewares\Utils\Dispatcher as UtilsDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Class Dispatcher
 * @package Nip\Http\ServerMiddleware
 *
 */
class Dispatcher extends UtilsDispatcher implements DispatcherInterface
{
    private array $middleware;

    public function __construct(array $middleware, $container = null)
    {
        foreach ($middleware as $key => $item) {
            if (!is_string($item)) {
                continue;
            }

            if ($container !== null && method_exists($container, 'get')) {
                $middleware[$key] = $container->get($item);
                continue;
            }

            if (!class_exists($item)) {
                throw new RuntimeException(sprintf('Middleware class "%s" does not exist.', $item));
            }

            $middleware[$key] = new $item();
        }

        $this->middleware = $middleware;
        parent::__construct($middleware);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = $this->middleware;
        $middleware[] = new class($handler) implements MiddlewareInterface {
            public function __construct(private RequestHandlerInterface $handler)
            {
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->handler->handle($request);
            }
        };

        return (new UtilsDispatcher($middleware))->dispatch($request);
    }
}
