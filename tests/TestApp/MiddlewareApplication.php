<?php
namespace Spekkoek\Test\TestApp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spekkoek\BaseApplication;

class MiddlewareApplication extends BaseApplication
{

    /**
     * @param \Spekkoek\MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return \Spekkoek\MiddlewareStack
     */
    public function middleware($middleware)
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

    public function __invoke(ServerRequestInterface $req, ResponseInterface $res, $next)
    {
        return $res;
    }
}
