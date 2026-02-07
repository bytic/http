<?php

namespace Nip\Http\Kernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Allows to execute logic after a response was sent
 *
 * Since the response was already sent to the client, the response cannot be
 * changed.
 */
class TerminateEvent extends KernelEvent
{
    protected Response $response;

    public function __construct(Request $request, Response $response, int $requestType = HttpKernelInterface::MAIN_REQUEST)
    {
        parent::__construct($request, $requestType);
        $this->response = $response;
    }

    /**
     * Returns the response for the current request.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
