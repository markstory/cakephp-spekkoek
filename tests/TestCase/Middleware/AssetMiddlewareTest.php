<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use Spekkoek\Middleware\AssetMiddleware;
use Spekkoek\ServerRequestFactory;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * Test for AssetMiddleware
 */
class AssetMiddlewareTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Plugin::load('TestPlugin');
        Plugin::load('Company/PluginTwo');
    }

    public function testCheckIfModifiedHeader()
    {
        $this->markTestIncomplete();
    }

    public function testMissingPluginAsset()
    {
        $this->markTestIncomplete();
    }

    public function testPluginAsset()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/test_plugin/root.js']);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };

        $middleware = new AssetMiddleware();
        $res = $middleware($request, $response, $next);

        $body = $res->getBody()->getContents();
        $this->assertEquals(
            file_get_contents(APP . 'Plugin/TestPlugin/webroot/root.js'),
            $body
        );
    }

    public function testPluginAssetSubdirectory()
    {
        $this->markTestIncomplete();
    }

    public function testVendorPluginAsset()
    {
        $this->markTestIncomplete();
    }

    public function test404OnDoubleSlash()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '//index.php']);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };

        $middleware = new AssetMiddleware();
        $res = $middleware($request, $response, $next);
        $this->assertEmpty($res->getBody()->getContents());
    }

    public function test404OnDoubleDot()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/test_plugin/../webroot/root.js']);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };

        $middleware = new AssetMiddleware();
        $res = $middleware($request, $response, $next);
        $this->assertEmpty($res->getBody()->getContents());
    }
}
