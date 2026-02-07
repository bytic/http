<?php

namespace Nip\Http\Kernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

/**
 * Allows to create a response for a thrown exception
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 */
class ExceptionEvent extends RequestEvent
{
    protected Throwable $throwable;
    protected bool $allowCustomResponseCode = false;

    public function __construct(Request $request, Throwable $throwable, int $requestType)
    {
        parent::__construct($request, $requestType);
        $this->throwable = $throwable;
    }

    /**
     * Returns the thrown exception.
     */
    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    /**
     * Replaces the thrown exception.
     *
     * This exception will be thrown if no response is set in the event.
     */
    public function setThrowable(Throwable $exception): void
    {
        $this->throwable = $exception;
    }

    /**
     * Mark the event as allowing a custom response code.
     */
    public function allowCustomResponseCode(): void
    {
        $this->allowCustomResponseCode = true;
    }

    /**
     * Returns whether the event allows a custom response code.
     */
    public function isAllowingCustomResponseCode(): bool
    {
        return $this->allowCustomResponseCode;
    }
}
