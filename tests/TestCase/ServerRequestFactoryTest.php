<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Spekkoek\ServerRequestFactory;

class ServerRequestFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->server = $_SERVER;
    }

    public function tearDown()
    {
        parent::tearDown();
        $_SERVER = $this->server;
    }

    public function testFromGlobalsUrlBaseDefined()
    {
        Configure::write('App.base', 'basedir');
        $server = [
            'DOCUMENT_ROOT' => '/cake/repo/branches/1.2.x.x/webroot',
            'PHP_SELF' => '/index.php',
            'REQUEST_URI' => '/posts/add',
        ];
        $res = ServerRequestFactory::fromGlobals($server);
        $this->assertEquals('basedir', $res->getAttribute('base'));
        $this->assertEquals('basedir/', $res->getAttribute('webroot'));
        $this->assertEquals('/posts/add', $res->getUri()->getPath());
    }

    public function testFromGlobalsUrlModRewrite()
    {
        Configure::write('App.baseUrl', false);

        $server = [
            'DOCUMENT_ROOT' => '/cake/repo/branches',
            'PHP_SELF' => '/urlencode me/webroot/index.php',
            'REQUEST_URI' => '/posts/view/1',
        ];
        $res = ServerRequestFactory::fromGlobals($server);

        $this->assertEquals('/urlencode%20me', $res->getAttribute('base'));
        $this->assertEquals('/urlencode%20me/', $res->getAttribute('webroot'));
        $this->assertEquals('/posts/view/1', $res->getUri()->getPath());
    }

    public function testFromGlobalsUrlModRewriteRootDir()
    {
        $server = [
            'DOCUMENT_ROOT' => '/cake/repo/branches/1.2.x.x/webroot',
            'PHP_SELF' => '/index.php',
            'REQUEST_URI' => '/posts/add',
        ];
        $res = ServerRequestFactory::fromGlobals($server);

        $this->assertEquals('', $res->getAttribute('base'));
        $this->assertEquals('/', $res->getAttribute('webroot'));
        $this->assertEquals('/posts/add', $res->getUri()->getPath());
    }

    public function testFromGlobalsUrlNoModRewrite()
    {
        Configure::write('App', [
            'dir' => 'app',
            'webroot' => 'webroot',
            'base' => false,
            'baseUrl' => '/cake/index.php'
        ]);
        $server = [
            'DOCUMENT_ROOT' => '/Users/markstory/Sites',
            'SCRIPT_FILENAME' => '/Users/markstory/Sites/cake/index.php',
            'PHP_SELF' => '/cake/index.php/posts/index',
            'REQUEST_URI' => '/cake/index.php/posts/index',
        ];
        $res = ServerRequestFactory::fromGlobals($server);

        $this->assertSame('/cake/webroot/', $res->getAttribute('webroot'));
        $this->assertSame('/cake/index.php', $res->getAttribute('base'));
        $this->assertSame('/posts/index', $res->getUri()->getPath());
    }

    public function testFromGlobalsUrlNoModRewriteRootDir()
    {
        Configure::write('App', [
            'dir' => 'cake',
            'webroot' => 'webroot',
            'base' => false,
            'baseUrl' => '/index.php'
        ]);
        $server = [
            'DOCUMENT_ROOT' => '/Users/markstory/Sites/cake',
            'SCRIPT_FILENAME' => '/Users/markstory/Sites/cake/index.php',
            'PHP_SELF' => '/index.php/posts/add',
            'REQUEST_URI' => '/index.php/posts/add',
        ];
        $res = ServerRequestFactory::fromGlobals($server);

        $this->assertEquals('/webroot/', $res->getAttribute('webroot'));
        $this->assertEquals('/index.php', $res->getAttribute('base'));
        $this->assertEquals('/posts/add', $res->getUri()->getPath());
    }
}
