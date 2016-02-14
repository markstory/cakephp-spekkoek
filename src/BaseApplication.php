<?php
namespace Spekkoek;

abstract class BaseApplication
{
    /**
     * Load all the application configuration and bootstrap logic.
     */
    public function bootstrap()
    {
        // Load paths.
        // Load config files via bootstrap.php.
    }

    abstract public function middleware($middleware);

    public function __invoke($request, $response, $next)
    {
        // Convert the request/response to CakePHP equivalents.
        // Create the application Dispatcher.
        // Dispatch the request/response
        // Convert the response back into a PSR7 object.
        return $response;
    }
}