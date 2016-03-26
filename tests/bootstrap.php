<?php
/**
 * Test suite bootstrap for LayerCake.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Cake\Core\Configure;

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

define('APP', __DIR__ . '/TestApp/');
define('TESTS', __DIR__);
define('TMP', sys_get_temp_dir());

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => 'src',
    'webroot' => 'webroot',
    'www_root' => APP . 'webroot',
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'plugins' => [APP . 'Plugin' . DS],
        'templates' => [APP . 'Template' . DS],
    ]
]);
