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
        $this->response = $this->getMock('Cake\Network\Response');
    }

    public function testApplicationController()
    {
        $request = new Request([
            'url' => 'cakes/index',
            'params' => [
                'controller' => 'Cakes',
                'action' => 'index',
            ]
        ]);
        $result = $this->factory->create($request, $this->response);
        $this->assertInstanceOf('Spekkoek\Test\TestApp\Controller\CakesController', $result);
        $this->assertSame($request, $result->request);
        $this->assertSame($this->response, $result->response);
    }

    public function testPrefixedAppController()
    {
        $request = new Request([
            'url' => 'admin/comments/index',
            'params' => [
                'prefix' => 'admin',
                'controller' => 'Comments',
                'action' => 'index',
            ]
        ]);
        $result = $this->factory->create($request, $this->response);
        $this->assertInstanceOf(
            'Spekkoek\Test\TestApp\Controller\Admin\CommentsController',
            $result
        );
        $this->assertSame($request, $result->request);
        $this->assertSame($this->response, $result->response);
    }

    public function testNestedPrefixedAppController()
    {
        $request = new Request([
            'url' => 'admin/internal/comments/index',
            'params' => [
                'prefix' => 'admin/internal',
                'controller' => 'Comments',
                'action' => 'index',
            ]
        ]);
        $result = $this->factory->create($request, $this->response);
        $this->assertInstanceOf(
            'Spekkoek\Test\TestApp\Controller\Admin\Internal\CommentsController',
            $result
        );
        $this->assertSame($request, $result->request);
        $this->assertSame($this->response, $result->response);
    }

    public function testPluginController()
    {
        $request = new Request([
            'url' => 'test_plugin/test_plugin/index',
            'params' => [
                'plugin' => 'TestPlugin',
                'controller' => 'TestPlugin',
                'action' => 'index',
            ]
        ]);
        $result = $this->factory->create($request, $this->response);
        $this->assertInstanceOf(
            'TestPlugin\Controller\TestPluginController',
            $result
        );
        $this->assertSame($request, $result->request);
        $this->assertSame($this->response, $result->response);
    }

    public function testVendorPluginController()
    {
        $request = new Request([
            'url' => 'plugin_two/ovens/index',
            'params' => [
                'plugin' => 'Company/PluginTwo',
                'controller' => 'Ovens',
                'action' => 'index',
            ]
        ]);
        $result = $this->factory->create($request, $this->response);
        $this->assertInstanceOf(
            'Company\PluginTwo\Controller\OvensController',
            $result
        );
        $this->assertSame($request, $result->request);
        $this->assertSame($this->response, $result->response);
    }

    public function testPrefixedPluginController()
    {
        $request = new Request([
            'url' => 'test_plugin/dashboards/index',
            'params' => [
                'prefix' => 'admin',
                'plugin' => 'TestPlugin',
                'controller' => 'Dashboards',
                'action' => 'index',
            ]
        ]);
        $result = $this->factory->create($request, $this->response);
        $this->assertInstanceOf(
            'TestPlugin\Controller\Admin\DashboardsController',
            $result
        );
        $this->assertSame($request, $result->request);
        $this->assertSame($this->response, $result->response);
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
        $this->factory->create($request, $this->response);
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
        $this->factory->create($request, $this->response);
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
        $this->factory->create($request, $this->response);
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
        $this->factory->create($request, $this->response);
    }
}
