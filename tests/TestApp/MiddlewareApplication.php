<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;

class MiddlewareApplication extends BaseApplication
{
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
}
