<?php

namespace Nip\Http\ServerMiddleware\Matchers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Accept
 * @package Middleland\Matchers
 */
class Accept implements MatcherInterface
{
    private $accept;
    private $result = true;

    /**
     * @param string $accept
     */
    public function __construct(string $accept)
    {
        if ($accept[0] === '!') {
            $this->result = false;
            $accept = substr($accept, 1);
        }
        $this->accept = $accept;
    }

    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request): bool
    {
        return is_int(stripos($request->getHeaderLine('Accept'), $this->accept)) === $this->result;
    }
}
