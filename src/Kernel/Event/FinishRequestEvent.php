<?php

namespace Nip\Http\Kernel\Event;

/**
 * Allows to execute logic after a request has been processed
 *
 * This event is triggered when a request finishes processing.
 * It's typically used to reset global state or clean up resources
 * after handling a sub-request.
 */
class FinishRequestEvent extends KernelEvent
{
}
