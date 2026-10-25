<?php

namespace Nip\Http\Kernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Allows to create a response for the return value of a controller
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 */
class ViewEvent extends RequestEvent
{
    /**
     * The controller result.
     *
     * @var mixed
     */
    protected $controllerResult;

    /**
     * @param mixed $controllerResult
     */
    public function __construct(Request $request, $controllerResult, int $requestType)
    {
        parent::__construct($request, $requestType);
        $this->controllerResult = $controllerResult;
    }

    /**
     * Returns the controller result.
     *
     * @return mixed
     */
    public function getControllerResult()
    {
        return $this->controllerResult;
    }

    /**
     * Sets a new controller result.
     *
     * @param mixed $controllerResult
     */
    public function setControllerResult($controllerResult): void
    {
        $this->controllerResult = $controllerResult;
    }
}
