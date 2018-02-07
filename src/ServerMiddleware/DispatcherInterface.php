<?php

namespace Nip\Http\ServerMiddleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface DispatcherInterface
 * @package Nip\Http\ServerMiddleware
 */
interface DispatcherInterface extends MiddlewareInterface, RequestHandlerInterface
{
}
