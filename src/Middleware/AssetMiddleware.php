<?php
namespace Spekkoek\Middleware;

use Cake\Core\Plugin;
use Cake\Filesystem\File;
use Cake\Utility\Inflector;
use Zend\Diactoros\Response;
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
        if (!empty($options['cacheTime'])) {
            $this->cacheTime = $options['cacheTime'];
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

        $file = new File($assetFile);
        $modifiedTime = $file->lastChange();
        if ($this->isNotModified($request, $file)) {
            $headers = $response->getHeaders();
            $headers['Last-Modified'] = date(DATE_RFC850, $modifiedTime);
            return new Response('php://memory', 304, $headers);
        }
        return $this->_deliverAsset($request, $response, $file);
    }

    /**
     * Check the not modified header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to check.
     * @param \Cake\Filesystem\File $file The file object to compare.
     * @return bool
     */
    protected function isNotModified($request, $file)
    {
        $modifiedSince = $request->getHeaderLine('If-Modified-Since');
        if (!$modifiedSince) {
            return false;
        }
        return strtotime($modifiedSince) === $file->lastChange();
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
        return '';
    }

    /**
     * Sends an asset file to the client
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object to use.
     * @param \Psr\Http\Message\ResponseInterface $response The response object to use.
     * @param \Cake\Filesystem\File $file The file wrapper for the file.
     * @return \Psr\Http\Message\ResponseInterface The response with the file & headers.
     */
    protected function _deliverAsset($request, $response, $file)
    {
        $contentType = $file->mime() ?: 'application/octet-stream';
        $modified = $file->lastChange();
        $expire = strtotime($this->cacheTime);
        $maxAge = $expire - time();

        $stream = new Stream(fopen($file->path, 'rb'));
        return $response->withBody($stream)
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Cache-Control', 'public,max-age=' . $maxAge)
            ->withHeader('Date', gmdate('D, j M Y G:i:s \G\M\T', time()))
            ->withHeader('Last-Modified', gmdate('D, j M Y G:i:s \G\M\T', $modified))
            ->withHeader('Expires', gmdate('D, j M Y G:i:s \G\M\T', $expire));
    }
}
