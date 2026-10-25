<?php

namespace Nip\Http\Kernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base class for kernel events
 */
abstract class KernelEvent extends Event
{
    protected Request $request;
    protected int $requestType;

    public function __construct(Request $request, int $requestType)
    {
        $this->request = $request;
        $this->requestType = $requestType;
    }

    /**
     * Returns the request the kernel is currently processing.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the request type the kernel is currently processing.
     */
    public function getRequestType(): int
    {
        return $this->requestType;
    }

    /**
     * Checks if this is a main request.
     */
    public function isMainRequest(): bool
    {
        return $this->requestType === HttpKernelInterface::MAIN_REQUEST;
    }

    /**
     * Checks if this is a main request.
     * @deprecated since version 2.1, use isMainRequest() instead
     */
    public function isMasterRequest(): bool
    {
        return $this->isMainRequest();
    }
}
