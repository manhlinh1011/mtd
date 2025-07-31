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
    $routes->get('detail/(:num)', 'CustomerController::detail/$1');
    $routes->post('deposit/(:num)', 'CustomerController::deposit/$1');
    $routes->get('invoices/(:num)', 'CustomerController::invoices/$1');
    $routes->get('update-balance/(:num)', 'CustomerController::updateBalance/$1');
    $routes->post('update-balance/(:num)', 'CustomerController::updateBalance/$1');
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
    $routes->post('update-bulk', 'OrderController::updateBulk');
    $routes->get('export-vn-today', 'OrderController::exportVietnamStockToday');
    $routes->get('export-excel-by-filter', 'OrderController::exportExcelByFilter');
    $routes->get('get-order-count', 'OrderController::getOrderCount');
    $routes->get('zero-price', 'OrderController::zeroPrice');
    // Autocomplete API
    $routes->get('search-customers', 'OrderController::searchCustomers');
    $routes->get('search-sub-customers', 'OrderController::searchSubCustomers');
});

$routes->get('/api/customers', 'ApiController::getAllCustomers');
$routes->get('/api/product-types', 'ApiController::getAllProductTypes');
$routes->post('/api/createorder', 'ApiController::createOrder');
$routes->get('/api/check-tracking', 'ApiController::checkTrackingCode');
$routes->get('/api/check-vietnam-stock', 'ApiController::checkVietnamStockStatus');
$routes->post('/api/update-vietnam-stock', 'ApiController::updateVietnamStockDate');
$routes->post('/api/add-tmdt', 'ApiController::AddTMDT');
$routes->post('/api/add-tmdt-kg16', 'ApiController::AddTMDT_KG16');


$routes->get('exchange-rates', 'ExchangeRateController::index');
$routes->get('exchange-rates/update-form', 'ExchangeRateController::updateForm');
$routes->post('exchange-rates/update', 'ExchangeRateController::update');
$routes->get('api/latest-exchange-rate', 'ExchangeRateController::getLatestRate');

$routes->post('/api/update-vietnam-stock-date', 'OrderController::updateVietnamStockDate');
$routes->post('/api/update-vietnam-stock-date-by-tracking', 'OrderController::updateVietnamStockDateByTrackingCode');

$routes->get('/permissions', 'PermissionController::index');
$routes->get('/permissions/create', 'PermissionController::create');
$routes->post('/permissions/store', 'PermissionController::store');
$routes->match(['GET', 'POST'], '/permissions/assign', 'PermissionController::assign');
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
    $routes->get('cart/count', 'InvoiceController::cartCount');
    $routes->post('cart/check', 'InvoiceController::cartCheck');
    $routes->get('create/(:num)', 'InvoiceController::create/$1');
    $routes->post('store/(:num)', 'InvoiceController::store/$1');
    $routes->get('detail/(:num)', 'InvoiceController::detail/$1');
    $routes->post('addToCartByTrackingCode', 'InvoiceController::addToCartByTrackingCode');
    $routes->get('payments/(:num)', 'InvoiceController::viewPayments/$1');
    $routes->get('payments/create/(:num)', 'InvoiceController::createPayment/$1');
    $routes->post('payments/store/(:num)', 'InvoiceController::storePayment/$1');
    $routes->post('payments/delete/(:num)/(:num)', 'InvoiceController::deletePayment/$1/$2');
    $routes->get('confirmShipping/(:num)', 'InvoiceController::confirmShipping/$1');
    $routes->post('delete/(:num)', 'InvoiceController::delete/$1'); // Xóa phiếu xuất
    $routes->post('reassignOrder/(:num)', 'InvoiceController::reassignOrder/$1'); // Chuyển đơn hàng
    $routes->get('overdue', 'InvoiceController::overdue');
    $routes->get('pending', 'InvoiceController::pending');
    $routes->get('detail/(:num)', 'InvoiceController::detail/$1');
});

