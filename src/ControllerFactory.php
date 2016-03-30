<?php
namespace Spekkoek;

use Cake\Core\App;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\Exception\MissingControllerException;
use Cake\Utility\Inflector;
use ReflectionClass;

/**
 * Factory method for building controllers from request/response pairs.
 */
class ControllerFactory
{
    /**
     * Create a controller for a given request/response
     *
     * @param \Cake\Network\Request $request The request to build a controller for.
     * @param \Cake\Network\Response $response The response to use.
     * @return \Cake\Controller\Controller
     */
    public function create(Request $request, Response $response)
    {
        $pluginPath = $controller = null;
        $namespace = 'Controller';
        if (isset($request->params['plugin'])) {
            $pluginPath = $request->params['plugin'] . '.';
        }
        if (isset($request->params['controller'])) {
            $controller = $request->params['controller'];
        }
        if (isset($request->params['prefix'])) {
            if (strpos($request->params['prefix'], '/') === false) {
                $namespace .= '/' . Inflector::camelize($request->params['prefix']);
            } else {
                $prefixes = array_map(
                    'Cake\Utility\Inflector::camelize',
                    explode('/', $request->params['prefix'])
                );
                $namespace .= '/' . implode('/', $prefixes);
            }
        }
        $firstChar = substr($controller, 0, 1);
        if (strpos($controller, '\\') !== false ||
            strpos($controller, '.') !== false ||
            $firstChar === strtolower($firstChar)
        ) {
            return $this->missingController($request);
        }
        $className = false;
        if ($pluginPath . $controller) {
            $className = App::classname($pluginPath . $controller, $namespace, 'Controller');
        }
        if (!$className) {
            return $this->missingController($request);
        }
        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            return $this->missingController($request);
        }
        return $reflection->newInstance($request, $response, $controller);
    }

    /**
     * Throws an exception when a controller is missing.
     *
     * @param \Cake\Network\Request $request The request.
     * @throws \Cake\Routing\Exception\MissingControllerException
     * @return void
     */
    protected function missingController($request)
    {
        throw new MissingControllerException([
            'class' => $request->param('controller'),
            'plugin' => $request->param('plugin'),
            'prefix' => $request->param('prefix'),
            '_ext' => $request->param('_ext')
        ]);
    }
}
