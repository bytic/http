<?php

namespace Nip\Http\Kernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Allows filtering of a controller callable
 *
 * You can call getController() to retrieve the current controller. With
 * setController() you can set a new controller that is used in the processing
 * of the request.
 *
 * Controllers should be callables.
 */
class ControllerEvent extends KernelEvent
{
    /**
     * @var callable|null
     */
    protected $controller;

    /**
     * @param callable|null $controller
     */
    public function __construct(Request $request, int $requestType, $controller = null)
    {
        parent::__construct($request, $requestType);
        $this->controller = $controller;
    }

    /**
     * Returns the current controller.
     *
     * @return callable|null
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets a new controller.
     *
     * @param callable $controller
     */
    public function setController($controller): void
    {
        $this->controller = $controller;
    }
}
