<?php

namespace Nip\Http\Tests\Kernel\Event;

use Nip\Http\Kernel\Event\RequestEvent;
use Nip\Http\Tests\AbstractTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class RequestEventTest
 * @package Nip\Http\Tests\Kernel\Event
 */
class RequestEventTest extends AbstractTest
{
    public function testConstructor()
    {
        $request = Request::create('/test');
        $event = new RequestEvent($request, HttpKernelInterface::MAIN_REQUEST);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame(HttpKernelInterface::MAIN_REQUEST, $event->getRequestType());
        $this->assertTrue($event->isMainRequest());
        $this->assertFalse($event->hasResponse());
        $this->assertNull($event->getResponse());
    }

    public function testSetResponse()
    {
        $request = Request::create('/test');
        $event = new RequestEvent($request, HttpKernelInterface::MAIN_REQUEST);
        
        $response = new Response('test');
        $event->setResponse($response);

        $this->assertTrue($event->hasResponse());
        $this->assertSame($response, $event->getResponse());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testIsMainRequest()
    {
        $request = Request::create('/test');
        
        $mainEvent = new RequestEvent($request, HttpKernelInterface::MAIN_REQUEST);
        $this->assertTrue($mainEvent->isMainRequest());
        
        $subEvent = new RequestEvent($request, HttpKernelInterface::SUB_REQUEST);
        $this->assertFalse($subEvent->isMainRequest());
    }

    public function testIsMasterRequestDeprecated()
    {
        $request = Request::create('/test');
        $event = new RequestEvent($request, HttpKernelInterface::MAIN_REQUEST);

        // Test deprecated method still works
        $this->assertTrue($event->isMasterRequest());
    }
}
