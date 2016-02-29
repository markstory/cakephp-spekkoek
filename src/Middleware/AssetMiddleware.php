<?php
namespace Spekkoek\Middleware;

use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use Zend\Diactoros\Stream;

/**
 * Handles serving plugin assets in development mode.
 */
class AssetMiddleware
{
    /**
     * The amount of time to cache the asset.
     *
     * @var string
     */
    protected $cacheTime = '+1 day';

    /**
     * Constructor.
     *
     * @param array $options The options to use
     */
    public function __construct(array $options = [])
    {
        if (!empty($config['cacheTime'])) {
            $this->cacheTime = $config['cacheTime'];
        }
    }

    /**
     * Serve assets if the path matches one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke($request, $response, $next)
    {
        $url = $request->getUri()->getPath();
        if (strpos($url, '..') !== false || strpos($url, '.') === false) {
            return $next($request, $response);
        }

        $assetFile = $this->_getAssetFile($url);
        if ($assetFile === null || !file_exists($assetFile)) {
            return $next($request, $response);
        }

        /* TODO re-enable this
        $response->modified(filemtime($assetFile));
        if ($response->checkNotModified($request)) {
            return $response;
        }
         */

        $pathSegments = explode('.', $url);
        $ext = array_pop($pathSegments);
        return $this->_deliverAsset($request, $response, $assetFile, $ext);
    }

    /**
     * Builds asset file path based off url
     *
     * @param string $url Asset URL
     * @return string Absolute path for asset file
     */
    protected function _getAssetFile($url)
    {
        $parts = explode('/', ltrim($url, '/'));
        $pluginPart = [];
        for ($i = 0; $i < 2; $i++) {
            if (!isset($parts[$i])) {
                break;
            }
            $pluginPart[] = Inflector::camelize($parts[$i]);
            $plugin = implode('/', $pluginPart);
            if ($plugin && Plugin::loaded($plugin)) {
                $parts = array_slice($parts, $i + 1);
                $fileFragment = implode(DIRECTORY_SEPARATOR, $parts);
                $pluginWebroot = Plugin::path($plugin) . 'webroot' . DIRECTORY_SEPARATOR;
                return $pluginWebroot . $fileFragment;
            }
        }
    }

    /**
     * Sends an asset file to the client
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object to use.
     * @param \Psr\Http\Message\ResponseInterface $response The response object to use.
     * @param string $assetFile Path to the asset file in the file system
     * @param string $ext The extension of the file to determine its mime type
     * @return void
     */
    protected function _deliverAsset($request, $response, $assetFile, $ext)
    {
        /* Re-enable this as it makes sense
        $compressionEnabled = $response->compress();
        if ($response->type($ext) === $ext) {
            $contentType = 'application/octet-stream';
            $agent = $request->env('HTTP_USER_AGENT');
            if (preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $agent) || preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent)) {
                $contentType = 'application/octetstream';
            }
            $response->type($contentType);
        }
        if (!$compressionEnabled) {
            $response->header('Content-Length', filesize($assetFile));
        }
        $response->cache(filemtime($assetFile), $this->_cacheTime);
        $response->sendHeaders();
        readfile($assetFile);
        if ($compressionEnabled) {
            ob_end_flush();
        }
        */
        $stream = new Stream(fopen($assetFile, 'rb'));
        return $response->withBody($stream);
    }
}