$routes->post('/invoices/addPayment/(:num)', 'InvoiceController::addPayment/$1');
$routes->post('/invoices/updateShippingFee/(:num)', 'InvoiceController::updateShippingFee/$1');
$routes->post('/invoices/updateOtherFee/(:num)', 'InvoiceController::updateOtherFee/$1');
$routes->post('/invoices/updateBulkPrices/(:num)', 'InvoiceController::updateBulkPrices/$1');
$routes->post('/invoices/deposit-ajax', 'InvoiceController::depositAjax');

$routes->get('/accounting-statistics', 'AccountingStatisticsController::index');

$routes->get('tracking', 'TrackingController::index');
$routes->get('tracking/check', 'TrackingController::check');


$routes->get('system-logs/', 'SystemLogController::index'); // Hiển thị danh sách log
$routes->get('system-logs/view/(:num)', 'SystemLogController::view/$1'); // Xem chi tiết log (tùy chọn)
$routes->get('system-logs/delete/(:num)', 'SystemLogController::delete/$1'); // Xóa log (tùy chọn)

$routes->get('orders/import', 'OrderController::importForm');
$routes->post('orders/preview', 'OrderController::preview');
$routes->post('orders/import', 'OrderController::import');

$routes->get('/orders/vncheck', 'OrderController::vnCheck');
$routes->post('/orders/checkVietnamStock', 'OrderController::checkVietnamStock');
$routes->post('/orders/updateCustomerAndStock', 'OrderController::updateCustomerAndStock');
$routes->post('/orders/updateVietnamStockDateUI', 'OrderController::updateVietnamStockDateUI');
$routes->get('/orders/get-sub-customers', 'OrderController::getSubCustomers');
$routes->get('/orders/get-sub-customers-by-code', 'OrderController::getSubCustomersByCode');

$routes->get('packages', 'PackageController::index');
$routes->get('packages/detail/(:segment)/(:segment)', 'PackageController::detail/$1/$2');

$routes->get('transactions', 'TransactionController::index');
$routes->post('transactions/delete/(:num)', 'TransactionController::delete/$1');
$routes->post('transactions/withdraw', 'TransactionController::withdraw');

$routes->get('customers/search', 'CustomerController::search');
$routes->get('orders/china-stock', 'OrderController::chinaStock');
$routes->get('orders/vietnam-stock', 'OrderController::vietnamStock');
$routes->get('/invoices/export-excel/(:num)', 'InvoiceController::exportExcel/$1');
$routes->get('/invoices/export-excel-by-filter', 'InvoiceController::exportInvoicesByFilter');
$routes->get('/invoices/export-excel-by-select', 'InvoiceController::exportExcelBySelect');
$routes->post('/invoices/notify-payment', 'InvoiceController::notifyPayment');

$routes->group('financial', function ($routes) {
    $routes->get('/', 'FinancialController::index');
    $routes->get('create', 'FinancialController::create');
    $routes->post('store', 'FinancialController::store');
    $routes->get('approve/(:num)', 'FinancialController::approve/$1');
    $routes->get('reject/(:num)', 'FinancialController::reject/$1');
    $routes->get('income', 'FinancialController::income');
    $routes->get('expense', 'FinancialController::expense');
    $routes->get('dashboard', 'FinancialController::dashboard');
    $routes->post('updateTransactionDate/(:num)', 'FinancialController::updateTransactionDate/$1');
    $routes->get('fund-transactions', 'FinancialController::fundTransactions');
    $routes->get('export-fund-transactions', 'FinancialController::exportFundTransactions');
});
$routes->post('financial/update-transaction-type', 'FinancialController::updateTransactionType');
$routes->get('/customers/sub-customers', 'CustomerController::subCustomerIndex');
$routes->get('/customers/edit-sub/(:num)', 'CustomerController::subCustomerEdit/$1');
$routes->post('/customers/edit-sub/(:num)', 'CustomerController::subCustomerEdit/$1');
$routes->get('/customers/sub-detail/(:num)', 'CustomerController::subCustomerDetail/$1');
$routes->get('/customers/delete-sub/(:num)', 'CustomerController::subCustomerDelete/$1');
$routes->get('/customers/sub-customers/create', 'CustomerController::subCustomerCreate');
$routes->post('/customers/sub-customers/store', 'CustomerController::subCustomerStore');

