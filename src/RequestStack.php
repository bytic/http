<?php

namespace Nip\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack as SymfonyRequestStack;

/**
 * Request stack that keeps track of requests.
 *
 * This class extends Symfony's RequestStack to provide a way to access
 * the current request and manage sub-requests.
 */
class RequestStack extends SymfonyRequestStack
{
    /**
     * Gets the main request.
     *
     * @return Request|null The main request
     */
    public function getMainRequest(): ?Request
    {
        return parent::getMainRequest();
    }

    /**
     * Gets the main request.
     *
     * @deprecated since version 2.1, use getMainRequest() instead
     * @return Request|null The main request
     */
    public function getMasterRequest(): ?Request
    {
        return $this->getMainRequest();
    }
}
