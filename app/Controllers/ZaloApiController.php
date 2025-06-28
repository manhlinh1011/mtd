<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CustomerModel;
use App\Models\OrderModel;
use App\Models\SubCustomerModel;
use App\Models\InvoiceModel;

class ZaloApiController extends Controller
{

    protected $orderModel;
    protected $customerModel;
    protected $subCustomerModel;
    protected $invoiceModel;
    protected $db;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
        $this->subCustomerModel = new SubCustomerModel();
        $this->invoiceModel = new InvoiceModel();
        $this->db = \Config\Database::connect();
    }

    public function getStockOrdersByThreadIdZalo()
    {

        $threadIdZalo = $this->request->getPost('threadId');
        // Lấy danh sách đơn hàng có vietnam_stock_date nhưng chưa có trong invoice
        $orders = $this->orderModel
            ->select('orders.*, customers.fullname as customer_name, customers.thread_id_zalo, customers.customer_code, customers.msg_zalo_type')
            ->join('customers', 'customers.id = orders.customer_id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('customers.thread_id_zalo', $threadIdZalo)
            ->findAll();

        // Nhóm theo khách hàng
        $groupedOrders = [];
        foreach ($orders as $order) {
            $customerId = $order['customer_id'];
            if (!isset($groupedOrders[$customerId])) {
                $groupedOrders[$customerId] = [
                    'customer_name' => $order['customer_name'],
                    'customer_code' => $order['customer_code'],
                    'thread_id_zalo' => $order['thread_id_zalo'],
                    'msg_zalo_type' => $order['msg_zalo_type'],
                    'total_orders' => 0,
                    'tracking_codes' => []
                ];
            }
            $groupedOrders[$customerId]['total_orders']++;
            $groupedOrders[$customerId]['tracking_codes'][] = $order['tracking_code'];
        }

        // Chuyển đổi thành mảng
        $result = array_values($groupedOrders);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function getStockOrdersSubCustomerByThreadIdZalo()
    {
        $threadIdZalo = $this->request->getPost('threadId');

        // Kiểm tra dữ liệu đầu vào
        if (empty($threadIdZalo)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Thread ID Zalo không được để trống'
            ]);
        }

        // Lấy thông tin khách hàng và đơn hàng tồn kho trong một lần query
        $results = $this->orderModel
            ->select('
                sub_customers.id as sub_customer_id,
                sub_customers.fullname as sub_customer_name,
                sub_customers.sub_customer_code,
                sub_customers.thread_id_zalo,
                sub_customers.msg_zalo_type,
                COUNT(orders.id) as total_orders,
                GROUP_CONCAT(CONCAT(orders.tracking_code, " - ", orders.total_weight, "Kg") SEPARATOR ",") as tracking_codes
            ')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('sub_customers.thread_id_zalo', $threadIdZalo)
            ->groupBy('sub_customers.id')
            ->findAll();

        if (empty($results)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng tồn kho cho thread ID Zalo: ' . $threadIdZalo
            ]);
        }

        // Format lại dữ liệu
        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'sub_customer_name' => $result['sub_customer_name'],
                'sub_customer_code' => $result['sub_customer_code'],
                'thread_id_zalo' => $result['thread_id_zalo'],
                'msg_zalo_type' => $result['msg_zalo_type'],
                'total_orders' => (int)$result['total_orders'],
                'tracking_codes' => explode(',', $result['tracking_codes'])
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $formattedResults
        ]);
    }

    public function getStockOrdersByCustomerCode()
    {
        $customerCode = $this->request->getPost('customerCode');

        // Kiểm tra dữ liệu đầu vào
        if (empty($customerCode)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mã khách hàng không được để trống'
            ]);
        }

        // Lấy thông tin khách hàng và đơn hàng tồn kho trong một lần query
        $result = $this->orderModel
            ->select('
                customers.id as customer_id,
                customers.fullname as customer_name,
                customers.customer_code,
                customers.thread_id_zalo,
                customers.msg_zalo_type,
                COUNT(orders.id) as total_orders,
                GROUP_CONCAT(CONCAT(orders.tracking_code, " - ", orders.total_weight, " kg") SEPARATOR ",") as tracking_codes
            ')
            ->join('customers', 'customers.id = orders.customer_id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('customers.customer_code', $customerCode)
            ->groupBy('customers.id')
            ->first();

        if (!$result) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng tồn kho cho khách hàng: ' . $customerCode
            ]);
        }

        // Format lại dữ liệu
        $formattedResult = [
            'customer_name' => $result['customer_name'],
            'customer_code' => $result['customer_code'],
            'thread_id_zalo' => $result['thread_id_zalo'],
            'msg_zalo_type' => $result['msg_zalo_type'],
            'total_orders' => (int)$result['total_orders'],
            'tracking_codes' => explode(',', $result['tracking_codes'])
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [$formattedResult]
        ]);
    }

    public function getStockOrdersBySubCustomerCode()
    {
        $subCustomerCode = $this->request->getPost('subCustomerCode');

        // Kiểm tra dữ liệu đầu vào
        if (empty($subCustomerCode)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mã phụ khách hàng không được để trống'
            ]);
        }

        // Lấy thông tin khách hàng và đơn hàng tồn kho trong một lần query
        $result = $this->orderModel
            ->select('
                sub_customers.id as sub_customer_id,
                sub_customers.fullname as sub_customer_name,
                sub_customers.sub_customer_code,
                sub_customers.thread_id_zalo,
                sub_customers.msg_zalo_type,
                COUNT(orders.id) as total_orders,
                GROUP_CONCAT(CONCAT(orders.tracking_code, " - ", orders.total_weight, " kg") SEPARATOR ",") as tracking_codes
            ')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('sub_customers.sub_customer_code', $subCustomerCode)
            ->groupBy('sub_customers.id')
            ->first();

        if (!$result) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng tồn kho cho khách hàng mã phụ: ' . $subCustomerCode
            ]);
        }

        // Format lại dữ liệu
        $formattedResult = [
            'sub_customer_name' => $result['sub_customer_name'],
            'sub_customer_code' => $result['sub_customer_code'],
            'thread_id_zalo' => $result['thread_id_zalo'],
            'msg_zalo_type' => $result['msg_zalo_type'],
            'total_orders' => (int)$result['total_orders'],
            'tracking_codes' => explode(',', $result['tracking_codes'])
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [$formattedResult]
        ]);
    }

    public function setStockNotification()
    {
        $threadIdZalo = $this->request->getPost('threadId');
        $msgZaloType = $this->request->getPost('msgZaloType');
        $customerCode = $this->request->getPost('customerCode');

        // Kiểm tra dữ liệu đầu vào
        if (empty($customerCode)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mã khách hàng không được để trống'
            ]);
        }

        // Kiểm tra msg_zalo_type phải là 0 hoặc 1
        if (!in_array($msgZaloType, ['0', '1', 0, 1])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'msg_zalo_type phải là 0 hoặc 1'
            ]);
        }

        // Chuyển đổi msg_zalo_type thành int
        $msgZaloType = (int)$msgZaloType;

        // Tìm khách hàng theo mã
        $customer = $this->customerModel->where('customer_code', $customerCode)->first();

        if (!$customer) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy khách hàng với mã: ' . $customerCode
            ]);
        }

        // Cập nhật thông tin Zalo
        $data = [
            'thread_id_zalo' => $threadIdZalo,
            'msg_zalo_type' => $msgZaloType
        ];

        try {
            $this->customerModel->update($customer['id'], $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Cập nhật thông tin Zalo nhận thông báo thành công cho khách hàng: ' . $customerCode,
                'data' => [
                    'customer_code' => $customerCode,
                    'thread_id_zalo' => $threadIdZalo,
                    'msg_zalo_type' => $msgZaloType
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật thông tin Zalo: ' . $e->getMessage()
            ]);
        }
    }

    public function setSubCustomerStockNotification()
    {
        $threadIdZalo = $this->request->getPost('threadId');
        $msgZaloType = $this->request->getPost('msgZaloType');
        $subCustomerCode = $this->request->getPost('subCustomerCode');

        // Kiểm tra dữ liệu đầu vào
        if (empty($subCustomerCode)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mã phụ khách hàng không được để trống'
            ]);
        }

        // Kiểm tra msg_zalo_type phải là 0 hoặc 1
        if (!in_array($msgZaloType, ['0', '1', 0, 1])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'msg_zalo_type phải là 0 hoặc 1'
            ]);
        }

        // Chuyển đổi msg_zalo_type thành int
        $msgZaloType = (int)$msgZaloType;

        // Tìm khách hàng theo mã
        $subCustomer = $this->subCustomerModel->where('sub_customer_code', $subCustomerCode)->first();

        if (!$subCustomer) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy mã phụ khách hàng với mã: ' . $subCustomerCode
            ]);
        }

        // Cập nhật thông tin Zalo
        $data = [
            'thread_id_zalo' => $threadIdZalo,
            'msg_zalo_type' => $msgZaloType
        ];

        try {
            $this->subCustomerModel->update($subCustomer['id'], $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Cập nhật thông tin Zalo nhận thông báo thành công cho khách hàng mã phụ: ' . $subCustomerCode,
                'data' => [
                    'sub_customer_code' => $subCustomerCode,
                    'thread_id_zalo' => $threadIdZalo,
                    'msg_zalo_type' => $msgZaloType
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật thông tin Zalo: ' . $e->getMessage()
            ]);
        }
    }

    public function setOrderNotification()
    {
        $threadIdZalo = $this->request->getPost('threadId');
        $msgZaloType = $this->request->getPost('msgZaloType');
        $customerCode = $this->request->getPost('customerCode');

        // Kiểm tra dữ liệu đầu vào
        if (empty($customerCode)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mã khách hàng không được để trống'
            ]);
        }

        // Kiểm tra msg_zalo_type phải là 0 hoặc 1
        if (!in_array($msgZaloType, ['0', '1', 0, 1])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'msg_zalo_type phải là 0 hoặc 1'
            ]);
        }

        // Chuyển đổi msg_zalo_type thành int
        $msgZaloType = (int)$msgZaloType;

        // Tìm khách hàng theo mã
        $customer = $this->customerModel->where('customer_code', $customerCode)->first();

        if (!$customer) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy khách hàng với mã: ' . $customerCode
            ]);
        }

        // Cập nhật thông tin Zalo
        $data = [
            'thread_id_zalo_notify_order' => $threadIdZalo,
            'msg_zalo_type_notify_order' => $msgZaloType
        ];

        try {
            $this->customerModel->update($customer['id'], $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Đơn hàng về kho Việt Nam sẽ được thông báo ngay lập tức cho khách hàng: ' . $customerCode,
                'data' => [
                    'customer_code' => $customerCode,
                    'thread_id_zalo_notify_order' => $threadIdZalo,
                    'msg_zalo_type_notify_order' => $msgZaloType
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật thông tin Zalo: ' . $e->getMessage()
            ]);
        }
    }

    public function setSubCustomerOrderNotification()
    {
        $threadIdZalo = $this->request->getPost('threadId');
        $msgZaloType = $this->request->getPost('msgZaloType');
        $subCustomerCode = $this->request->getPost('subCustomerCode');

        // Kiểm tra dữ liệu đầu vào
        if (empty($subCustomerCode)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Mã phụ khách hàng không được để trống'
            ]);
        }

        // Kiểm tra msg_zalo_type phải là 0 hoặc 1
        if (!in_array($msgZaloType, ['0', '1', 0, 1])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'msg_zalo_type phải là 0 hoặc 1'
            ]);
        }

        // Chuyển đổi msg_zalo_type thành int
        $msgZaloType = (int)$msgZaloType;

        // Tìm khách hàng theo mã
        $subCustomer = $this->subCustomerModel->where('sub_customer_code', $subCustomerCode)->first();

        if (!$subCustomer) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Không tìm thấy mã phụ khách hàng với mã: ' . $subCustomerCode
            ]);
        }

        // Cập nhật thông tin Zalo
        $data = [
            'thread_id_zalo_notify_order' => $threadIdZalo,
            'msg_zalo_type_notify_order' => $msgZaloType
        ];

        try {
            $this->subCustomerModel->update($subCustomer['id'], $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Đơn hàng về kho Việt Nam sẽ được thông báo ngay lập tức cho khách hàng mã phụ: ' . $subCustomerCode,
                'data' => [
                    'sub_customer_code' => $subCustomerCode,
                    'thread_id_zalo_notify_order' => $threadIdZalo,
                    'msg_zalo_type_notify_order' => $msgZaloType
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật thông tin Zalo: ' . $e->getMessage()
            ]);
        }
    }

    function getListCustomerHasStockNotification()
    {
        // Lấy danh sách khách hàng có đơn hàng tồn kho và có thread_id_zalo
        $customers = $this->customerModel
            ->select('customers.*, COUNT(orders.id) as total_stock_orders')
            ->join('orders', 'orders.customer_id = customers.id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('customers.thread_id_zalo IS NOT NULL')
            ->groupBy('customers.id')
            ->findAll();

        $result = [];
        foreach ($customers as $customer) {

            $result[] = [
                'customer_code' => $customer['customer_code'],
                'thread_id_zalo' => $customer['thread_id_zalo'],
                'msg_zalo_type' => $customer['msg_zalo_type']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result
        ]);
    }

    function getListSubCustomerHasStockNotification()
    {
        // Lấy danh sách khách hàng có đơn hàng tồn kho và có thread_id_zalo
        $subCustomers = $this->subCustomerModel
            ->select('sub_customers.*, COUNT(orders.id) as total_stock_orders')
            ->join('orders', 'orders.sub_customer_id = sub_customers.id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('sub_customers.thread_id_zalo IS NOT NULL')
            ->groupBy('sub_customers.id')
            ->findAll();

        $result = [];
        foreach ($subCustomers as $subCustomer) {

            $result[] = [
                'sub_customer_code' => $subCustomer['sub_customer_code'],
                'thread_id_zalo' => $subCustomer['thread_id_zalo'],
                'msg_zalo_type' => $subCustomer['msg_zalo_type']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result
        ]);
    }

    function getListSubCustomerHasStockNotificationWithThreadId($threadIdZalo)
    {
        // Lấy danh sách khách hàng có đơn hàng tồn kho và có thread_id_zalo
        $subCustomers = $this->subCustomerModel
            ->select('sub_customers.*, COUNT(orders.id) as total_stock_orders')
            ->join('orders', 'orders.sub_customer_id = sub_customers.id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('sub_customers.thread_id_zalo', $threadIdZalo)
            ->groupBy('sub_customers.id')
            ->findAll();

        $result = [];
        foreach ($subCustomers as $subCustomer) {

            $result[] = [
                'sub_customer_code' => $subCustomer['sub_customer_code'],
                'thread_id_zalo' => $subCustomer['thread_id_zalo'],
                'msg_zalo_type' => $subCustomer['msg_zalo_type']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result
        ]);
    }

    function getListSubCustomerHasStockNotificationByThreadId()
    {
        $threadIdZalo = $this->request->getPost('threadId');
        // Lấy danh sách khách hàng có đơn hàng tồn kho và có thread_id_zalo
        $subCustomers = $this->subCustomerModel
            ->select('sub_customers.*, COUNT(orders.id) as total_stock_orders')
            ->join('orders', 'orders.sub_customer_id = sub_customers.id')
            ->where('orders.vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id', null)
            ->where('sub_customers.thread_id_zalo', $threadIdZalo)
            ->groupBy('sub_customers.id')
            ->findAll();

        $result = [];
        foreach ($subCustomers as $subCustomer) {

            $result[] = [
                'sub_customer_code' => $subCustomer['sub_customer_code'],
                'thread_id_zalo' => $subCustomer['thread_id_zalo'],
                'msg_zalo_type' => $subCustomer['msg_zalo_type']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function getStatistics()
    {
        $today = date('Y-m-d');
        $startOfDay = $today . ' 00:00:00';
        $endOfDay = $today . ' 23:59:59';

        // Số đơn nhập kho trung quốc (create_at hôm nay và vietnam_stock_date là null)
        $chinaStockOrders = $this->orderModel
            ->where('created_at >=', $startOfDay)
            ->where('created_at <=', $endOfDay)
            ->where('vietnam_stock_date IS NULL')
            ->countAllResults();

        // Số đơn nhập kho việt nam (vietnam_stock_date là hôm nay)
        $vietnamStockOrders = $this->orderModel
            ->where('vietnam_stock_date >=', $startOfDay)
            ->where('vietnam_stock_date <=', $endOfDay)
            ->countAllResults();

        // Số đơn tồn kho (invoice_id là null)
        $stockOrders = $this->orderModel
            ->where('invoice_id IS NULL')
            ->where('vietnam_stock_date IS NOT NULL')
            ->countAllResults();

        // Số phiếu xuất được tạo hôm nay
        $createdInvoices = $this->db->table('invoices')
            ->where('created_at >=', $startOfDay)
            ->where('created_at <=', $endOfDay)
            ->countAllResults();

        // Số phiếu xuất được giao hôm nay
        $shippedInvoices = $this->db->table('invoices')
            ->where('shipping_confirmed_at >=', $startOfDay)
            ->where('shipping_confirmed_at <=', $endOfDay)
            ->countAllResults();


        // Số tiền khách nạp hôm nay
        $depositAmount = $this->db->table('customer_transactions')
            ->select('COALESCE(SUM(amount), 0) as total_amount')
            ->where('created_at >=', $startOfDay)
            ->where('created_at <=', $endOfDay)
            ->where('transaction_type', 'deposit')
            ->get()
            ->getRow()
            ->total_amount;

        // Format số tiền theo định dạng Việt Nam
        $formatMoney = function ($amount) {
            return number_format($amount, 0, ',', '.') . 'đ';
        };


        // Đếm số phiếu xuất quá hạn 7 ngày
        $totalOverdueInvoices = $this->invoiceModel
            ->where('payment_status', 'unpaid')
            ->where('created_at <=', date('Y-m-d', strtotime('-6 days')))
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'china_stock_orders_today' => $chinaStockOrders,
                'vietnam_stock_orders_today' => $vietnamStockOrders,
                'stock_orders' => $stockOrders,
                'created_invoices_today' => $createdInvoices,
                'shipped_invoices_today' => $shippedInvoices,
                'invoice_overdue_7day' => $totalOverdueInvoices,
                'deposit_amount_today' => $formatMoney($depositAmount),
                'date' => $today
            ]
        ]);
    }
}
