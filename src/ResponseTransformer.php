<?php
namespace Spekkoek;

use Cake\Network\Response as CakeResponse;
use Psr\Http\Message\ResponseInterface as PsrResponse;

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
     * @param Psr\Http\Message\ResponseInterface $response The response to convert.
     * @return Cake\Network\Response The equivalent CakePHP response
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
     * @param Psr\Http\Message\ResponseInterface $response The response to convert.
     * @return string The response body.
     */
    protected static function getBody($response)
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
     * @param Psr\Http\Message\ResponseInterface $response The response to convert.
     * @return Cake\Network\Response The equivalent CakePHP response
     */
    protected static function collapseHeaders($response)
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
     * @param Cake\Network\Response $response The CakePHP response to convert
     * @param Psr\Http\Message\ResponseInterface $response The equivalent PSR7 response.
     */
    public static function toPsr(CakeResponse $response)
    {
    }
}
