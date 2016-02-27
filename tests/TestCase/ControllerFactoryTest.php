<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use Spekkoek\ControllerFactory;

class ControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Configure::write('App.namespace', 'Spekkoek\Test\TestApp');
        $this->factory = new ControllerFactory();
    }

    public function testApplicationController()
    {
        $request = new Request([
            'url' => 'interface/index',
            'params' => [
                'controller' => 'Cakes',
                'action' => 'index',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $result = $this->factory->create($request, $response);
        $this->assertInstanceOf('Spekkoek\Test\TestApp\Controller\CakesController', $result);
        $this->assertSame($request, $result->request);
        $this->assertSame($response, $result->response);
    }

    public function testPluginController()
    {
        $this->markTestIncomplete();
    }

    public function testNestedPluginController()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class Abstract could not be found.
     * @return void
     */
    public function testAbstractClassFailure()
    {
        $request = new Request([
            'url' => 'abstract/index',
            'params' => [
                'controller' => 'Abstract',
                'action' => 'index',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->factory->create($request, $response);
    }

    /**
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class Interface could not be found.
     * @return void
     */
    public function testInterfaceFailure()
    {
        $request = new Request([
            'url' => 'interface/index',
            'params' => [
                'controller' => 'Interface',
                'action' => 'index',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->factory->create($request, $response);
    }

    /**
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class Invisible could not be found.
     * @return void
     */
    public function testMissingClassFailure()
    {
        $request = new Request([
            'url' => 'interface/index',
            'params' => [
                'controller' => 'Invisible',
                'action' => 'index',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->factory->create($request, $response);
    }

    /**
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class Spekkoek\Test\TestCase\Controller\CakesController could not be found.
     * @return void
     */
    public function testAbsoluteReferenceFailure()
    {
        $request = new Request([
            'url' => 'interface/index',
            'params' => [
                'controller' => 'Spekkoek\Test\TestCase\Controller\CakesController',
                'action' => 'index',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->factory->create($request, $response);
    }
}
