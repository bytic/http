<?php

namespace Nip\Http\Kernel;

/**
 * Contains all kernel events
 *
 * This class follows Symfony's kernel event naming and dispatching conventions.
 */
final class KernelEvents
{
    /**
     * The REQUEST event occurs at the very beginning of request dispatching.
     *
     * This event allows you to create a response for a request before any
     * other code in the framework is executed.
     *
     * @Event("Nip\Http\Kernel\Event\RequestEvent")
     */
    public const REQUEST = 'kernel.request';

    /**
     * The CONTROLLER event occurs before the controller is executed.
     *
     * This event allows you to replace the controller callable.
     *
     * @Event("Nip\Http\Kernel\Event\ControllerEvent")
     */
    public const CONTROLLER = 'kernel.controller';

    /**
     * The CONTROLLER_ARGUMENTS event occurs before the controller arguments are resolved.
     *
     * This event allows you to change the arguments passed to the controller.
     *
     * @Event("Nip\Http\Kernel\Event\ControllerEvent")
     */
    public const CONTROLLER_ARGUMENTS = 'kernel.controller_arguments';

    /**
     * The VIEW event occurs when the return value of a controller is not a Response.
     *
     * This event allows you to create a response for the return value of the
     * controller.
     *
     * @Event("Nip\Http\Kernel\Event\ViewEvent")
     */
    public const VIEW = 'kernel.view';

    /**
     * The RESPONSE event occurs once a response was created for replying to a request.
     *
     * This event allows you to modify or replace the response that will be
     * replied to the request.
     *
     * @Event("Nip\Http\Kernel\Event\ResponseEvent")
     */
    public const RESPONSE = 'kernel.response';

    /**
     * The FINISH_REQUEST event occurs when a response was generated for a request.
     *
     * This event allows you to reset the global state of the application,
     * when it was changed during the request.
     *
     * @Event("Nip\Http\Kernel\Event\FinishRequestEvent")
     */
    public const FINISH_REQUEST = 'kernel.finish_request';

    /**
     * The TERMINATE event occurs once a response was sent.
     *
     * This event allows you to run expensive post-response jobs.
     *
     * @Event("Nip\Http\Kernel\Event\TerminateEvent")
     */
    public const TERMINATE = 'kernel.terminate';

    /**
     * The EXCEPTION event occurs when an uncaught exception appears.
     *
     * This event allows you to create a response for a thrown exception or
     * to modify the thrown exception.
     *
     * @Event("Nip\Http\Kernel\Event\ExceptionEvent")
     */
    public const EXCEPTION = 'kernel.exception';
}