$routes->get('/api/sub-customers', 'ApiController::getSubCustomers');

$routes->group('funds', function ($routes) {
    $routes->get('/', 'FundController::index');
    $routes->get('create', 'FundController::create');
    $routes->post('store', 'FundController::store');
    $routes->get('edit/(:num)', 'FundController::edit/$1');
    $routes->post('update/(:num)', 'FundController::update/$1');
    $routes->get('delete/(:num)', 'FundController::delete/$1');
    $routes->get('detail/(:num)', 'FundController::detail/$1');
});

$routes->group('affiliate-mapping', function ($routes) {
    $routes->get('/', 'AffiliateMappingController::index');
    $routes->get('create', 'AffiliateMappingController::create');
    $routes->post('store', 'AffiliateMappingController::store');
    $routes->get('edit/(:num)', 'AffiliateMappingController::edit/$1');
    $routes->post('update/(:num)', 'AffiliateMappingController::update/$1');
    $routes->get('delete/(:num)', 'AffiliateMappingController::delete/$1');
});

$routes->get('affiliate-mapping/get-customer-sub-customers/(:num)', 'AffiliateMappingController::getCustomerSubCustomers/$1');

$routes->group('affiliate-pricing', function ($routes) {
    $routes->get('/', 'AffiliatePricingController::index');
    $routes->get('create', 'AffiliatePricingController::create');
    $routes->post('store', 'AffiliatePricingController::store');
    $routes->get('edit/(:num)', 'AffiliatePricingController::edit/$1');
    $routes->post('update/(:num)', 'AffiliatePricingController::update/$1');
    $routes->get('delete/(:num)', 'AffiliatePricingController::delete/$1');
    $routes->get('get-affiliate-pricing/(:num)', 'AffiliatePricingController::getAffiliatePricing/$1');
    $routes->get('get-pricing-by-product-type/(:num)', 'AffiliatePricingController::getPricingByProductType/$1');
});

$routes->group('affiliate-commission', function ($routes) {
    $routes->get('/', 'AffiliateCommissionController::index');
    $routes->get('create', 'AffiliateCommissionController::create');
    $routes->post('store', 'AffiliateCommissionController::store');
    $routes->get('edit/(:num)', 'AffiliateCommissionController::edit/$1');
    $routes->post('update/(:num)', 'AffiliateCommissionController::update/$1');
    $routes->get('delete/(:num)', 'AffiliateCommissionController::delete/$1');
    $routes->get('logs/(:num)', 'AffiliateCommissionController::logs/$1');
});

$routes->get('/zalo-api/get-statistics', 'ZaloApiController::getStatistics');
$routes->post('/zalo-api/get-stock-orders-by-thread-id', 'ZaloApiController::getStockOrdersByThreadIdZalo');
$routes->post('/zalo-api/get-stock-orders-sub-customer-by-thread-id', 'ZaloApiController::getStockOrdersSubCustomerByThreadIdZalo');
$routes->post('/zalo-api/get-stock-orders-by-customer-code', 'ZaloApiController::getStockOrdersByCustomerCode');
$routes->post('/zalo-api/get-stock-orders-by-sub-customer-code', 'ZaloApiController::getStockOrdersBySubCustomerCode');
$routes->post('/zalo-api/set-stock-notification', 'ZaloApiController::setStockNotification');
$routes->post('/zalo-api/set-order-notification', 'ZaloApiController::setOrderNotification');
$routes->post('/zalo-api/set-sub-customer-stock-notification', 'ZaloApiController::setSubCustomerStockNotification');
$routes->post('/zalo-api/set-sub-customer-order-notification', 'ZaloApiController::setSubCustomerOrderNotification');
$routes->get('/zalo-api/list-customer-stock-notification', 'ZaloApiController::getListCustomerHasStockNotification');
$routes->get('/zalo-api/list-sub-customer-stock-notification', 'ZaloApiController::getListSubCustomerHasStockNotification');
$routes->get('/zalo-api/list-sub-customer-stock-notification-with-thread-id/(:segment)', 'ZaloApiController::getListSubCustomerHasStockNotificationWithThreadId/$1');
$routes->post('/zalo-api/get-list-sub-customer-has-stock-notification-by-thread-id', 'ZaloApiController::getListSubCustomerHasStockNotificationByThreadId');

