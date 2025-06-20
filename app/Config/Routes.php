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
    $routes->get('authLogs', 'AuthController::authLogs');
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

// 產品
$routes->group('product', function ($routes) {
    $routes->get('/', 'ProductController::index');
    $routes->get('create', 'ProductController::create');
    $routes->get('edit/(:num)', 'ProductController::edit/$1');
    $routes->get('delete/(:num)', 'ProductController::delete/$1');
    $routes->post('save', 'ProductController::save');
});

// 地點
$routes->group('location', function ($routes) {
    $routes->get('/', 'LocationController::index');
    $routes->get('create', 'LocationController::create');
    $routes->get('edit/(:num)', 'LocationController::edit/$1');
    $routes->get('delete/(:num)', 'LocationController::delete/$1');
    $routes->post('save', 'LocationController::save');
});

// 訂單
$routes->group('order', function ($routes) {
    $routes->get('/', 'OrderController::index');
    $routes->get('view/(:num)', 'OrderController::view/$1');
    $routes->get('delete/(:num)', 'OrderController::delete/$1');
    $routes->get('serveSignature/(:segment)', 'OrderController::serveSignature/$1', ['as' => 'signature_image']);
});

// GPS
$routes->group('gps', function ($routes) {
    $routes->get('/', 'GpsController::index');
    $routes->get('create', 'GpsController::create');
    $routes->get('edit/(:num)', 'GpsController::edit/$1');
    $routes->get('delete/(:num)', 'GpsController::delete/$1');
    $routes->post('save', 'GpsController::save');
});

// API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // 登入
    $routes->group('auth', function ($routes) { 
        $routes->get('getUsersList', 'AuthController::getUsersList');
        $routes->post('login', 'AuthController::login');
        $routes->post('logout/(:num)', 'AuthController::logout/$1');
    });

    // 取得大分類
    $routes->get('majorCategory/getMajorCategories', 'MajorCategoryController::getMajorCategories');

    // 取得小分類
    $routes->get('minorCategory/getMinorCategories/(:num)', 'MinorCategoryController::getMinorCategoriesByMajorCategory/$1');

    // 取得產品
    $routes->get('product/getProducts/(:num)', 'ProductController::getProductsByMinorCategoryId/$1');

    // 取得地點
    $routes->get('location/getLocations/(:num)', 'LocationController::getLocations/$1');

    // 訂單
    $routes->group('order', function ($routes) {
        $routes->post('create', 'OrderController::create'); // 新增訂單
        $routes->get('/', 'OrderController::index'); // 取得訂單列表
        $routes->get('detail/(:num)', 'OrderController::detail/$1'); // 取得訂單詳細資料
        $routes->post('update/(:num)', 'OrderController::update/$1'); // 更新訂單
    });

    // 取得GPS
    $routes->get('gps/getOptions', 'GpsController::getOptions');
}); 