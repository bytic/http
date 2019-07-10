<?php

namespace Nip\Http\Kernel\Traits;

use Exception;
use Nip\Http\Exceptions\Handler;
use Nip\Http\Response\Response;
use Nip\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Trait HandleExceptionsTrait
 * @package Nip\Http\Kernel\Traits
 */
trait HandleExceptionsTrait
{
    /**
     * Report the exception to the exception handler.
     *
     * @param Exception $e
     * @return void
     */
    protected function reportException(Exception $e)
    {
        app('log')->error($e);
    }

    /**
     * @param Request|ServerRequestInterface $request
     * @param Exception $e
     * @return Response|ResponseInterface
     */
    protected function renderException($request, Exception $e)
    {
        return (new Handler($this->getApplication()->getContainer()))->render($request, $e);
    }

    /**
     * @param Exception $e
     * @param Request|ServerRequestInterface $request
     * @return Response
     */
    protected function handleException($request, Exception $e)
    {
        $this->reportException($e);

        return $this->renderException($request, $e);
    }
}
