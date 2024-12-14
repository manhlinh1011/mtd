<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/login', 'Login::index');
$routes->post('/login/authenticate', 'Login::authenticate');
$routes->get('/logout', 'Login::logout');
$routes->get('/user', 'User::index');
$routes->get('/user/create', 'User::create');
$routes->post('/user/store', 'User::store');
$routes->get('/user/edit/(:num)', 'User::edit/$1');
$routes->post('/user/update/(:num)', 'User::update/$1');
$routes->get('/user/delete/(:num)', 'User::delete/$1');

// Quản lý vai trò
$routes->get('/user/manageRoles', 'User::manageRoles');
$routes->get('/user/createRole', 'User::createRole');
$routes->post('/user/storeRole', 'User::storeRole');
$routes->get('/user/editRole/(:num)', 'User::editRole/$1');
$routes->post('/user/updateRole/(:num)', 'User::updateRole/$1');
$routes->get('/user/deleteRole/(:num)', 'User::deleteRole/$1');



$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/profile', 'Profile::index');
});

$routes->group('customers', function ($routes) {
    $routes->get('/', 'CustomerController::index');
    $routes->get('create', 'CustomerController::create');
    $routes->post('create', 'CustomerController::create');
    $routes->get('edit/(:num)', 'CustomerController::edit/$1');
    $routes->post('edit/(:num)', 'CustomerController::edit/$1');
    $routes->get('delete/(:num)', 'CustomerController::delete/$1');
    $routes->post('update-bulk', 'CustomerController::updateBulk');
});

$routes->group('product-types', function ($routes) {
    $routes->get('/', 'ProductTypeController::index');
    $routes->get('create', 'ProductTypeController::create');
    $routes->post('create', 'ProductTypeController::create');
    $routes->get('edit/(:num)', 'ProductTypeController::edit/$1');
    $routes->post('edit/(:num)', 'ProductTypeController::edit/$1');
    $routes->get('delete/(:num)', 'ProductTypeController::delete/$1');
});

$routes->group('orders', function ($routes) {
    $routes->get('/', 'OrderController::index');
    $routes->get('create', 'OrderController::create');
    $routes->post('create', 'OrderController::create');
    $routes->get('edit/(:num)', 'OrderController::edit/$1');
    $routes->post('edit/(:num)', 'OrderController::edit/$1');
    $routes->get('delete/(:num)', 'OrderController::delete/$1');
});

$routes->get('/api/customers', 'ApiController::getAllCustomers');
$routes->get('/api/product-types', 'ApiController::getAllProductTypes');
$routes->post('/api/createorder', 'ApiController::createOrder');
$routes->get('/api/check-tracking', 'ApiController::checkTrackingCode');

$routes->get('exchange-rates', 'ExchangeRateController::index');
$routes->get('exchange-rates/update-form', 'ExchangeRateController::updateForm');
$routes->post('exchange-rates/update', 'ExchangeRateController::update');

$routes->get('api/latest-exchange-rate', 'ExchangeRateController::getLatestRate');

$routes->post('/orders/update-bulk', 'OrderController::updateBulk');

$routes->post('/api/update-vietnam-stock-date', 'OrderController::updateVietnamStockDate');
$routes->post('/api/update-vietnam-stock-date-by-tracking', 'OrderController::updateVietnamStockDateByTrackingCode');
