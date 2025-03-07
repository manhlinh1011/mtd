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
    $routes->post('update/(:num)', 'OrderController::update/$1');
    $routes->get('export', 'OrderController::exportToExcel');
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

$routes->get('/permissions', 'PermissionController::index');
$routes->get('/permissions/create', 'PermissionController::create');
$routes->post('/permissions/store', 'PermissionController::store');
$routes->match(['get', 'post'], '/permissions/assign', 'PermissionController::assign');
$routes->post('/permissions/saveAssignedPermissions', 'PermissionController::saveAssignedPermissions');
$routes->post('/permissions/storeAssignment', 'PermissionController::storeAssignment');

$routes->group('invoices', function ($routes) {
    $routes->get('/', 'InvoiceController::index');
    $routes->get('cart', 'InvoiceController::cart');
    $routes->post('addOrderToCart', 'InvoiceController::addOrderToCart');
    $routes->post('removeOrderFromCart/(:num)', 'InvoiceController::removeOrderFromCart/$1');
    $routes->get('createInvoiceForm/(:num)', 'InvoiceController::createInvoiceForm/$1');
    $routes->post('createInvoice', 'InvoiceController::createInvoice');
    $routes->post('cart/add', 'InvoiceController::cartAdd');
    $routes->get('create/(:num)', 'InvoiceController::create/$1');
    $routes->post('store/(:num)', 'InvoiceController::store/$1');
    $routes->get('detail/(:num)', 'InvoiceController::detail/$1');
    $routes->post('addToCartByTrackingCode', 'InvoiceController::addToCartByTrackingCode');

    // Thêm các tuyến đường cho thanh toán hóa đơn
    $routes->get('payments/(:num)', 'InvoiceController::viewPayments/$1');           // Xem danh sách thanh toán của hóa đơn
    $routes->get('payments/create/(:num)', 'InvoiceController::createPayment/$1');  // Form thêm thanh toán
    $routes->post('payments/store/(:num)', 'InvoiceController::storePayment/$1');   // Lưu thanh toán mới
    $routes->get('payments/delete/(:num)/(:num)', 'InvoiceController::deletePayment/$1/$2'); // Xóa thanh toán (invoice_id/payment_id)

    $routes->get('confirmShipping/(:num)', 'InvoiceController::confirmShipping/$1');
});

$routes->post('/invoices/addPayment/(:num)', 'InvoiceController::addPayment/$1');
$routes->post('/invoices/updateShippingFee/(:num)', 'InvoiceController::updateShippingFee/$1');
$routes->post('/invoices/updateOtherFee/(:num)', 'InvoiceController::updateOtherFee/$1');
$routes->post('/invoices/updateBulkPrices/(:num)', 'InvoiceController::updateBulkPrices/$1');

$routes->get('/accounting-statistics', 'AccountingStatisticsController::index');

$routes->group('customers', function ($routes) {
    $routes->get('detail/(:num)', 'CustomerController::detail/$1');
    $routes->post('deposit/(:num)', 'CustomerController::deposit/$1');
});

$routes->get('tracking', 'TrackingController::index');
$routes->get('tracking/check', 'TrackingController::check');
