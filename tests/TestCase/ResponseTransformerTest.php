<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Zend\Diactoros\Response as PsrResponse;
use Cake\Network\Response as CakeResponse;
use Spekkoek\ResponseTransformer;

class ResponseTransformerTest extends TestCase
{
    protected $server;

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

    public function testToCakeType()
    {
        $psr = new PsrResponse('php://memory', 401, []);
        $result = ResponseTransformer::toCake($psr);
        $this->assertInstanceOf('Cake\Network\Response', $result);
    }

    public function testToCakeStatusCode()
    {
        $psr = new PsrResponse('php://memory', 401, []);
        $result = ResponseTransformer::toCake($psr);
        $this->assertSame(401, $result->statusCode());

        $psr = new PsrResponse('php://memory', 200, []);
        $result = ResponseTransformer::toCake($psr);
        $this->assertSame(200, $result->statusCode());
    }

    public function testToCakeHeaders()
    {
        $psr = new PsrResponse('php://memory', 200, ['X-testing' => 'value']);
        $result = ResponseTransformer::toCake($psr);
        $this->assertSame(['X-testing' => 'value'], $result->header());
    }

    public function testToCakeHeaderMultiple()
    {
        $psr = new PsrResponse('php://memory', 200, ['X-testing' => ['value', 'value2']]);
        $result = ResponseTransformer::toCake($psr);
        $this->assertSame(['X-testing' => ['value', 'value2']], $result->header());
    }

    public function testToCakeBody()
    {
        $psr = new PsrResponse('php://memory', 200, ['X-testing' => ['value', 'value2']]);
        $psr->getBody()->write('A message for you');
        $result = ResponseTransformer::toCake($psr);
        $this->assertSame('A message for you', $result->body());
    }

    public function testToPsrStatusCode()
    {
        $cake = new CakeResponse(['status' => 403]);
        $result = ResponseTransformer::toPsr($cake);
        $this->assertSame(403, $result->getStatusCode());
    }

    public function testToPsrHeaders()
    {
        $cake = new CakeResponse(['status' => 403]);
        $cake->header([
            'X-testing' => ['one', 'two'],
            'Location' => 'http://example.com/testing'
        ]);
        $result = ResponseTransformer::toPsr($cake);
        $expected = [
            'X-testing' => ['one', 'two'],
            'Location' => ['http://example.com/testing'],
        ];
        $this->assertSame($expected, $result->getHeaders());
    }

    public function testToPsrBodyString()
    {
        $cake = new CakeResponse(['status' => 403, 'body' => 'A response for you']);
        $result = ResponseTransformer::toPsr($cake);
        $this->assertSame($cake->body(), '' . $result->getBody());
    }

    public function testToPsrBodyCallable()
    {
        $cake = new CakeResponse(['status' => 200]);
        $cake->body(function () {
            return 'callback response';
        });
        $result = ResponseTransformer::toPsr($cake);
        $this->assertSame('callback response', '' . $result->getBody());
    }

    public function testToPsrBodyFileResponse()
    {
        $cake = $this->getMock('Cake\Network\Response', ['_clearBuffer']);
        $cake->file(__FILE__, ['name' => 'some-file.php', 'download' => true]);

        $result = ResponseTransformer::toPsr($cake);
        $this->assertEquals(
            'attachment; filename="some-file.php"',
            $result->getHeaderLine('Content-Disposition')
        );
        $this->assertEquals(
            'binary',
            $result->getHeaderLine('Content-Transfer-Encoding')
        );
        $this->assertEquals(
            'bytes',
            $result->getHeaderLine('Accept-Ranges')
        );
        $this->assertContains('<?php', '' . $result->getBody());
    }

    public function testToPsrBodyFileResponseFileRange()
    {
        $_SERVER['HTTP_RANGE'] = 'bytes=10-20';
        $cake = $this->getMock('Cake\Network\Response', ['_clearBuffer']);
        $path = dirname(__DIR__) . '/TestApp/asset.css';
        $cake->file($path, ['name' => 'test-asset.css', 'download' => true]);

        $result = ResponseTransformer::toPsr($cake);
        $this->assertEquals(
            'bytes 10-20/38',
            $result->getHeaderLine('Content-Range'),
            'Content-Range header missing'
        );
    }
}
