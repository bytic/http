<?php

namespace Nip\Http\Request;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

class PsrRequest implements BaseRequest, ServerRequestInterface
{
    use Traits\PsrBridgeTrait;

    public FoundationRequest $symfonyRequest;

    public function __construct(FoundationRequest $symfonyRequest, bool $cloneRequest = true)
    {
        $this->symfonyRequest = $cloneRequest ? clone $symfonyRequest : $symfonyRequest;
    }

    public function getSymfonyRequest(): FoundationRequest
    {
        return $this->symfonyRequest;
    }

    public function getPSRRequest(): ServerRequestInterface
    {
        return $this;
    }

    public function __get(string $name)
    {
        return $this->symfonyRequest->{$name};
    }

    public function setMethod($method): void
    {
        $this->symfonyRequest->setMethod($method);
        if ($this->psrRequest !== null) {
            $this->psrRequest = $this->psrRequest->withMethod($this->symfonyRequest->getMethod());
        }
    }

    public function __call(string $name, array $arguments)
    {
        return $this->symfonyRequest->{$name}(...$arguments);
    }

    public function __clone()
    {
        $this->symfonyRequest = clone $this->symfonyRequest;
    }

    public function getParsedBody()
    {
        if ($this->parsedBodySet) {
            return $this->parsedBody;
        }

        $contentType = strtolower($this->symfonyRequest->headers->get('Content-Type', ''));
        if (str_starts_with($contentType, 'application/json')) {
            if ($this->symfonyRequest instanceof SymfonyRequest) {
                return $this->symfonyRequest->json()->all();
            }

            $parsed = json_decode($this->symfonyRequest->getContent(), true);

            return is_array($parsed) || is_object($parsed) ? $parsed : null;
        }

        $body = $this->symfonyRequest->request->all();

        return $body || ($this->getMethod() === 'POST'
            && (str_starts_with($contentType, 'application/x-www-form-urlencoded')
                || str_starts_with($contentType, 'multipart/form-data'))) ? $body : null;
    }
}