$routes->get('/test', 'TestController::index');

$routes->group('shipping-provider', function ($routes) {
    $routes->get('/', 'ShippingProviderController::index');
    $routes->get('create', 'ShippingProviderController::create');
    $routes->post('store', 'ShippingProviderController::store');
    $routes->get('edit/(:num)', 'ShippingProviderController::edit/$1');
    $routes->post('update/(:num)', 'ShippingProviderController::update/$1');
    $routes->get('delete/(:num)', 'ShippingProviderController::delete/$1');
});

$routes->group('shipping-manager', function ($routes) {
    $routes->get('/', 'ShippingManagerController::index');
    $routes->get('delivered', 'ShippingManagerController::delivered');
    $routes->get('search', 'ShippingManagerController::search');
    $routes->get('create/(:num)', 'ShippingManagerController::create/$1');
    $routes->post('store', 'ShippingManagerController::store');
    $routes->post('confirm/(:num)', 'ShippingManagerController::confirm/$1');
    $routes->get('get-shipping-details/(:num)', 'ShippingManagerController::getShippingDetails/$1');
});

$routes->get('customers/get-sub-customers/(:num)', 'CustomerController::getSubCustomers/$1');

// Order Inspection Routes
$routes->group('order-inspections', function ($routes) {
    $routes->get('/', 'OrderInspectionController::index');
    $routes->get('create', 'OrderInspectionController::create');
    $routes->post('store', 'OrderInspectionController::store');
    $routes->get('edit/(:num)', 'OrderInspectionController::edit/$1');
    $routes->post('update/(:num)', 'OrderInspectionController::update/$1');
    $routes->post('delete/(:num)', 'OrderInspectionController::delete/$1');
    $routes->post('mark-as-notified/(:num)', 'OrderInspectionController::markAsNotified/$1');
});

// API for C# client
$routes->get('/api/pending-inspections', 'OrderInspectionController::getPendingInspections');
$routes->post('/api/confirm-notification', 'OrderInspectionController::confirmNotification');
$routes->post('/api/set-inspection', 'OrderInspectionController::setInspectionByApi');
$routes->get('api/customer/balance/(:num)', 'TransactionController::getCustomerBalanceApi/$1');

// Transaction Types Routes
$routes->group('transaction-types', ['filter' => 'auth'], function ($routes) {
    // Hiển thị danh sách
    $routes->get('/', 'TransactionTypeController::index');

    // Tạo mới
    $routes->get('create', 'TransactionTypeController::create');
    $routes->post('/', 'TransactionTypeController::store');

    // Chỉnh sửa
    $routes->get('edit/(:num)', 'TransactionTypeController::edit/$1');
    $routes->post('update/(:num)', 'TransactionTypeController::update/$1');

    // Xóa
    $routes->get('delete/(:num)', 'TransactionTypeController::delete/$1');

    // Toggle trạng thái
    $routes->get('toggle/(:num)', 'TransactionTypeController::toggleActive/$1');

    // Thống kê
    $routes->get('statistics', 'TransactionTypeController::statistics');

    // API endpoints
    $routes->group('api', function ($routes) {
        $routes->get('by-category', 'TransactionTypeController::getByCategory');
        $routes->get('active', 'TransactionTypeController::getActive');
        $routes->get('statistics', 'TransactionTypeController::getStatistics');
    });
});

$routes->get('transaction-action-config', 'TransactionActionConfigController::index');
$routes->get('transaction-action-config/create', 'TransactionActionConfigController::create');
$routes->post('transaction-action-config/store', 'TransactionActionConfigController::store');
$routes->get('transaction-action-config/edit/(:num)', 'TransactionActionConfigController::edit/$1');
$routes->post('transaction-action-config/update/(:num)', 'TransactionActionConfigController::update/$1');
$routes->post('transaction-action-config/delete/(:num)', 'TransactionActionConfigController::delete/$1');

$routes->post('/auto-api/create-order', 'AutoApiController::createAutoOrder');
