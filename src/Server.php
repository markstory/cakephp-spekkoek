<?php
namespace Spekkoek;

use Cake\Event\EventDispatcherTrait;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\SapiStreamEmitter;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response;

/**
 * Runs an application invoking all the PSR7 middleware and the registered application.
 */
class Server
{

    use EventDispatcherTrait;

    /**
     * @var BaseApplication
     */
    protected $app;

    /**
     * @var Runner
     */
    protected $runner;

    public function __construct(BaseApplication $app)
    {
        $this->setApp($app);
        $this->setRunner(new Runner());
    }

    /**
     * Run the request/response through the Application and its middleware.
     *
     * This will invoke the following methods:
     *
     * - App->bootstrap() - Perform any bootstrapping logic for your application here.
     * - App->middleware() - Attach any application middleware here.
     * - Trigger the 'Server.buildMiddleware' event. You can use this to modify the
     *   from event listeners.
     * - Run the middleware stack including the application.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The request to use or null.
     * @param \Psr\Http\Message\ResponseInterface      $response The response to use or null.
     * @return \Psr\Http\Message\ResponseInterface
     */
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
        $this->dispatchEvent('Server.buildMiddleware', ['middleware' => $middleware]);
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
     * @param BaseApplication $app The application to set.
     */
    public function setApp(BaseApplication $app)
    {
        $this->app = $app;
    }

    /**
     * Get the current application.
     *
     * @return BaseApplication The application that will be run.
     */
    public function getApp()
    {
        return $this->app;
    }

    public function setRunner(Runner $runner)
    {
        $this->runner = $runner;
    }
}
