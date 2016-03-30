<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;
use Spekkoek\MiddlewareStack;

class InvalidMiddlewareApplication extends BaseApplication
{

    /**
     * @param MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return null
     */
    public function middleware(MiddlewareStack $middleware)
    {
        return null;
    }
}
