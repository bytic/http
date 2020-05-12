<?php

namespace Nip\Http\Kernel\Traits;

use Nip\Application;

/**
 * Trait HasApplication
 * @package Nip\Http\Kernel\Traits
 */
trait HasApplication
{

    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * Get the application instance.
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * @return \Nip\Container\Container
     */
    public function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
