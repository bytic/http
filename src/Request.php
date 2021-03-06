<?php

namespace Nip\Http;

use ByTIC\RequestDetective\RequestDetective;
use Nip\Http\Request\Http;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Request
 * @package Nip
 */
class Request extends \Symfony\Component\HttpFoundation\Request implements \ArrayAccess, ServerRequestInterface
{
    use Traits\InteractsWithContentTypes;
    use Traits\InteractsWithInput;

    use Request\Traits\ArrayAccessTrait;
    use Request\Traits\HasInterface;
    use Request\Traits\HasJsonParameterBag;
    use Request\Traits\PsrBridgeTrait;
    use Request\Traits\InteractsWithUri;
    use Request\Traits\InteractsWithMca;

    /**
     * @var Http
     */
    protected $http;


    /**
     * Singleton
     *
     * @param null $newInstance
     * @return static
     */
    public static function instance($newInstance = null)
    {
        static $instance;

        if ($newInstance instanceof self) {
            $instance = $newInstance;
        }

        if (!($instance instanceof self)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Set an action parameter
     *
     * A $value of null will unset the $key if it exists
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setAttribute($key, $value)
    {
        $this->attributes->set($key, $value);

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        foreach ($params as $param => $value) {
            $this->{$param} = $value;
        }

        return $this;
    }

    /**
     * Returns Http object
     * @return Http
     */
    public function getHttp()
    {
        if (!$this->http) {
            $this->http = new Http();
            $this->http->setRequest($this);
        }

        return $this->http;
    }

    /**
     * Checks if requested URI matches $url parameter,
     * and redirects to $url if not
     *
     * @param string $url
     * @param int $code
     */
    public function checkURL($url, $code = 302)
    {
        $components = parse_url($url);
        $request = parse_url($_SERVER['REQUEST_URI']);

        if ($components['path'] != $request['path']) {
            $redirect = $url . ($request['query'] ? '?' . $request['query'] : '');

            header("Location: " . $redirect, true, $code);
            exit();
        }
    }

    /**
     * @param $url
     */
    public function redirect($url)
    {
        header("Location: " . $url);
        exit();
    }

    /**
     * @param bool $action
     * @param bool $controller
     * @param bool $module
     * @param array $params
     * @return $this
     */
    public function duplicateWithParams($action = false, $controller = false, $module = false, $params = [])
    {
        /** @var self $newRequest */
        $newRequest = $this->duplicate();
        $newRequest->setActionName($action);
        $newRequest->setControllerName($controller);
        $newRequest->setModuleName($module);
        $newRequest->attributes->add($params);

        return $newRequest;
    }

    /**
     * @return bool
     */
    public function isMalicious()
    {
        return RequestDetective::isMalicious($this);
    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');
        return $pattern == '' ? '/' : '/' . $pattern;
    }
}
