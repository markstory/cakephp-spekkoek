<?php
use Cake\Routing\Router;

Router::plugin(
    'LayerCake',
    ['path' => '/layer-cake'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
