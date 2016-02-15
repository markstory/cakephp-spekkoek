<?php
namespace Spekkoek\Test\TestCase;

use Cake\TestSuite\TestCase;
use Zend\Diactoros\Response as PsrResponse;
use Cake\Network\Response as CakeResponse;
use Spekkoek\ResponseTransformer;

class ResponseTransformerTest extends TestCase
{
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
        $this->markTestIncomplete();
    }

    public function testToPsrHeaders()
    {
        $this->markTestIncomplete();
    }

    public function testToPsrBody()
    {
        $this->markTestIncomplete();
    }
}
