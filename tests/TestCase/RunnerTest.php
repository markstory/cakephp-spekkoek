<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Spekkoek\MiddlewareStack;
use Spekkoek\Runner;

class RunnerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->stack = new MiddlewareStack();

        $this->ok = function ($req, $res, $next) {
            return $next($req, $res);
        };
        $this->pass = function ($req, $res, $next) {
            return $next($req, $res);
        };
        $this->noNext = function ($req, $res, $next) {
        };
        $this->fail = function ($req, $res, $next) {
            throw new \RuntimeException('A bad thing');
        };
    }

    public function testRunSingle()
    {
        $this->stack->push($this->ok);
        $req = $this->getMock('Psr\Http\RequestInterface');
        $res = $this->getMock('Psr\Http\ResponseInterface');

        $runner = new Runner();
        $result = $runner->run($this->stack, $req, $res);
        $this->assertSame($res, $result);
    }

    public function testRunResponseReplace()
    {
        $one = function ($req, $res, $next) {
            $res = $this->getMock('Psr\Http\ResponseInterface');
            return $next($req, $res);
        };
        $this->stack->push($one);
        $runner = new Runner();

        $req = $this->getMock('Psr\Http\RequestInterface');
        $res = $this->getMock('Psr\Http\ResponseInterface');
        $result = $runner->run($this->stack, $req, $res);

        $this->assertNotSame($res, $result, 'Response was not replaced');
        $this->assertInstanceOf('Psr\Http\ResponseInterface', $result);
    }

    public function testRunSequencing()
    {
        $log = [];
        $one = function ($req, $res, $next) use (&$log) {
            $log[] = 'one';
            return $next($req, $res);
        };
        $two = function ($req, $res, $next) use (&$log) {
            $log[] = 'two';
            return $next($req, $res);
        };
        $three = function ($req, $res, $next) use (&$log) {
            $log[] = 'three';
            return $next($req, $res);
        };
        $this->stack->push($one)->push($two)->push($three);
        $runner = new Runner();

        $req = $this->getMock('Psr\Http\RequestInterface');
        $res = $this->getMock('Psr\Http\ResponseInterface');
        $result = $runner->run($this->stack, $req, $res);

        $this->assertSame($res, $result, 'Response is not correct');

        $expected = ['one', 'two', 'three'];
        $this->assertEquals($expected, $log);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage A bad thing
     */
    public function testRunExceptionInMiddleware()
    {
        $this->stack->push($this->ok)->push($this->fail);
        $req = $this->getMock('Psr\Http\RequestInterface');
        $res = $this->getMock('Psr\Http\ResponseInterface');

        $runner = new Runner();
        $runner->run($this->stack, $req, $res);
    }

    public function testRunNextNotCalled()
    {
        $this->stack->push($this->noNext);
        $req = $this->getMock('Psr\Http\RequestInterface');
        $res = $this->getMock('Psr\Http\ResponseInterface');

        $runner = new Runner();
        $result = $runner->run($this->stack, $req, $res);
        $this->assertNull($result);
    }
}
