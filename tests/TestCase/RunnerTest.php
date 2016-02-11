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

    public function testRunSequencing()
    {
        $this->stack->push($this->ok);
        $this->markTestIncomplete();
    }

    public function testRunExceptionInMiddleware()
    {
        $this->markTestIncomplete();
    }

    public function testRunNextNotCalled()
    {
        $this->markTestIncomplete();
    }
}
