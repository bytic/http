<?php

namespace Nip\Http\Tests\Response;

use Nip\Http\Response\Response;
use Nip\Http\Tests\AbstractTest;
use Nip\Http\Tests\Fixtures\Response\ObjectAwareResponse;

/**
 * Class ResponseAwareTraitTest
 * @package Nip\Http\Tests\Response
 */
class ResponseAwareTraitTest extends AbstractTest
{
    public function testGetResponseAutoInitParam()
    {
        $object = new ObjectAwareResponse();
        self::assertFalse($object->hasResponse());

        self::assertNull($object->getResponse());

        $response = $object->getResponse(true);
        self::assertInstanceOf(Response::class, $response);
        self::assertSame($response, $object->getResponse());
    }
}