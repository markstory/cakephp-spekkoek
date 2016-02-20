<?php
namespace Spekkoek;

use Spekkoek\BaseApplication;
use Spekkoek\MiddlewareStack;
use Spekkoek\Runner;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\EmitterInterface;
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

    public function run(ServerRequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->app->bootstrap();
        $request = $request ?: ServerRequestFactory::fromGlobals();
        $response = $response ?: new Response();

        $middleware = $this->app->middleware(new MiddlewareStack());
        if (!($middleware instanceof MiddlewareStack)) {
            throw new RuntimeException('The application `middleware` method did not return a middleware stack.');
        }
        $middleware->push($this->app);
        $response = $this->runner->run($middleware, $request, $response);

        if (!($response instanceof ResponseInterface)) {
            throw new RuntimeException(sprintf(
                'Application did not create a response. Got "%s" instead.',
                is_object($response) ? get_class($response) : $response
            ));
        }
        return $response;
    }

    public function emit(ResponseInterface $response, EmitterInterface $emitter = null)
    {
        if (!$emitter) {
            $emitter = new SapiStreamEmitter();
        }
        $emitter->emit($response);
    }

    /**
     * Set the application.
     *
     * @param Spekkoek\BaseApplication $app The application to set.
     */
    public function setApp(BaseApplication $app)
    {
        $this->app = $app;
    }

    /**
     * Get the current application.
     *
     * @return Spekkoek\BaseApplication The application that will be run.
     */
    public function getApp(BaseApplication $app)
    {
        return $this->app;
    }

    public function setRunner(Runner $runner)
    {
        $this->runner = $runner;
    }
}
