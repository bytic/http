<?php

namespace Nip\Http\Tests\Exceptions;

use Nip\Config\Config;
use Nip\Container\Container;
use Nip\Http\Tests\AbstractTest;
use Nip\Http\Exceptions\Handler;

/**
 * Class HandlerTest
 * @package Nip\Http\Tests\Exceptions
 */
class HandlerTest extends AbstractTest
{
    /**
     * @dataProvider dataIsDebug
     */
    public function testIsDebug($value, $debug)
    {
        $container = new Container();
        Container::setInstance($container);
        \Nip\Container\Utility\Container::container(true);

        $config = new Config(['app' => ['debug' => $value]]);
        $container->set('config', $config);

        $handler = new Handler($container);
        self::assertSame($debug, $handler->isDebug());
    }

    /**
     * @return array
     */
    public function dataIsDebug()
    {
        return [
            ['', false],
            ['false', false],
            [false, false],
            [0, false],
            [1, true],
            ['true', true],
            [true, true],
        ];
    }
}

