<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;
use Spekkoek\MiddlewareStack;

class MiddlewareApplication extends BaseApplication
{

    /**
     * @param \Spekkoek\MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return \Spekkoek\MiddlewareStack
     */
    public function middleware(MiddlewareStack $middleware)
    {
        $middleware
            ->push(function ($req, $res, $next) {
                $res = $res->withHeader('X-First', 'first');
                return $next($req, $res);
            })
            ->push(function ($req, $res, $next) {
                $res = $res->withHeader('X-Second', 'second');
                return $next($req, $res);
            })
            ->push(function ($req, $res, $next) {
                if ($req->hasHeader('X-pass')) {
                    $res = $res->withHeader('X-pass', $req->getHeaderLine('X-pass'));
                }
                $res = $res->withHeader('X-Second', 'second');
                return $next($req, $res);
            });
        return $middleware;
    }

    public function __invoke($req, $res, $next)
    {
        return $res;
    }
}
