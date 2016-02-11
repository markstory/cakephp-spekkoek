<?php
namespace Spekkoek;

/**
 * Executes the middleware stack and provides the `next` callable
 * that allows the stack to be iterated.
 */
class Runner
{
    protected $index;
    protected $middleware;

    public function run($middleware, $request, $response)
    {
        $this->middleware = $middleware;
        $this->index = 0;
        return $this->__invoke($request, $response);
    }

    public function __invoke($request, $response)
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
