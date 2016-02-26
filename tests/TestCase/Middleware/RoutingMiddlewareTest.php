<?php
namespace Spekkoek\Test\TestCase;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Spekkoek\Middleware\RoutingMiddleware;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * Test for RoutingMiddleware
 */
class RoutingMiddlewareTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Router::reload();
        Router::connect('/articles', ['controller' => 'Articles', 'action' => 'index']);
    }

    public function testRedirectResponse()
    {
        Router::redirect('/testpath', '/pages');
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/testpath']);
        $response = new Response();
        $next = function ($req, $res) {
        };
        $middleware = new RoutingMiddleware();
        $response = $middleware($request, $response, $next);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/pages', $response->getHeaderLine('Location'));
    }

    public function testRedirectResponseWithHeaders()
    {
        Router::redirect('/testpath', '/pages');
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/testpath']);
        $response = new Response('php://memory', 200, ['X-testing' => 'Yes']);
        $next = function ($req, $res) {
        };
        $middleware = new RoutingMiddleware();
        $response = $middleware($request, $response, $next);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/pages', $response->getHeaderLine('Location'));
        $this->assertEquals('Yes', $response->getHeaderLine('X-testing'));
    }

    public function testRouterSetParams()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/articles']);
        $response = new Response();
        $next = function ($req, $res) {
            $expected = [
                'controller' => 'Articles',
                'action' => 'index',
                'plugin' => null,
                'pass' => []
            ];
            $this->assertEquals($expected, $req->getAttribute('params'));
        };
        $middleware = new RoutingMiddleware();
        $middleware($request, $response, $next);
    }

    public function testRouterNoopOnController()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/articles']);
        $request = $request->withAttribute('params', ['controller' => 'Articles']);
        $response = new Response();
        $next = function ($req, $res) {
            $this->assertEquals(['controller' => 'Articles'], $req->getAttribute('params'));
        };
        $middleware = new RoutingMiddleware();
        $middleware($request, $response, $next);
    }
}
