<?php

namespace Nip\Http\Kernel\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as SymfonyControllerResolver;

/**
 * ControllerResolver
 *
 * This class extends Symfony's ControllerResolver to resolve controllers
 * from the request in a Symfony-compliant way.
 */
class ControllerResolver extends SymfonyControllerResolver
{
    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger A LoggerInterface instance
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
    }

    /**
     * Returns the Controller instance associated with a Request.
     *
     * This method looks for a _controller request attribute that represents
     * the controller name (a string like ClassName::MethodName).
     *
     * @param Request $request A Request instance
     *
     * @return callable|false A PHP callable representing the Controller,
     *                        or false if this resolver is not able to determine the controller
     */
    public function getController(Request $request): callable|false
    {
        if (!$controller = $request->attributes->get('_controller')) {
            return false;
        }

        return parent::getController($request);
    }
}
