<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Spekkoek\Server;
use Spekkoek\Test\TestApp\BadResponseApplication;
use Spekkoek\Test\TestApp\InvalidMiddlewareApplication;

class ServerTest extends TestCase
{
    public function testAppGetSet()
    {
        $app = $this->getMock('Spekkoek\BaseApplication');
        $server = new Server($app);
        $this->assertSame($app, $server->getApp($app));
    }

    public function testRunWithRequestAndResponse()
    {
        $this->markTestIncomplete();
    }

    public function testRunWithGlobals()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The application `middleware` method
     */
    public function testRunWithApplicationNotMakingMiddleware()
    {
        $app = new InvalidMiddlewareApplication();
        $server = new Server($app);
        $server->run();
    }

    public function testRunMultipleMiddlewareSuccess()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Application did not create a response. Got "Not a response" instead.
     */
    public function testRunMiddlewareNoResponse()
    {
        $app = new BadResponseApplication();
        $server = new Server($app);
        $server->run();
    }
}
