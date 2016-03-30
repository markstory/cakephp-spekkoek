<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;
use Spekkoek\MiddlewareStack;

class BadResponseApplication extends BaseApplication
{

    /**
     * @param MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return MiddlewareStack
     */
    public function middleware(MiddlewareStack $middleware)
    {
        $middleware->push(function ($req, $res, $next) {
            return 'Not a response';
        });
        return $middleware;
    }

    public function __invoke($req, $res, $next)
    {
        return $res;
    }
}
