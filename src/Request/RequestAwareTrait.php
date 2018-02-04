<?php

namespace Nip\Http\Request;

use Nip\Request;
use Psr\Http\Message\RequestInterface;

/**
 * Trait RequestAwareTrait
 * @package Nip\Http\Request
 */
trait RequestAwareTrait
{
    /**
     * @var Request|null
     */
    protected $request = null;

    /**
     * Get the Request.
     *
     * @return Request
     */
    public function getRequest()
    {
        if ($this->request == null) {
            $this->initRequest();
        }

        return $this->request;
    }

    /**
     * Set a container.
     *
     * @param Request|RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    public function initRequest()
    {
        $this->request = $this->newRequest();
    }

    /**
     * @return Request
     */
    public function newRequest()
    {
        return new Request();
    }
}
