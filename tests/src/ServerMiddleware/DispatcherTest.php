<?php

namespace Nip\Http\Tests\ServerMiddleware;

use Nip\Http\ServerMiddleware\Dispatcher;
use Nip\Http\Tests\AbstractTest;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherTest extends AbstractTest
{
    public function testDispatchesMiddlewareBeforeTheTerminalHandler(): void
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = $handler->handle($request);

                return $response->withHeader('X-Middleware', 'executed');
            }
        };
        $terminalHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(204);
            }
        };

        $response = (new Dispatcher([$middleware]))->process(
            new ServerRequest('GET', '/'),
            $terminalHandler
        );

        static::assertSame(204, $response->getStatusCode());
        static::assertSame('executed', $response->getHeaderLine('X-Middleware'));
    }
}
