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

// 首頁
$routes->get('home', 'HomeController::index');

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
    $routes->get('locationSettings/(:num)', 'UserController::locationSettings/$1');
    $routes->post('saveLocationSettings', 'UserController::saveLocationSettings');
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
    $routes->get('getMinorCategories/(:num)', 'MinorCategoryController::getMinorCategories/$1');
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
    $routes->get('materialUsage/(:num)', 'LocationController::materialUsage/$1');
});

// 訂單
$routes->group('order', function ($routes) {
    $routes->get('/', 'OrderController::index');
    $routes->get('edit/(:num)', 'OrderController::edit/$1');
    $routes->post('save', 'OrderController::save');
    $routes->get('delete/(:num)', 'OrderController::delete/$1');
    $routes->get('serveSignature/(:segment)', 'OrderController::serveSignature/$1', ['as' => 'signature_image']);
    $routes->get('print/(:num)', 'OrderController::print/$1');
    $routes->get('getDetail/(:num)', 'OrderController::getDetail/$1');
});

// GPS
$routes->group('gps', function ($routes) {
    $routes->get('/', 'GpsController::index');
    $routes->get('create', 'GpsController::create');
    $routes->get('edit/(:num)', 'GpsController::edit/$1');
    $routes->get('delete/(:num)', 'GpsController::delete/$1');
    $routes->post('save', 'GpsController::save');
});

// 租賃單
$routes->group('rental', function ($routes) {
    $routes->get('/', 'RentalController::index');
    $routes->get('image/(:segment)', 'RentalController::image/$1');
    $routes->get('delete/(:num)', 'RentalController::delete/$1');
});

// 機械
$routes->group('machine', function ($routes) {
    $routes->get('/', 'MachineController::index');
    $routes->get('create', 'MachineController::create');
    $routes->get('edit/(:num)', 'MachineController::edit/$1');
    $routes->get('delete/(:num)', 'MachineController::delete/$1');
    $routes->post('save', 'MachineController::save');
});

// 機械保養
$routes->group('machineMaintenance', function ($routes) {
    $routes->get('/', 'MachineMaintenanceController::index');
    $routes->get('create', 'MachineMaintenanceController::create');
    $routes->get('edit/(:num)', 'MachineMaintenanceController::edit/$1');
    $routes->get('delete/(:num)', 'MachineMaintenanceController::delete/$1');
    $routes->post('save', 'MachineMaintenanceController::save');
});

// 機械維修
$routes->group('machineRepair', function ($routes) {
    $routes->get('/', 'MachineRepairController::index');
    $routes->get('create', 'MachineRepairController::create');
    $routes->get('edit/(:num)', 'MachineRepairController::edit/$1');
    $routes->get('delete/(:num)', 'MachineRepairController::delete/$1');
    $routes->post('save', 'MachineRepairController::save');
});

// 廠商
$routes->group('manufacturer', function ($routes) {
    $routes->get('/', 'ManufacturerController::index');
    $routes->get('create', 'ManufacturerController::create');
    $routes->get('edit/(:num)', 'ManufacturerController::edit/$1');
    $routes->get('delete/(:num)', 'ManufacturerController::delete/$1');
    $routes->post('save', 'ManufacturerController::save');
});

// 庫存
$routes->group('inventory', function ($routes) {
    $routes->get('/', 'InventoryController::index');
    $routes->get('create', 'InventoryController::create');
    $routes->get('edit/(:num)', 'InventoryController::edit/$1');
    $routes->get('delete/(:num)', 'InventoryController::delete/$1');
    $routes->post('save', 'InventoryController::save');
});

// 鋪路鋼板
$routes->get('roadPlate', 'RoadPlateController::index');

// 租賃訂單管理
$routes->group('rentalOrder', function ($routes) {
    $routes->get('/', 'RentalController::index_order');
    $routes->get('create', 'RentalController::createOrder');
    $routes->get('edit/(:num)', 'RentalController::editOrder/$1');
    $routes->get('getDetail/(:num)', 'RentalController::getDetail/$1');
    $routes->get('delete/(:num)', 'RentalController::deleteOrder/$1');
    $routes->post('save', 'RentalController::saveOrder');
});

// Excel匯入
$routes->group('excel', function ($routes) {
    $routes->get('/', 'ExcelController::index');
    $routes->post('import', 'ExcelController::import');
    $routes->post('save', 'ExcelController::save');
});

// 廠商庫存
$routes->group('manufacturerInventory', function ($routes) {
    $routes->get('/', 'ManufacturerInventoryController::index');
    $routes->get('create', 'ManufacturerInventoryController::create');
    $routes->get('edit/(:num)', 'ManufacturerInventoryController::edit/$1');
    $routes->get('delete/(:num)', 'ManufacturerInventoryController::delete/$1');
    $routes->post('save', 'ManufacturerInventoryController::save');
});

// 施工項目
$routes->group('projectItem', function ($routes) {
    $routes->get('/', 'ProjectItemController::index');
    $routes->get('create', 'ProjectItemController::create');
    $routes->get('edit/(:num)', 'ProjectItemController::edit/$1');
    $routes->get('delete/(:num)', 'ProjectItemController::delete/$1');
    $routes->post('save', 'ProjectItemController::save');
    $routes->get('getItems', 'ProjectItemController::getItems');
});

// 訂單明細施工項目
$routes->group('orderDetailProjectItem', function ($routes) {
    $routes->get('getDetail/(:num)', 'OrderDetailProjectItemController::getDetail/$1');
    $routes->post('save', 'OrderDetailProjectItemController::save');
});

// 租賃明細施工項目
$routes->group('rentalDetailProjectItem', function ($routes) {
    $routes->get('getDetail/(:num)', 'RentalDetailProjectItemController::getDetail/$1');
    $routes->post('save', 'RentalDetailProjectItemController::save');
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
    $routes->get('product/getProducts/(:num)', 'ProductController::getProductList/$1');

    // 取得地點
    $routes->group('location', function ($routes) {
        // 取得地點
        $routes->get('getLocations/(:num)', 'LocationController::getLocations/$1');

        // 工地用料情況
        $routes->get('materialUsage/(:num)', 'LocationController::materialUsage/$1');
    });

    // 取得使用者地點
    $routes->get('userLocation/getUserLocations', 'UserLocationController::index');

    // 訂單
    $routes->group('order', function ($routes) {
        $routes->post('create', 'OrderController::create'); // 新增訂單
        $routes->get('/', 'OrderController::index'); // 取得訂單列表
        $routes->get('detail/(:num)', 'OrderController::detail/$1'); // 取得訂單詳細資料
        $routes->post('update/(:num)', 'OrderController::update/$1'); // 更新訂單
        $routes->get('history', 'OrderController::history'); // 歷史紀錄
    });

    // 取得GPS
    $routes->get('gps/getOptions', 'GpsController::getOptions');

    // 上傳租賃單
    $routes->post('rental', 'RentalController::create');

    // 機械保養
    $routes->get('machine-maintenance', 'MachineMaintenanceController::index');

    // 機械維修
    $routes->get('machine-repair', 'MachineRepairController::index');

    // 鋪路鋼板
    $routes->get('roadPlate', 'RoadPlateController::index');
});
