<?php
namespace Spekkoek;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Executes the middleware stack and provides the `next` callable
 * that allows the stack to be iterated.
 */
class Runner
{

    protected $index;

    /**
     * @var MiddlewareStack
     */
    protected $middleware;

    public function run(MiddlewareStack $middleware, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->middleware = $middleware;
        $this->index = 0;
        return $this->__invoke($request, $response);
    }

    /**
     * @param ServerRequestInterface $request The server request
     * @param ResponseInterface      $response
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $next = $this->middleware->get($this->index);
        if ($next) {
            $this->index++;
            return $next($request, $response, $this);
        }

        // End of the stack
        return $response;
    }
}
