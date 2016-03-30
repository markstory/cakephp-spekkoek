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

    /**
     * @param MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return mixed
     */
    abstract public function middleware(MiddlewareStack $middleware);

    /**
     * Load all the application configuration and bootstrap logic.
     */
    public function bootstrap()
    {
        // Load traditional bootstrap file..
        require_once $this->configDir . '/bootstrap.php';

        // Load other config files your application needs.
    }

    /**
     *
     * @param ServerRequestInterface $request  A server request object
     * @param ResponseInterface      $response A response object
     * @param callable               $next     The next middleware to be executed
     * @return mixed
     */
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
