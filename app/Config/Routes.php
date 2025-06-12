<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Login::index');
$routes->post('/login/authenticate', 'Login::authenticate');
$routes->get('/login/logout', 'Login::logout');

// 職位
$routes->group('position', function ($routes) {
    $routes->get('/', 'PositionController::index');
    $routes->get('create', 'PositionController::create');
    $routes->get('edit/(:num)', 'PositionController::edit/$1');
    $routes->get('delete/(:num)', 'PositionController::delete/$1');
    $routes->post('save', 'PositionController::save');
});

// 使用者
$routes->group('user', function ($routes) {
    $routes->get('/', 'UserController::index');
    $routes->get('create', 'UserController::create');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->get('delete/(:num)', 'UserController::delete/$1');
    $routes->post('save', 'UserController::save');
});