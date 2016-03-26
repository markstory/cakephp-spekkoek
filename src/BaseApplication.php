<?php
namespace Spekkoek;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class BaseApplication
{

    /**
     * @var string Contains the path of the config directory
     */
    protected $configDir;

    public function __construct($configDir)
    {
        $this->configDir = $configDir;
    }

    abstract public function middleware($middleware);

    /**
     * Load all the application configuration and bootstrap logic.
     */
    public function bootstrap()
    {
        // Load traditional bootstrap file..
        require_once $this->configDir . '/bootstrap.php';

        // Load other config files your application needs.
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        // Convert the request/response to CakePHP equivalents.
        $cakeRequest = RequestTransformer::toCake($request);
        $cakeResponse = ResponseTransformer::toCake($response);

        // Dispatch the request/response to CakePHP
        $cakeResponse = $this->getDispatcher()->dispatch($cakeRequest, $cakeResponse);

        // Convert the response back into a PSR7 object.
        return $next($request, ResponseTransformer::toPsr($cakeResponse));
    }

    /**
     * Get the ActionDispatcher.
     *
     * @return \Spekkoek\ActionDispatcher
     */
    protected function getDispatcher()
    {
        return new ActionDispatcher();
    }
}
