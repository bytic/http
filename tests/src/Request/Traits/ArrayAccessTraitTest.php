<?php

namespace Nip\Http\Tests\Request\Traits;

use Nip\Http\Request;
use Nip\Http\Tests\AbstractTest;

/**
 * Class ArrayAccessTraitTest
 * @package Nip\Http\Tests\Request\Traits
 */
class ArrayAccessTraitTest extends AbstractTest
{
    public function test_offsetExists()
    {
        foreach (['attributes', 'query'] as $bag) {
            $request = new Request();

            self::assertFalse(isset($request['foe']));
            $request->{$bag}->set('foe', 'doe');

            self::assertTrue(isset($request['foe']));
        }
    }
}
