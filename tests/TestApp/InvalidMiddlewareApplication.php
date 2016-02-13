<?php
namespace Spekkoek\Test\TestApp;

use Spekkoek\BaseApplication;

class InvalidMiddlewareApplication extends BaseApplication
{
    public function middleware($middleware)
    {
        return null;
    }
}
