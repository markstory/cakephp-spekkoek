<?php
namespace Spekkoek\Test\TestCase;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use Spekkoek\RequestTransformer;
use Zend\Diactoros\ServerRequestFactory;

class RequestTransformerTest extends TestCase
{
    public function testToCakeGetParams()
    {
        $psr = ServerRequestFactory::fromGlobals(null, ['a' => 'b', 'c' => ['d' => 'e']]);
        $cake = RequestTransformer::toCake($psr);
        $this->assertEquals('b', $cake->query('a'));
        $this->assertEquals(['d' => 'e'], $cake->query('c'));
        $this->assertEmpty($cake->data);
        $this->assertEmpty($cake->cookies);
    }

    public function testToCakePostParams()
    {
        $psr = ServerRequestFactory::fromGlobals(null, null, ['title' => 'first post', 'some' => 'data']);
        $cake = RequestTransformer::toCake($psr);
        $this->assertEquals('first post', $cake->data('title'));
        $this->assertEquals('data', $cake->data('some'));
        $this->assertEmpty($cake->query);
        $this->assertEmpty($cake->cookies);
    }

    public function testToCakeCookies()
    {
        $psr = ServerRequestFactory::fromGlobals(null, null, null, ['gtm' => 'watchingyou']);
        $cake = RequestTransformer::toCake($psr);
        $this->assertEmpty($cake->query);
        $this->assertEmpty($cake->data);
        $this->assertEquals('watchingyou', $cake->cookie('gtm'));
    }

    public function testToCakeHeadersAndEnvironment()
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'example.com',
            'REQUEST_METHOD' => 'PATCH',
            'HTTP_ACCEPT' => 'application/json',
            'SERVER_PROTOCOL' => '1.1',
            'SERVER_PORT' => 443,
        ];
        $psr = ServerRequestFactory::fromGlobals($server);
        $cake = RequestTransformer::toCake($psr);
        $this->assertEmpty($cake->query);
        $this->assertEmpty($cake->data);
        $this->assertEmpty($cake->cookie);

        $this->assertSame('application/json', $cake->header('accept'));
        $this->assertSame('PATCH', $cake->method());
        $this->assertSame('https', $cake->scheme());
        $this->assertSame(443, $cake->port());
        $this->assertSame('example.com', $cake->host());
    }

    public function testToCakeParamsEmpty()
    {
        $psr = ServerRequestFactory::fromGlobals();
        $cake = RequestTransformer::toCake($psr);

        $this->assertArrayHasKey('controller', $cake->params);
        $this->assertArrayHasKey('action', $cake->params);
        $this->assertArrayHasKey('plugin', $cake->params);
        $this->assertArrayHasKey('_ext', $cake->params);
        $this->assertArrayHasKey('pass', $cake->params);
    }

    public function testToCakeParamsPopulated()
    {
        $psr = ServerRequestFactory::fromGlobals();
        $psr = $psr->withAttribute('params', ['controller' => 'Articles', 'action' => 'index']);
        $cake = RequestTransformer::toCake($psr);

        $this->assertEmpty($cake->query);
        $this->assertEmpty($cake->data);
        $this->assertEmpty($cake->cookie);

        $this->assertSame('Articles', $cake->param('controller'));
        $this->assertSame('index', $cake->param('action'));
        $this->assertArrayHasKey('plugin', $cake->params);
        $this->assertArrayHasKey('_ext', $cake->params);
        $this->assertArrayHasKey('pass', $cake->params);
    }

    public function testToCakeUploadedFiles()
    {
        $files = [
            'image_main' => [
                'name' => ['file' => 'born on.txt'],
                'type' => ['file' => 'text/plain'],
                'tmp_name' => ['file' => __FILE__],
                'error' => ['file' => 0],
                'size' => ['file' => 17178]
            ],
            0 => [
                'name' => ['image' => 'scratch.text'],
                'type' => ['image' => 'text/plain'],
                'tmp_name' => ['image' => __FILE__],
                'error' => ['image' => 0],
                'size' => ['image' => 1490]
            ],
            'pictures' => [
                'name' => [
                    0 => ['file' => 'a-file.png'],
                    1 => ['file' => 'a-moose.png']
                ],
                'type' => [
                    0 => ['file' => 'image/png'],
                    1 => ['file' => 'image/jpg']
                ],
                'tmp_name' => [
                    0 => ['file' => __FILE__],
                    1 => ['file' => __FILE__]
                ],
                'error' => [
                    0 => ['file' => 0],
                    1 => ['file' => 0]
                ],
                'size' => [
                    0 => ['file' => 17188],
                    1 => ['file' => 2010]
                ],
            ]
        ];
        $post = [
            'pictures' => [
                0 => ['name' => 'A cat'],
                1 => ['name' => 'A moose']
            ],
            0 => [
                'name' => 'A dog'
            ]
        ];
        $psr = ServerRequestFactory::fromGlobals(null, null, $post, null, $files);
        $request = RequestTransformer::toCake($psr);
        $expected = [
            'image_main' => [
                'file' => [
                    'name' => 'born on.txt',
                    'type' => 'text/plain',
                    'tmp_name' => __FILE__,
                    'error' => 0,
                    'size' => 17178,
                ]
            ],
            'pictures' => [
                0 => [
                    'name' => 'A cat',
                    'file' => [
                        'name' => 'a-file.png',
                        'type' => 'image/png',
                        'tmp_name' => __FILE__,
                        'error' => 0,
                        'size' => 17188,
                    ]
                ],
                1 => [
                    'name' => 'A moose',
                    'file' => [
                        'name' => 'a-moose.png',
                        'type' => 'image/jpg',
                        'tmp_name' => __FILE__,
                        'error' => 0,
                        'size' => 2010,
                    ]
                ]
            ],
            0 => [
                'name' => 'A dog',
                'image' => [
                    'name' => 'scratch.text',
                    'type' => 'text/plain',
                    'tmp_name' => __FILE__,
                    'error' => 0,
                    'size' => 1490
                ]
            ]
        ];
        $this->assertEquals($expected, $request->data);
    }

    public function testToCakeUrlModRewrite()
    {
        Configure::write('App.baseUrl', false);

        $server = [
            'DOCUMENT_ROOT' => '/cake/repo/branches',
            'PHP_SELF' => '/urlencode me/webroot/index.php',
            'REQUEST_URI' => '/posts/view/1',
        ];
        $psr = ServerRequestFactory::fromGlobals($server);
        $cake = RequestTransformer::toCake($psr);

        $this->assertEquals('/urlencode%20me', $cake->base);
        $this->assertEquals('/urlencode%20me/', $cake->webroot);
        $this->assertEquals('posts/view/1', $cake->url);
    }

    public function testToCakeUrlNoModRewrite()
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
        $psr = ServerRequestFactory::fromGlobals($server);
        $cake = RequestTransformer::toCake($psr);

        $this->assertSame('/cake/webroot/', $cake->webroot);
        $this->assertSame('/cake/index.php', $cake->base);
        $this->assertSame('posts/index', $cake->url);
    }

    public function testToCakeInputStream()
    {
        $this->markTestIncomplete();
    }

    public function testToCakeSession()
    {
        $this->markTestIncomplete();
    }
}
