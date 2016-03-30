<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BadResponseApplication extends BaseApplication
{

    /**
     * @param \Spekkoek\MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return \Spekkoek\MiddlewareStack
     */
    public function middleware($middleware)
    {
        $middleware->push(function ($req, $res, $next) {
            return 'Not a response';
        });
        return $middleware;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        return $res;
    }
}
