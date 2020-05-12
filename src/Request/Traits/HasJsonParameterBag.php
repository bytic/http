<?php

namespace Nip\Http\Request\Traits;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Trait HasJsonParameterBag
 * @package Nip\Http\Request\Traits
 */
trait HasJsonParameterBag
{

    /**
     * The decoded JSON content for the request.
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag|null
     */
    protected $json;

    /**
     * Get the JSON payload for the request.
     *
     * @param string|null $key
     * @param mixed $default
     * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
     */
    public function json($key = null, $default = null)
    {
        if (!isset($this->json)) {
            $this->json = new ParameterBag((array)json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }

        return data_get($this->json->all(), $key, $default);
    }
}
