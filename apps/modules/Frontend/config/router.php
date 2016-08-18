<?php

use Phalcon\Mvc\Router;
use Backend\Models\NewsModel as NewsModel;

$router = new Router();
$router->removeExtraSlashes(true);

$router->add('/', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 'Index',
    'action' => 'index',
    'params' => 1
))->setName($module);
$router->add('/:controller', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 1
));

$router->add('/:controller/:action', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 1,
    'action' => 2
))->convert(
    'action', function ($action) {
    return str_replace('-', '', $action);
});

$router->add('/:controller/:action/:params', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 1,
    'action' => 2,
    'params' => 3
))->convert(
    'action', function ($action) {
    return str_replace('-', '', $action);
});
$router->add('/search', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 'Index',
    'action' => 'search',
))->setName($module);
$router->add('/page/{seo-link}', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 'Page',
    'action' => 'index'
));

$router->add('/manufacturer/{id}', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 'Manufacturer',
    'action' => 'index'
));
$router->add('/menu/{id}', array(
    'namespace' => $moduleClass,
    'module' => $module,
    'controller' => 'Menu',
    'action' => 'index'
));
$router->handle();
return $router;
