<?php
namespace Spekkoek;

use Cake\Controller\Controller;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\DispatcherFactory;
use Cake\Routing\Exception\MissingControllerException;
use Cake\Routing\Router;
use LogicException;
use Spekkoek\ControllerFactory;

/**
 * This class provides compatibility with dispatcher filters
 * and interacting with the related CakePHP layers.
 *
 * Long term this should just be the controller dispatcher, but
 * for now it will do a bit more than that.
 */
class ActionDispatcher
{
    use EventDispatcherTrait;

    protected $filters = [];

    protected $factory;

    /**
     * @param \Spekkoek\ControllerFactory $factory A controller factory instance.
     */
    public function __construct($factory = null)
    {
        // Compatibility with DispatcherFilters.
        foreach (DispatcherFactory::filters() as $filter) {
            $this->addFilter($filter);
        }
        $this->factory = $factory ?: new ControllerFactory();
    }

    public function dispatch(Request $request, Response $response)
    {
        Router::pushRequest($request);
        $beforeEvent = $this->dispatchEvent('Dispatcher.beforeDispatch', compact('request', 'response'));

        $request = $beforeEvent->data['request'];
        if ($beforeEvent->result instanceof Response) {
            return $beforeEvent->result;
        }
        $controller = $this->factory->create($request, $response);
        $response = $this->_invoke($controller);
        if (isset($request->params['return'])) {
            return $response;
        }

        $afterEvent = $this->dispatchEvent('Dispatcher.afterDispatch', compact('request', 'response'));
        return $afterEvent->data['response'];
    }

    protected function _invoke(Controller $controller)
    {
        $result = $controller->startupProcess();
        if ($result instanceof Response) {
            return $result;
        }

        $response = $controller->invokeAction();
        if ($response !== null && !($response instanceof Response)) {
            throw new LogicException('Controller actions can only Cake\Network\Response instances');
        }

        if (!$response && $controller->autoRender) {
            $response = $controller->render();
        } elseif (!$response) {
            $response = $controller->response;
        }

        $result = $controller->shutdownProcess();
        if ($result instanceof Response) {
            return $result;
        }

        return $response;
    }

    /**
     * Add a filter to this dispatcher.
     *
     * The added filter will be attached to the event manager used
     * by this dispatcher.
     *
     * @param \Cake\Event\EventListenerInterface $filter The filter to connect. Can be
     *   any EventListenerInterface. Typically an instance of \Cake\Routing\DispatcherFilter.
     * @return void
     * @deprecated This is only available for backwards compatibility with DispatchFilters
     */
    public function addFilter(EventListenerInterface $filter)
    {
        $this->filters[] = $filter;
        $this->eventManager()->on($filter);
    }

    public function getFilters()
    {
        return $this->filters;
    }
}
