<?php
namespace Spekkoek\Middleware;

use Cake\Core\App;
use Exception;
use Spekkoek\ResponseTransformer;

/**
 * Error handling middleware
 */
class ErrorHandlerMiddleware
{
    /**
     * Constructor
     *
     * @param string|callable $renderer The renderer or class name
     *   to use or a callable factory.
     */
    public function __construct($renderer = null)
    {
        $this->renderer = $renderer ?: 'Cake\Error\ExceptionRenderer';
    }

    /**
     * Wrap the remaining middleware with error handling.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke($request, $response, $next)
    {
        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            return $this->handleException($e, $request, $response);
        }
    }

    /**
     * Handle an exception and generate an error response
     *
     * @param \Exception $exception The exception to handle.
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function handleException($exception, $request, $response)
    {
        $renderer = $this->getRenderer($exception);
        try {
            $response = $renderer->render();
            return ResponseTransformer::toPsr($response);
        } catch (Exception $e) {
            $message = sprintf(
                "[%s] %s\n%s", // Keeping same message format
                get_class($e),
                $e->getMessage(),
                $e->getTraceAsString()
            );
            trigger_error($message, E_USER_ERROR);
        }
        return $response;
    }

    /**
     * Get a renderer instance
     *
     * @param \Exception $exception The exception being rendered.
     * @return \Cake\Error\BaseErrorHandler The exception renderer.
     */
    protected function getRenderer($exception)
    {
        if (is_string($this->renderer)) {
            $class = App::className($this->renderer, 'Error');
            if (!$class) {
                throw new \Exception("The '{$this->renderer}' renderer class could not be found.");
            }
            return new $class($exception);
        }
        $factory = $this->renderer;
        return $factory($exception);
    }
}
