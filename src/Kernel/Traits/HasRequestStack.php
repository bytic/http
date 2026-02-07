<?php

namespace Nip\Http\Kernel\Traits;

use Nip\Http\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adds request stack management to the kernel
 */
trait HasRequestStack
{
    /**
     * @var RequestStack|null
     */
    protected $requestStack;

    /**
     * Get the request stack instance.
     *
     * @return RequestStack
     */
    public function getRequestStack(): RequestStack
    {
        if ($this->requestStack === null) {
            $this->requestStack = new RequestStack();
        }

        return $this->requestStack;
    }

    /**
     * Set the request stack instance.
     *
     * @param RequestStack $requestStack
     * @return $this
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * Push a request onto the stack.
     *
     * @param Request $request
     */
    protected function pushRequest(Request $request): void
    {
        $this->getRequestStack()->push($request);
    }

    /**
     * Pop a request from the stack.
     *
     * @return Request|null
     */
    protected function popRequest(): ?Request
    {
        return $this->getRequestStack()->pop();
    }

    /**
     * Get the current request from the stack.
     *
     * @return Request|null
     */
    protected function getCurrentRequest(): ?Request
    {
        return $this->getRequestStack()->getCurrentRequest();
    }

    /**
     * Get the main request from the stack.
     *
     * @return Request|null
     */
    protected function getMainRequest(): ?Request
    {
        return $this->getRequestStack()->getMainRequest();
    }
}
