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
     * @var boolean
     */
    protected $autoInitRequest = false;

    /**
     * Get the Request.
     *
     * @return Request
     */
    public function getRequest()
    {
        if ($this->request == null && $this->isAutoInitRequest()) {
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

    /**
     * @return bool
     */
    public function hasRequest()
    {
        return $this->request instanceof RequestInterface;
    }

    /**
     * @return bool
     */
    public function isAutoInitRequest(): bool
    {
        return $this->autoInitRequest;
    }

    /**
     * @param bool $autoInitRequest
     */
    public function setAutoInitRequest(bool $autoInitRequest)
    {
        $this->autoInitRequest = $autoInitRequest;
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
