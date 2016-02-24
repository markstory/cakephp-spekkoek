<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Spekkoek\Server;
use Spekkoek\Test\TestApp\BadResponseApplication;
use Spekkoek\Test\TestApp\InvalidMiddlewareApplication;
use Spekkoek\Test\TestApp\MiddlewareApplication;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

class ServerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->server = $_SERVER;
        $this->config = dirname(__DIR__);
    }

    public function tearDown()
    {
        parent::tearDown();
        $_SERVER = $this->server;
    }

    public function testAppGetSet()
    {
        $app = $this->getMock('Spekkoek\BaseApplication', [], [$this->config]);
        $server = new Server($app);
        $this->assertSame($app, $server->getApp($app));
    }

    public function testRunWithRequestAndResponse()
    {
        $response = new Response('php://memory', 200, ['X-testing' => 'source header']);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('X-pass', 'request header');

        $app = new MiddlewareApplication($this->config);
        $server = new Server($app);
        $res = $server->run($request, $response);
        $this->assertEquals(
            'source header',
            $res->getHeaderLine('X-testing'),
            'Input response is carried through out middleware'
        );
        $this->assertEquals(
            'request header',
            $res->getHeaderLine('X-pass'),
            'Request is used in middleware'
        );
    }

    public function testRunWithGlobals()
    {
        $_SERVER['HTTP_X_PASS'] = 'globalvalue';

        $app = new MiddlewareApplication($this->config);
        $server = new Server($app);

        $res = $server->run();
        $this->assertEquals(
            'globalvalue',
            $res->getHeaderLine('X-pass'),
            'Default request is made from server'
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The application `middleware` method
     */
    public function testRunWithApplicationNotMakingMiddleware()
    {
        $app = new InvalidMiddlewareApplication($this->config);
        $server = new Server($app);
        $server->run();
    }

    public function testRunMultipleMiddlewareSuccess()
    {
        $app = new MiddlewareApplication($this->config);
        $server = new Server($app);
        $res = $server->run();
        $this->assertSame('first', $res->getHeaderLine('X-First'));
        $this->assertSame('second', $res->getHeaderLine('X-Second'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Application did not create a response. Got "Not a response" instead.
     */
    public function testRunMiddlewareNoResponse()
    {
        $app = new BadResponseApplication($this->config);
        $server = new Server($app);
        $server->run();
    }

    /**
     * Test that emit invokes the appropriate methods on the emitter.
     *
     * @return void
     */
    public function testEmit()
    {
        $response = new Response('php://memory', 200, ['x-testing' => 'source header']);
        $final = $response
            ->withHeader('X-First', 'first')
            ->withHeader('X-Second', 'second');

        $emitter = $this->getMock('Zend\Diactoros\Response\EmitterInterface');
        $emitter->expects($this->once())
            ->method('emit')
            ->with($final);

        $app = new MiddlewareApplication($this->config);
        $server = new Server($app);
        $server->emit($server->run(null, $response), $emitter);
    }
}
