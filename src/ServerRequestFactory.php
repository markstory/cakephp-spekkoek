<?php
namespace Spekkoek;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Zend\Diactoros\ServerRequestFactory as BaseFactory;

/**
 * Factory for making ServerRequest instances.
 *
 * This subclass adds in CakePHP specific behavior to populate
 * the basePath and webroot attributes. Furthermore the Uri's path
 * is corrected to only contain the 'virtual' path for the request.
 */
abstract class ServerRequestFactory extends BaseFactory
{
    /**
     * {@inheritDoc}
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) {
        $request = parent::fromGlobals($server, $query, $body, $cookies, $files);
        list($base, $webroot) = static::getBase($request);
        $request = $request->withAttribute('base', $base)
            ->withAttribute('webroot', $webroot);
        if ($base) {
            $request = static::updatePath($base, $request);
        }
        return $request;
    }

    protected static function updatePath($base, $request)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        if (strlen($base) > 0 && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        if (strpos($path, '?') !== false) {
            list($path) = explode('?', $path, 2);
        }
        if (empty($path) || $path === '/' || $path === '//' || $path === '/index.php') {
            $path = '/';
        }
        $endsWithIndex = '/webroot/index.php';
        $endsWithLength = strlen($endsWithIndex);
        if (strlen($path) >= $endsWithLength &&
            substr($path, -$endsWithLength) === $endsWithIndex
        ) {
            $path = '/';
        }
        return $request->withUri($uri->withPath($path));
    }

    /**
     * Calculate the base directory and webroot directory.
     *
     * This code is a copy/paste from Cake\Network\Request::_base()
     */
    protected static function getBase($request)
    {
        $path = $request->getUri()->getPath();
        $server = $request->getServerParams();

        $base = $webroot = $baseUrl = null;
        $config = Configure::read('App');
        extract($config);

        if ($base !== false && $base !== null) {
            return [$base, $base . '/'];
        }

        if (!$baseUrl) {
            $base = dirname(Hash::get($server, 'PHP_SELF'));
            // Clean up additional / which cause following code to fail..
            $base = preg_replace('#/+#', '/', $base);

            $indexPos = strpos($base, '/' . $webroot . '/index.php');
            if ($indexPos !== false) {
                $base = substr($base, 0, $indexPos) . '/' . $webroot;
            }
            if ($webroot === basename($base)) {
                $base = dirname($base);
            }

            if ($base === DIRECTORY_SEPARATOR || $base === '.') {
                $base = '';
            }
            $base = implode('/', array_map('rawurlencode', explode('/', $base)));
            return [$base, $base . '/'];
        }

        $file = '/' . basename($baseUrl);
        $base = dirname($baseUrl);

        if ($base === DIRECTORY_SEPARATOR || $base === '.') {
            $base = '';
        }
        $webrootDir = $base . '/';

        $docRoot = Hash::get($server, 'DOCUMENT_ROOT');
        $docRootContainsWebroot = strpos($docRoot, $webroot);

        if (!empty($base) || !$docRootContainsWebroot) {
            if (strpos($webrootDir, '/' . $webroot . '/') === false) {
                $webrootDir .= $webroot . '/';
            }
        }
        return [$base . $file, $webrootDir];
    }

    /**
     * Convert the full URI into the application specific one.
     *
     * The base directory/script name is removed from $uri to get the application URI.
     */
    protected static function getUri($uri, $base)
    {
        if (strlen($base) > 0 && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        return $uri;
    }
}
