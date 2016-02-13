<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;

class BadResponseApplication extends BaseApplication
{
    public function middleware($middleware)
    {
        $middleware->push(function ($req, $res, $next) {
            return 'Not a response';
        });
        return $middleware;
    }
}
