<?php
namespace Spekkoek;

use Spekkoek\ActionDispatcher;
use Spekkoek\RequestTransformer;
use Spekkoek\ResponseTransformer;

abstract class BaseApplication
{
    protected $configDir;

    public function __construct($configDir)
    {
        $this->configDir = $configDir;
    }

    /**
     * Load all the application configuration and bootstrap logic.
     */
    public function bootstrap()
    {
        // Load traditional bootstrap file..
        require_once $this->configDir . '/bootstrap.php';

        // Load other config files your application needs.
    }

    abstract public function middleware($middleware);

    public function __invoke($request, $response, $next)
    {
        // Convert the request/response to CakePHP equivalents.
        $cakeRequest = RequestTransformer::toCake($request);
        $cakeResponse = ResponseTransformer::toCake($response);

        // - Create the application Dispatcher.
        // - Dispatch the request/response
        $cakeResponse = $this->getDispatcher()->dispatch($cakeRequest, $cakeResponse);

        // - Convert the response back into a PSR7 object.
        return ResponseTransformer::toPsr($cakeResponse);
    }

    protected function getDispatcher()
    {
        return new ActionDispatcher();
    }
}
