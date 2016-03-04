<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Configure;
use Cake\Network\Response as CakeResponse;
use Cake\TestSuite\TestCase;
use Spekkoek\Middleware\ErrorHandlerMiddleware;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Test for ErrorHandlerMiddleware
 */
class ErrorHandlerMiddlewareTest extends TestCase
{
    public function testNoErrorResponse()
    {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $middleware = new ErrorHandlerMiddleware();
        $next = function ($req, $res) {
            return $res;
        };
        $result = $middleware($request, $response, $next);
        $this->assertSame($result, $response);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The 'TotallyInvalid' renderer class could not be found
     */
    public function testInvalidRenderer()
    {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $middleware = new ErrorHandlerMiddleware('TotallyInvalid');
        $next = function ($req, $res) {
            throw new \Exception('Something bad');
        };
        $middleware($request, $response, $next);
    }

    public function testRendererFactory()
    {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $factory = function ($exception) {
            $this->assertInstanceOf('LogicException', $exception);
            $cakeResponse = new CakeResponse;
            $mock = $this->getMock('StdClass', ['render']);
            $mock->expects($this->once())
                ->method('render')
                ->will($this->returnValue($cakeResponse));
            return $mock;
        };
        $middleware = new ErrorHandlerMiddleware($factory);
        $next = function ($req, $res) {
            throw new \LogicException('Something bad');
        };
        $middleware($request, $response, $next);
    }

    public function testHandleException()
    {
        Configure::write('App.namespace', 'TestApp');

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();
        $middleware = new ErrorHandlerMiddleware();
        $next = function ($req, $res) {
            throw new \Cake\Network\Exception\NotFoundException('whoops');
        };
        $result = $middleware($request, $response, $next);
        $this->assertNotSame($result, $response);
        $this->assertEquals(404, $result->getStatusCode());
        $this->assertEquals("A 400 series error!\n", '' . $result->getBody());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testHandleExceptionRenderingFails()
    {
        Configure::write('App.namespace', 'TestApp');

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();
        $middleware = new ErrorHandlerMiddleware();
        $next = function ($req, $res) {
            throw new \Cake\Network\Exception\ServiceUnavailableException('whoops');
        };
        $middleware($request, $response, $next);
    }
}
