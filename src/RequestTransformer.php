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

        return new CakeRequest([
            'query' => $request->getQueryParams(),
            'post' => $post,
            'cookies' => $request->getCookieParams(),
            'environment' => $server,
            'params' => static::getParams($request),
            'url' => $request->getUri()->getPath(),
            'base' => $request->getAttribute('base', ''),
            'webroot' => $request->getAttribute('webroot', '/'),
        ]);
    }

    /**
     * Extract the routing parameters out of the request object.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to extract params from.
     * @return array The routing parameters.
     */
    protected static function getParams(PsrRequest $request)
    {
        $params = (array)$request->getAttribute('params', []);
        $params += [
            'plugin' => null,
            'controller' => null,
            'action' => null,
            '_ext' => null,
            'pass' => []
        ];
        return $params;
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
}
