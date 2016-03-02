<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Spekkoek\Middleware\ErrorHandlerMiddleware;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * Test for ErrorHandlerMiddleware
 */
class ErrorHandlerMiddlewareTest extends TestCase
{
    public function testNoErrorResponse()
    {
        $this->markTestIncomplete();
    }

    public function testInvalidRenderer()
    {
        $this->markTestIncomplete();
    }

    public function testRendererFactory()
    {
        $this->markTestIncomplete();
    }

    public function testHandleException()
    {
        $this->markTestIncomplete();
    }

    public function testHandleExceptionRenderingFails()
    {
        $this->markTestIncomplete();
    }
}
