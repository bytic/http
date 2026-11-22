<?php

namespace Nip\Http\Request;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

interface BaseRequest
{
    public function getSymfonyRequest(): Request;

    public function getPSRRequest(): ServerRequestInterface;
}
