<?php
namespace Spekkoek;

use Spekkoek\BaseApplication;
use Spekkoek\MiddlewareStack;
use Spekkoek\Runner;
use RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

/**
 * Runs an application invoking all the PSR7 middleware and the registered application.
 */
class Server
{
    protected $app;

    // TODO perhaps an interface for the application?
    public function __construct(BaseApplication $app)
    {
        $this->setApp($app);
        $this->setRunner(new Runner());
    }

    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->app->bootstrap();
        if (!$request) {
            $request = ServerRequestFactory::fromGlobals();
        }
        if (!$response) {
            $response = new Response();
        }
        $middleware = $this->app->middleware(new MiddlewareStack());
        if (!($middleware instanceof MiddlewareStack)) {
            throw new RuntimeException('The application `middleware` method did not return a middleware stack.');
        }
        $middleware->push($this->app);
        return $this->runner->run($middleware, $request, $response);
    }

    public function setApp(BaseApplication $app)
    {
        $this->app = $app;
    }

    public function getApp(BaseApplication $app)
    {
        return $this->app;
    }

    public function setRunner(Runner $runner)
    {
        $this->runner = $runner;
    }
}
