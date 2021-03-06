<?php
namespace Spekkoek;

use Cake\Network\Response as CakeResponse;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Zend\Diactoros\Response as DiactorosResponse;
use Zend\Diactoros\CallbackStream;
use Zend\Diactoros\Stream;

/**
 * This class converts PSR7 responses into CakePHP ones and back again.
 *
 * By bridging the CakePHP and PSR7 responses together, applications
 * can be embedded as PSR7 middleware in a fully compatible way.
 */
class ResponseTransformer
{

    /**
     * Convert a PSR7 Response into a CakePHP one.
     *
     * @param PsrResponse $response The response to convert.
     * @return CakeResponse The equivalent CakePHP response
     */
    public static function toCake(PsrResponse $response)
    {
        $data = [
            'status' => $response->getStatusCode(),
            'body' => static::getBody($response),
        ];
        $cake = new CakeResponse($data);
        $cake->header(static::collapseHeaders($response));
        return $cake;
    }

    /**
     * Get the response body from a PSR7 Response.
     *
     * @param PsrResponse $response The response to convert.
     * @return string The response body.
     */
    protected static function getBody(PsrResponse $response)
    {
        $stream = $response->getBody();
        if ($stream->getSize() === 0) {
            return '';
        }
        $stream->rewind();
        return $stream->getContents();
    }

    /**
     * Convert a PSR7 Response headers into a flat array
     *
     * @param PsrResponse $response The response to convert.
     * @return CakeResponse The equivalent CakePHP response
     */
    protected static function collapseHeaders(PsrResponse $response)
    {
        $out = [];
        foreach ($response->getHeaders() as $name => $value) {
            if (count($value) === 1) {
                $out[$name] = $value[0];
            } else {
                $out[$name] = $value;
            }
        }
        return $out;
    }

    /**
     * Convert a CakePHP response into a PSR7 one.
     *
     * @param CakeResponse $response The CakePHP response to convert
     * @return PsrResponse $response The equivalent PSR7 response.
     */
    public static function toPsr(CakeResponse $response)
    {
        $status = $response->statusCode();
        $headers = $response->header();
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = $response->type();
        }
        $body = $response->body();
        $stream = 'php://memory';
        if (is_string($body)) {
            $stream = new Stream('php://memory', 'wb');
            $stream->write($response->body());
        }
        if (is_callable($body)) {
            $stream = new CallbackStream($body);
        }
        // This is horrible, but CakePHP doesn't have a getFile() method just yet.
        $fileProp = new \ReflectionProperty($response, '_file');
        $fileProp->setAccessible(true);
        $file = $fileProp->getValue($response);
        if ($file) {
            $stream = new Stream($file->path, 'rb');
        }
        return new DiactorosResponse($stream, $status, $headers);
    }
}
