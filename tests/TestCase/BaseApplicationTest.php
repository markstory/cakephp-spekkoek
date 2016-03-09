<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Spekkoek\BaseApplication;
use Spekkoek\ServerRequestFactory;
use Zend\Diactoros\Response;

class BaseApplicationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Configure::write('App.namespace', 'Spekkoek\Test\TestApp');
    }

    /**
     * Integration test for a simple controller.
     *
     * @return void
     */
    public function testInvoke()
    {
        $next = function ($req, $res) {
            return $res;
        };
        $response = new Response();
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/cakes']);
        $request = $request->withAttribute('params', [
            'controller' => 'Cakes',
            'action' => 'index',
            'plugin' => null,
            'pass' => []
        ]);

        $app = $this->getMockForAbstractClass('Spekkoek\BaseApplication', [TESTS]);
        $result = $app($request, $response, $next);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $result);
        $this->assertEquals('Hello Jane', '' . $result->getBody());
    }
}
