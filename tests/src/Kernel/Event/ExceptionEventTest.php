<?php

namespace Nip\Http\Tests\Kernel\Event;

use Exception;
use Nip\Http\Kernel\Event\ExceptionEvent;
use Nip\Http\Tests\AbstractTest;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ExceptionEventTest
 * @package Nip\Http\Tests\Kernel\Event
 */
class ExceptionEventTest extends AbstractTest
{
    public function testConstructor()
    {
        $request = Request::create('/test');
        $exception = new Exception('test');
        $event = new ExceptionEvent($request, $exception, HttpKernelInterface::MAIN_REQUEST);

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($exception, $event->getThrowable());
        $this->assertSame(HttpKernelInterface::MAIN_REQUEST, $event->getRequestType());
        $this->assertFalse($event->hasResponse());
    }

    public function testSetThrowable()
    {
        $request = Request::create('/test');
        $exception = new Exception('test');
        $event = new ExceptionEvent($request, $exception, HttpKernelInterface::MAIN_REQUEST);

        $newException = new RuntimeException('new test');
        $event->setThrowable($newException);

        $this->assertSame($newException, $event->getThrowable());
        $this->assertNotSame($exception, $event->getThrowable());
    }

    public function testAllowCustomResponseCode()
    {
        $request = Request::create('/test');
        $exception = new Exception('test');
        $event = new ExceptionEvent($request, $exception, HttpKernelInterface::MAIN_REQUEST);

        $this->assertFalse($event->isAllowingCustomResponseCode());
        
        $event->allowCustomResponseCode();
        
        $this->assertTrue($event->isAllowingCustomResponseCode());
    }

    public function testSetResponse()
    {
        $request = Request::create('/test');
        $exception = new Exception('test');
        $event = new ExceptionEvent($request, $exception, HttpKernelInterface::MAIN_REQUEST);

        $response = new Response('error');
        $event->setResponse($response);

        $this->assertTrue($event->hasResponse());
        $this->assertSame($response, $event->getResponse());
        $this->assertTrue($event->isPropagationStopped());
    }
}
