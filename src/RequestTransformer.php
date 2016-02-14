<?php
namespace Spekkoek;

use Cake\Core\Configure;
use Cake\Network\Request as CakeRequest;
use Cake\Utility\Hash;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

/**
 * Translate and transform from PSR7 requests into CakePHP requests.
 *
 * This is an important step for maintaining backwards compatibility
 * with existing CakePHP applications, which depend on the CakePHP request object.
 *
 * There is no reverse transform as the 'application' cannot return a mutated
 * request object.
 */
class RequestTransformer
{
    /**
     * Transform a PSR7 request into a CakePHP one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The PSR7 request.
     * @return \Cake\Network\Request The transformed request.
     */
    public static function toCake(PsrRequest $request)
    {
        $post = $request->getParsedBody();
        $server = $request->getServerParams();

        $files = static::getFiles($request);
        if (!empty($files)) {
            $post = Hash::merge($post, $files);
        }
        $path = $request->getUri()->getPath();
        list($base, $webroot) = static::getBase($path, $server);

        return new CakeRequest([
            'query' => $request->getQueryParams(),
            'post' => $post,
            'cookies' => $request->getCookieParams(),
            'environment' => $server,
            'params' => static::getParams($request),
            'url' => static::getUri($path, $base),
            'base' => $base,
            'webroot' => $webroot,
        ]);
    }

    /**
     * Extract the routing parameters out of the request object.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to extract params from.
     * @return array The routing parameters.
     */
    protected static function getParams($request)
    {
        $attributes = $request->getAttributes();
        $attributes += ['params' => []];
        $attributes['params'] += [
            'plugin' => null,
            'controller' => null,
            'action' => null,
            '_ext' => null,
            'pass' => []
        ];
        return $attributes['params'];
    }

    /**
     * Extract the uploaded files out of the request object.
     *
     * CakePHP expects to get arrays of file information and
     * not the parsed objects that PSR7 requests contain. Downsample the data here.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to extract files from.
     * @return array The routing parameters.
     */
    protected static function getFiles($request)
    {
        return static::convertFiles([], $request->getUploadedFiles());
    }

    /**
     * Convert a nested array of files to arrays.
     *
     * @param array $data The data to add files to.
     * @param array $files The file objects to convert.
     * @param string $path The current array path.
     * @return array Converted file data
     */
    protected static function convertFiles($data, $files, $path = '')
    {
        foreach ($files as $key => $file) {
            $newPath = $path;
            if ($newPath === '') {
                $newPath = $key;
            }
            if ($newPath !== $key) {
                $newPath .= '.' . $key;
            }

            if (is_array($file)) {
                $data = static::convertFiles($data, $file, $newPath);
            } else {
                $data = Hash::insert($data, $newPath, static::convertFile($file));
            }
        }
        return $data;
    }

    /**
     * Convert a single file back into an array.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The file to convert.
     * @return array
     */
    protected static function convertFile($file)
    {
        return [
            'name' => $file->getClientFilename(),
            'type' => $file->getClientMediaType(),
            'tmp_name' => $file->getStream()->getMetadata('uri'),
            'error' => $file->getError(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Calculate the base directory and webroot directory.
     *
     * This code is a copy/paste from Cake\Network\Request::_base()
     */
    protected static function getBase($path, $server)
    {
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
