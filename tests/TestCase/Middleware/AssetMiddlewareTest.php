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
        $modified = filemtime(APP . 'Plugin/TestPlugin/webroot/root.js');
        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/test_plugin/root.js',
            'HTTP_IF_MODIFIED_SINCE' => date('D, j M Y G:i:s \G\M\T', $modified)
        ]);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $middleware = new AssetMiddleware();
        $res = $middleware($request, $response, $next);

        $body = $res->getBody()->getContents();
        $this->assertEquals('', $body);
        $this->assertEquals(304, $res->getStatusCode());
        $this->assertNotEmpty($res->getHeaderLine('Last-Modified'));
    }

    public function testMissingPluginAsset()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/test_plugin/not_found.js']);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };

        $middleware = new AssetMiddleware();
        $res = $middleware($request, $response, $next);

        $body = $res->getBody()->getContents();
        $this->assertEquals('', $body);
    }

    /**
     * Data provider for assets.
     *
     * @return array
     */
    public function assetProvider()
    {
        return [
            // In plugin root.
            [
                '/test_plugin/root.js',
                APP . 'Plugin/TestPlugin/webroot/root.js'
            ],
            // Subdirectory
            [
                '/test_plugin/js/alert.js',
                APP . 'Plugin/TestPlugin/webroot/js/alert.js'
            ],
            // In path that matches the plugin name
            [
                '/test_plugin/js/test_plugin/alert.js',
                APP . 'Plugin/TestPlugin/webroot/js/test_plugin/alert.js'
            ],
            // In vendored plugin
            [
                '/company/plugin_two/root.js',
                APP . 'Plugin/Company/PluginTwo/webroot/root.js'
            ],
        ];
    }

    /**
     * @dataProvider assetProvider
     */
    public function testPluginAsset($url, $expectedFile)
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => $url]);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };

        $middleware = new AssetMiddleware();
        $res = $middleware($request, $response, $next);

        $body = $res->getBody()->getContents();
        $this->assertEquals(file_get_contents($expectedFile), $body);
    }

    public function testPluginAssetHeaders()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/test_plugin/root.js']);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };

        $modified = filemtime(APP . 'Plugin/TestPlugin/webroot/root.js');
        $expires = strtotime('+4 hours');
        $time = time();

        $middleware = new AssetMiddleware(['cacheTime' => '+4 hours']);
        $res = $middleware($request, $response, $next);

        $this->assertEquals(
            'text/plain',
            $res->getHeaderLine('Content-Type')
        );
        $this->assertEquals(
            gmdate('D, j M Y G:i:s ', $time) . 'GMT',
            $res->getHeaderLine('Date')
        );
        $this->assertEquals(
            'public,max-age=' . ($expires - $time),
            $res->getHeaderLine('Cache-Control')
        );
        $this->assertEquals(
            gmdate('D, j M Y G:i:s ', $modified) . 'GMT',
            $res->getHeaderLine('Last-Modified')
        );
        $this->assertEquals(
            gmdate('D, j M Y G:i:s ', $expires) . 'GMT',
            $res->getHeaderLine('Expires')
        );
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
