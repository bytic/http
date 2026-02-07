<?php

namespace Nip\Http\Tests\Kernel\Event;

use Nip\Http\Kernel\Event\ResponseEvent;
use Nip\Http\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ResponseEventTest
 * @package Nip\Http\Tests\Kernel\Event
 */
class ResponseEventTest extends AbstractTest
{
    public function testConstructor()
    {
        $request = Request::create('/test');
        $response = new Response('test');
        $event = new ResponseEvent($request, $response, HttpKernelInterface::MAIN_REQUEST);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($response, $event->getResponse());
        $this->assertSame(HttpKernelInterface::MAIN_REQUEST, $event->getRequestType());
    }

    public function testSetResponse()
    {
        $request = Request::create('/test');
        $response = new Response('test');
        $event = new ResponseEvent($request, $response, HttpKernelInterface::MAIN_REQUEST);

        $newResponse = new Response('new test');
        $event->setResponse($newResponse);

        $this->assertSame($newResponse, $event->getResponse());
        $this->assertNotSame($response, $event->getResponse());
    }
}
