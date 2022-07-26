<?php

namespace Nip\Http\Kernel\Traits;

use Exception;
use Nip\Http\Exceptions\Handler;
use Nip\Http\Response\Response;
use Nip\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Trait HandleExceptions
 * @package Nip\Http\Kernel\Traits
 */
trait HandleExceptions
{
    /**
     * Report the exception to the exception handler.
     *
     * @param Exception $e
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        app('log')->error($e);
    }

    /**
     * @param Request|ServerRequestInterface $request
     * @param Exception $e
     * @return Response|ResponseInterface
     */
    protected function renderException($request, Throwable $e)
    {
        return (new Handler($this->getContainer()))->render($request, $e);
    }

    /**
     * @param Exception $e
     * @param Request|ServerRequestInterface $request
     * @return Response
     */
    protected function handleException($request, Throwable $e)
    {
        $this->reportException($e);

        return $this->renderException($request, $e);
    }
}
