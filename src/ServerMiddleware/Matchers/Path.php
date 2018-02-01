<?php

namespace Nip\Http\ServerMiddleware\Matchers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Path
 * @package Nip\Http\ServerMiddleware\Matchers
 */
class Path implements MatcherInterface
{
    private $path;
    private $result = true;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        if ($path[0] === '!') {
            $this->result = false;
            $path = substr($path, 1);
        }
        $this->path = rtrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function match(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        return (($path === $this->path) || stripos($path, $this->path . '/') === 0) === $this->result;
    }
}
