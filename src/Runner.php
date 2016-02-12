<?php
namespace Spekkoek;

use Psr\Http\RequestInterface;
use Psr\Http\ResponseInterface;

/**
 * Executes the middleware stack and provides the `next` callable
 * that allows the stack to be iterated.
 */
class Runner
{
    protected $index;
    protected $middleware;

    public function run($middleware, RequestInterface $request, ResponseInterface $response)
    {
        $this->middleware = $middleware;
        $this->index = 0;
        return $this->__invoke($request, $response);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $next = $this->middleware->get($this->index);
        if ($next) {
            $this->index++;
            return $next($request, $response, $this);
        }

        // End of the stack
        if ($next === null) {
            return $response;
        }
    }
}
