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
    /**
     * The current index in the middleware stack.
     *
     * @var int
     */
    protected $index;

    /**
     * The middleware stack being run.
     *
     * @var MiddlewareStack
     */
    protected $middleware;

    /**
     * @param \Spekkoek\MiddlewareStack $middleware The middleware stack
     * @param \Psr\Http\Message\ServerRequestInterface $request The Server Request
     * @param \Psr\Http\Message\ResponseInterface $response The response
     * @return \Psr\Http\Message\ResponseInterface A response object
     */
    public function run($middleware, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->middleware = $middleware;
        $this->index = 0;
        return $this->__invoke($request, $response);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request  The server request
     * @param \Psr\Http\Message\ResponseInterface $response The response object
     * @return \Psr\Http\Message\ResponseInterface An updated response
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
