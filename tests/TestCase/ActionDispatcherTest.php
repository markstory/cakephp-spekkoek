<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Session;
use Cake\Network\Response;
use Cake\Routing\Filter\ControllerFactoryFilter;
use Cake\TestSuite\TestCase;
use Spekkoek\ActionDispatcher;

class ActionDispatcherTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Configure::write('App.namespace', 'Spekkoek\Test\TestApp');
        $this->dispatcher = new ActionDispatcher();
        $this->dispatcher->addFilter(new ControllerFactoryFilter());
    }

    public function testAddFilter()
    {
        $this->assertCount(1, $this->dispatcher->getFilters());
        $events = $this->dispatcher->eventManager();
        $this->assertCount(1, $events->listeners('Dispatcher.beforeDispatch'));
        $this->assertCount(1, $events->listeners('Dispatcher.afterDispatch'));

        $filter = $this->getMock(
            'Cake\Routing\DispatcherFilter',
            ['beforeDispatch', 'afterDispatch']
        );
        $this->dispatcher->addFilter($filter);

        $this->assertCount(2, $this->dispatcher->getFilters());
        $this->assertCount(2, $events->listeners('Dispatcher.beforeDispatch'));
        $this->assertCount(2, $events->listeners('Dispatcher.afterDispatch'));
    }

    public function testBeforeDispatchEventAbort()
    {
        $response = new Response();
        $dispatcher = new ActionDispatcher();
        $filter = $this->getMock(
            'Cake\Routing\DispatcherFilter',
            ['beforeDispatch', 'afterDispatch']
        );
        $filter->expects($this->once())
            ->method('beforeDispatch')
            ->will($this->returnValue($response));

        $req = new Request();
        $res = new Response();
        $dispatcher->addFilter($filter);
        $result = $dispatcher->dispatch($req, $res);
        $this->assertSame($response, $result, 'Should be response from filter.');
    }

    public function testAfterDispatchEventModifyResponse()
    {
        $filter = $this->getMock(
            'Cake\Routing\DispatcherFilter',
            ['beforeDispatch', 'afterDispatch']
        );
        $filter->expects($this->once())
            ->method('afterDispatch')
            ->will($this->returnCallback(function ($event) {
                $event->data['response']->body('Filter body');
            }));

        $req = new Request([
            'url' => '/cakes',
            'params' => [
                'plugin' => null,
                'controller' => 'Cakes',
                'action' => 'index',
                'pass' => [],
            ],
            'session' => new Session
        ]);
        $res = new Response();
        $this->dispatcher->addFilter($filter);
        $result = $this->dispatcher->dispatch($req, $res);
        $this->assertSame('Filter body', $result->body(), 'Should be response from filter.');
    }

    public function testActionReturnResponseNoAfterDispatch()
    {
        $filter = $this->getMock(
            'Cake\Routing\DispatcherFilter',
            ['beforeDispatch', 'afterDispatch']
        );
        $filter->expects($this->never())
            ->method('afterDispatch');

        $req = new Request([
            'url' => '/cakes',
            'params' => [
                'plugin' => null,
                'controller' => 'Cakes',
                'action' => 'index',
                'pass' => [],
                'return' => true,
            ],
        ]);
        $res = new Response();
        $this->dispatcher->addFilter($filter);
        $result = $this->dispatcher->dispatch($req, $res);
        $this->assertSame('Hello Jane', $result->body(), 'Response from controller.');
    }

    /**
     * test invalid response from dispatch process.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Controller actions can only Cake\Network\Response instances
     * @return void
     */
    public function testDispatchInvalidResponse()
    {
        $req = new Request([
            'url' => '/cakes',
            'params' => [
                'plugin' => null,
                'controller' => 'Cakes',
                'action' => 'invalid',
                'pass' => [],
            ],
        ]);
        $res = new Response();
        $result = $this->dispatcher->dispatch($req, $res);
    }

    /**
     * testMissingController method
     *
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class SomeController could not be found.
     * @return void
     */
    public function testMissingController()
    {
        $request = new Request([
            'url' => 'some_controller/home',
            'params' => [
                'controller' => 'SomeController',
                'action' => 'home',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->dispatcher->dispatch($request, $response);
    }

    /**
     * testMissingControllerInterface method
     *
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class DispatcherTestInterface could not be found.
     * @return void
     */
    public function testMissingControllerInterface()
    {
        $request = new Request([
            'url' => 'dispatcher_test_interface/index',
            'params' => [
                'controller' => 'DispatcherTestInterface',
                'action' => 'index',
            ]
        ]);
        $url = new Request('dispatcher_test_interface/index');
        $response = $this->getMock('Cake\Network\Response');
        $this->dispatcher->dispatch($request, $response);
    }

    /**
     * testMissingControllerInterface method
     *
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class Abstract could not be found.
     * @return void
     */
    public function testMissingControllerAbstract()
    {
        $request = new Request([
            'url' => 'abstract/index',
            'params' => [
                'controller' => 'Abstract',
                'action' => 'index',
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->dispatcher->dispatch($request, $response);
    }

    /**
     * Test that lowercase controller names result in missing controller errors.
     *
     * In case-insensitive file systems, lowercase controller names will kind of work.
     * This causes annoying deployment issues for lots of folks.
     *
     * @expectedException \Cake\Routing\Exception\MissingControllerException
     * @expectedExceptionMessage Controller class somepages could not be found.
     * @return void
     */
    public function testMissingControllerLowercase()
    {
        $request = new Request([
            'url' => 'pages/home',
            'params' => [
                'plugin' => null,
                'controller' => 'somepages',
                'action' => 'display',
                'pass' => ['home'],
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $this->dispatcher->dispatch($request, $response);
    }

    public function testStartupProcessAbort()
    {
        $request = new Request([
            'url' => 'brakes/index',
            'params' => [
                'plugin' => null,
                'controller' => 'Brakes',
                'action' => 'index',
                'stop' => 'startup',
                'pass' => [],
            ]
        ]);
        $response = new Response();
        $result = $this->dispatcher->dispatch($request, $response);
        $this->assertSame('startup stop', $result->body());
    }

    public function testShutdownProcessResponse()
    {
        $request = new Request([
            'url' => 'brakes/index',
            'params' => [
                'plugin' => null,
                'controller' => 'Brakes',
                'action' => 'index',
                'stop' => 'shutdown',
                'pass' => [],
            ]
        ]);
        $response = new Response();
        $result = $this->dispatcher->dispatch($request, $response);
        $this->assertSame('shutdown stop', $result->body());
    }
}
