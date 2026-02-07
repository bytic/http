<?php

namespace Nip\Http\Kernel\Event;

/**
 * Allows to execute logic after a request has been processed
 *
 * This event is triggered during the kernel.terminate event when the request
 * is finished processing, but before the kernel terminates.
 */
class FinishRequestEvent extends KernelEvent
{
}
