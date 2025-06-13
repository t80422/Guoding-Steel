<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/', 'AuthController::index');

// 登入
$routes->group('auth', function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->get('logout', 'AuthController::logout');
});

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

// 大分類
$routes->group('majorCategory', function ($routes) {
    $routes->get('/', 'MajorCategoryController::index');
    $routes->get('create', 'MajorCategoryController::create');
    $routes->get('edit/(:num)', 'MajorCategoryController::edit/$1');
    $routes->get('delete/(:num)', 'MajorCategoryController::delete/$1');
    $routes->post('save', 'MajorCategoryController::save');
});

// 小分類
$routes->group('minorCategory', function ($routes) {
    $routes->get('/', 'MinorCategoryController::index');
    $routes->get('create', 'MinorCategoryController::create');
    $routes->get('edit/(:num)', 'MinorCategoryController::edit/$1');
    $routes->get('delete/(:num)', 'MinorCategoryController::delete/$1');
    $routes->post('save', 'MinorCategoryController::save');
});

// API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // 登入
    $routes->group('auth', function ($routes) { 
        $routes->get('getLoginData', 'AuthController::getLoginData');
        $routes->post('login', 'AuthController::login');
    });

    // 取得大分類
    $routes->get('majorCategory/getMajorCategories', 'MajorCategoryController::getMajorCategories');

    // 取得小分類
    $routes->get('minorCategory/getMinorCategories/(:num)', 'MinorCategoryController::getMinorCategories/$1');
});