<?php

namespace Nip\Http\Exceptions;

use Exception;
use Nip\Container\Container;
use Nip\Http\Response\ResponseFactory;
use Nip\Http\ServerMiddleware\Dispatcher;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;

/**
 * Class Handler
 * @package Nip\Http\Exceptions
 */
class Handler
{
    /**
     * The container implementation.
     *
     * @var Container
     */
    protected $container;

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $request
     * @param Exception $e
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render($request, Exception $e)
    {
        if ($this->isDebug()) {
            return $this->renderWhoops($request, $e);
        }
        return $this->renderErrorController($request, $e);
    }

    /**
     * @param $request
     * @param Exception $e
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function renderWhoops($request, Exception $e)
    {
        $whoops = new WhoopsRun;
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());

        return ResponseFactory::make($whoops->handleException($e));
    }

    /**
     * @param $request
     * @param Exception $e
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function renderErrorController($request, Exception $e)
    {
        $request->setControllerName('error')->setActionName('index');

        return (
        new Dispatcher(
            [\Nip\Dispatcher\ActionDispatcherMiddleware::class],
            $this->container
        )
        )->dispatch($request);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        $config = config('app.debug');
        return $config === true || $config === 'true' || $config === '1' || $config === 1;
    }
}
