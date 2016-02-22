<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Cake\Network\Request;
use Cake\Network\Response;
use Spekkoek\ActionDispatcher;

class ActionDispatcherTest extends TestCase
{
    public function testDispatcherFilterCompat()
    {
        $this->markTestIncomplete();
    }

    public function testBeforeDispatchEventAbort()
    {
        $this->markTestIncomplete();
    }

    public function testAfterDispatchEventModifyResponse()
    {
        $this->markTestIncomplete();
    }

    public function testDispatchInvalidResponse()
    {
        // Add mocks in beforeDispatch event
        $this->markTestIncomplete();
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
        $dispatcher = new ActionDispatcher();
        $dispatcher->dispatch($request, $response);
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
        $dispatcher = new ActionDispatcher();
        $dispatcher->dispatch($request, $response);
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
        $dispatcher = new ActionDispatcher();
        $dispatcher->dispatch($request, $response);
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
                'controller' => 'somepages',
                'action' => 'display',
                'pass' => ['home'],
            ]
        ]);
        $response = $this->getMock('Cake\Network\Response');
        $dispatcher = new ActionDispatcher();
        $dispatcher->dispatch($request, $response);
    }

    public function testStartupProcessAbort()
    {
        // Add mocks in beforeDispatch event
        $this->markTestIncomplete();
    }

    public function testShutdownProcessResponse()
    {
        // Add mocks in beforeDispatch event
        $this->markTestIncomplete();
    }
}
