<?php

namespace Nip\Http\Kernel\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver as SymfonyArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;

/**
 * ArgumentResolver
 *
 * This class extends Symfony's ArgumentResolver to resolve controller arguments
 * from the request in a Symfony-compliant way.
 */
class ArgumentResolver extends SymfonyArgumentResolver
{
    protected ?ContainerInterface $container;

    /**
     * Constructor.
     *
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     * @param iterable<ArgumentValueResolverInterface> $argumentValueResolvers
     * @param ContainerInterface|null $container
     */
    public function __construct(
        ArgumentMetadataFactoryInterface $argumentMetadataFactory = null,
        iterable $argumentValueResolvers = [],
        ContainerInterface $container = null
    ) {
        parent::__construct(
            $argumentMetadataFactory ?? new ArgumentMetadataFactory(),
            $argumentValueResolvers
        );
        
        $this->container = $container;
    }

    /**
     * Returns the arguments to pass to the controller.
     *
     * @param Request $request A Request instance
     * @param callable $controller A PHP callable
     *
     * @return array An array of arguments to pass to the controller
     */
    public function getArguments(Request $request, callable $controller): array
    {
        return parent::getArguments($request, $controller);
    }

    /**
     * Get the container instance.
     *
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set the container instance.
     *
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
