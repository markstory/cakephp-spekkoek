<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;

class InvalidMiddlewareApplication extends BaseApplication
{

    /**
     * @param \Spekkoek\MiddlewareStack $middleware The middleware stack to set in your App Class
     * @return null
     */
    public function middleware($middleware)
    {
        return null;
    }
}
