<?php
namespace Spekkoek\Middleware;

use Cake\Routing\Exception\RedirectException;
use Cake\Routing\Router;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Applies routing rules to the request and creates the controller
 * instance if possible.
 */
class RoutingMiddleware
{
    public function __invoke($request, $response, $next)
    {
        try {
            $params = (array)$request->getAttribute('params', []);
            if (empty($params['controller'])) {
                $path = $request->getUri()->getPath();
                $request = $request->withAttribute('params', Router::parse($path, $request->getMethod()));
            }
        } catch (RedirectException $e) {
            return new RedirectResponse(
                $e->getMessage(),
                $e->getCode(),
                $response->getHeaders()
            );
        }
        return $next($request, $response);
    }
}
