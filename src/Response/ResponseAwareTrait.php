<?php

namespace Nip\Http\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ContainerAwareTrait
 * @package Nip\Container
 */
trait ResponseAwareTrait
{
    /**
     * @var Response|null
     */
    protected $response = null;

    /**
     * @var boolean
     */
    protected $autoInitResponse = false;

    /**
     * Get the container.
     *
     * @return Response
     */
    public function getResponse()
    {
        if ($this->response == null && $this->isAutoInitResponse()) {
            $this->initResponse();
        }

        return $this->response;
    }

    /**
     * Set a container.
     *
     * @param Response|ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return $this->response instanceof ResponseInterface;
    }

    /**
     * @return bool
     */
    public function isAutoInitResponse(): bool
    {
        return $this->autoInitResponse;
    }

    /**
     * @param bool $autoInitResponse
     */
    public function setAutoInitResponse(bool $autoInitResponse)
    {
        $this->autoInitResponse = $autoInitResponse;
    }

    public function initResponse()
    {
        $this->response = $this->newResponse();
    }

    /**
     * @return Response
     */
    public function newResponse()
    {
        return new Response();
    }
}
