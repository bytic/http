<?php

namespace Nip\Http\Kernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Allows to filter a Response object
 *
 * You can call getResponse() to retrieve the current response. With
 * setResponse() you can set a new response that will be returned to the browser.
 */
class ResponseEvent extends KernelEvent
{
    protected Response $response;

    public function __construct(Request $request, Response $response, int $requestType)
    {
        parent::__construct($request, $requestType);
        $this->response = $response;
    }

    /**
     * Returns the current response object.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Sets a new response object.
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
