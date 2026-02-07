<?php

namespace Nip\Http\Kernel\Traits;

use Nip\Http\Kernel\Controller\ArgumentResolver;
use Nip\Http\Kernel\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * Adds controller resolution capabilities to the kernel
 */
trait HasControllerResolver
{
    /**
     * @var ControllerResolverInterface|null
     */
    protected $controllerResolver;

    /**
     * @var ArgumentResolverInterface|null
     */
    protected $argumentResolver;

    /**
     * Get the controller resolver instance.
     *
     * @return ControllerResolverInterface
     */
    public function getControllerResolver(): ControllerResolverInterface
    {
        if ($this->controllerResolver === null) {
            $this->controllerResolver = $this->createControllerResolver();
        }

        return $this->controllerResolver;
    }

    /**
     * Set the controller resolver instance.
     *
     * @param ControllerResolverInterface $resolver
     * @return $this
     */
    public function setControllerResolver(ControllerResolverInterface $resolver)
    {
        $this->controllerResolver = $resolver;

        return $this;
    }

    /**
     * Get the argument resolver instance.
     *
     * @return ArgumentResolverInterface
     */
    public function getArgumentResolver(): ArgumentResolverInterface
    {
        if ($this->argumentResolver === null) {
            $this->argumentResolver = $this->createArgumentResolver();
        }

        return $this->argumentResolver;
    }

    /**
     * Set the argument resolver instance.
     *
     * @param ArgumentResolverInterface $resolver
     * @return $this
     */
    public function setArgumentResolver(ArgumentResolverInterface $resolver)
    {
        $this->argumentResolver = $resolver;

        return $this;
    }

    /**
     * Create a new controller resolver instance.
     *
     * @return ControllerResolverInterface
     */
    protected function createControllerResolver(): ControllerResolverInterface
    {
        return new ControllerResolver();
    }

    /**
     * Create a new argument resolver instance.
     *
     * @return ArgumentResolverInterface
     */
    protected function createArgumentResolver(): ArgumentResolverInterface
    {
        $container = method_exists($this, 'getApplication') 
            ? $this->getApplication()->getContainer() 
            : null;

        return new ArgumentResolver(null, [], $container);
    }

    /**
     * Resolve the controller from the request.
     *
     * @param Request $request
     * @return callable|false
     */
    protected function resolveController(Request $request): callable|false
    {
        return $this->getControllerResolver()->getController($request);
    }

    /**
     * Resolve the arguments for the controller.
     *
     * @param Request $request
     * @param callable $controller
     * @return array
     */
    protected function resolveArguments(Request $request, callable $controller): array
    {
        return $this->getArgumentResolver()->getArguments($request, $controller);
    }
}
