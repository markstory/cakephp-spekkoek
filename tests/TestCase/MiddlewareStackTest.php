<?php
namespace Spekkoek\Test\TestCase;

use Spekkoek\MiddlewareStack;
use Cake\TestSuite\TestCase;

class MiddlewareStackTest extends TestCase
{
    public function testPushReturn()
    {
        $stack = new MiddlewareStack();
        $cb = function () {
        };
        $this->assertSame($stack, $stack->push($cb));
    }

    public function testPushOrdering()
    {
        $one = function () {
        };
        $two = function () {
        };

        $stack = new MiddlewareStack();
        $this->assertCount(0, $stack);

        $stack->push($one);
        $this->assertCount(1, $stack);

        $stack->push($two);
        $this->assertCount(2, $stack);

        $this->assertSame($one, $stack->get(0));
        $this->assertSame($two, $stack->get(1));
    }

    public function testPrependReturn()
    {
        $cb = function () {
        };
        $stack = new MiddlewareStack();
        $this->assertSame($stack, $stack->prepend($cb));
    }

    public function testPrependOrdering()
    {
        $one = function () {
        };
        $two = function () {
        };

        $stack = new MiddlewareStack();
        $this->assertCount(0, $stack);

        $stack->push($one);
        $this->assertCount(1, $stack);

        $stack->prepend($two);
        $this->assertCount(2, $stack);

        $this->assertSame($two, $stack->get(0));
        $this->assertSame($one, $stack->get(1));
    }

    public function testInsertAt()
    {
        $one = function () {
        };
        $two = function () {
        };
        $three = function() {
        };

        $stack = new MiddlewareStack();
        $stack->push($one)->push($two)->insertAt(0, $three);
        $this->assertSame($three, $stack->get(0));
        $this->assertSame($one, $stack->get(1));
        $this->assertSame($two, $stack->get(2));

        $stack = new MiddlewareStack();
        $stack->push($one)->push($two)->insertAt(1, $three);
        $this->assertSame($one, $stack->get(0));
        $this->assertSame($three, $stack->get(1));
        $this->assertSame($two, $stack->get(2));
    }

    public function testInsertAtOutOfBounds()
    {
        $this->markTestIncomplete('not done');
    }

    public function testInsertAtNegative()
    {
        $this->markTestIncomplete('not done');
    }

    public function testInsertBefore()
    {
        $this->markTestIncomplete('not done');
    }

    public function testInsertBeforeInvalid()
    {
        $this->markTestIncomplete('not done');
    }

    public function testInsertAfter()
    {
        $this->markTestIncomplete('not done');
    }

    public function testInsertAfterInvalid()
    {
        $this->markTestIncomplete('not done');
    }
}
