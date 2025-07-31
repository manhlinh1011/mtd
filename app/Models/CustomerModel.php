<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'fullname',
        'phone',
        'address',
        'zalo_link',
        'email',
        'customer_code',
        'price_per_kg',       // Thêm trường này
        'price_per_cubic_meter', // Thêm trường này
        'created_at',
        'balance',
        'payment_limit_days',
        'thread_id_zalo',
        'msg_zalo_type',
        'thread_id_zalo_notify_order',
        'msg_zalo_type_notify_order',
        'payment_type',
        'is_free_shipping'
    ];

    public function getCustomersWithOrderCount()
    {
        // Lấy danh sách khách hàng kèm số lượng đơn hàng, thông tin phiếu xuất và số lượng mã phụ
        $customers = $this->select('
            customers.*, 
            (SELECT COUNT(*) FROM orders WHERE orders.customer_id = customers.id) as order_count,
            (SELECT COUNT(DISTINCT id) FROM invoices WHERE invoices.customer_id = customers.id) as invoice_count,
            (SELECT COUNT(DISTINCT id) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.payment_status = "paid") as paid_invoice_count,
            (SELECT COUNT(*) FROM sub_customers WHERE sub_customers.customer_id = customers.id) as sub_customer_count
        ')
            ->orderBy('customers.id', 'DESC')
            ->findAll();

        // Thêm số dư động cho từng khách hàng
        foreach ($customers as &$customer) {
            $customer['dynamic_balance'] = $this->getCustomerBalance($customer['id']);
            // Đảm bảo paid_invoice_count, invoice_count, order_count và sub_customer_count không null
            $customer['paid_invoice_count'] = $customer['paid_invoice_count'] ?? 0;
            $customer['invoice_count'] = $customer['invoice_count'] ?? 0;
            $customer['order_count'] = $customer['order_count'] ?? 0;
            $customer['sub_customer_count'] = $customer['sub_customer_count'] ?? 0;
        }

        return $customers;
    }

    /**
     * Lấy danh sách 10 khách hàng đặt hàng nhiều nhất
     * @return array
     */
    public function getTopCustomers()
    {
        return $this->select('
                customers.customer_code, 
                customers.phone, 
                customers.zalo_link, 
                customers.email, 
                COUNT(orders.id) as total_orders
            ')
            ->join('orders', 'orders.customer_id = customers.id', 'left')
            ->groupBy('customers.id')
            ->orderBy('total_orders', 'DESC')
            ->limit(10)
            ->findAll();
    }

    public function getCustomerBalance($customerId)
    {
        $cacheKey = "customer_balance_{$customerId}";
        $balance = cache($cacheKey);

        if (empty($balance)) {
            // Lấy tổng số dư từ các giao dịch
            $balance = $this->db->table('customer_transactions')
                ->selectSum('amount', 'balance')
                ->where('customer_id', $customerId)
                ->get()
                ->getRow()
                ->balance ?? 0;

            // Debug để kiểm tra giá trị
            //log_message('debug', "Customer ID: {$customerId}, Total Balance: {$balance}");

            cache()->save($cacheKey, $balance, 3600); // Cache trong 1 giờ
        }

        return $balance;
    }

    /**
     * Lấy số dư khách hàng trực tiếp từ database, không qua cache
     * @param int $customerId
     * @return float
     */
    public function getCustomerBalanceDirect($customerId)
    {
        // Lấy tổng số dư từ các giao dịch trực tiếp từ database
        $balance = $this->db->table('customer_transactions')
            ->selectSum('amount', 'balance')
            ->where('customer_id', $customerId)
            ->get()
            ->getRow()
            ->balance ?? 0;

        return $balance;
    }

    public function getCustomerInvoices($customerId, $filters = [], $page = 1, $perPage = 30)
    {
        // Xây dựng query cơ bản
        $builder = $this->db->table('invoices')
            ->select('invoices.*, 
                    invoices.id, 
                    invoices.created_at, 
                    invoices.shipping_fee, 
                    invoices.other_fee, 
                    invoices.shipping_status,
                    invoices.shipping_confirmed_at,
                    invoices.payment_status')
            ->where('customer_id', $customerId);

        // Thêm điều kiện lọc
        if (!empty($filters['shipping_status'])) {
            if ($filters['shipping_status'] === 'confirmed') {
                $builder->where('shipping_status', 'confirmed');
            } else {
                $builder->where('shipping_status', 'pending');
            }
        }
        if (!empty($filters['payment_status'])) {
            $builder->where('payment_status', $filters['payment_status']);
        }

        // Clone query để đếm tổng số bản ghi
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        // Thêm phân trang
        $builder->orderBy('created_at', 'DESC');
        $start = ($page - 1) * $perPage;
        $builder->limit($perPage, $start);

        // Lấy dữ liệu
        $invoices = $builder->get()->getResultArray();

        // Tính toán dynamic_total cho mỗi invoice
        foreach ($invoices as &$invoice) {
            // Lấy tất cả đơn hàng của phiếu xuất
            $orders = $this->db->table('orders')
                ->where('invoice_id', $invoice['id'])
                ->get()
                ->getResultArray();

            $total_amount = 0;
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume);
                $domesticFee = $order['domestic_fee'] * $order['exchange_rate'];
                $total_amount += $finalPrice + $domesticFee;
            }

            // Cộng thêm phí giao hàng và phí khác
            $invoice['dynamic_total'] = $total_amount + (float)$invoice['shipping_fee'] + (float)$invoice['other_fee'];
        }

        // Tạo đối tượng pager
        $pager = service('pager');
        $pager->setPath('customers/invoices/' . $customerId);

        // Tạo segment cho phân trang
        $segment = count(explode('/', uri_string()));
        $pager->setSegment($segment);

        // Tạo links phân trang
        $pager->makeLinks($page, $perPage, $total, 'bootstrap_pagination');

        return [
            'invoices' => $invoices,
            'pager' => $pager
        ];
    }

    public function getCustomerDetail($customerId)
    {
        $db = \Config\Database::connect();

        // Lấy thông tin khách hàng
        $customer = $this->find($customerId);
        if (!$customer) {
            return null;
        }

        // Lấy số dư động
        $balance = $this->getCustomerBalance($customerId);

        // Lấy thống kê đơn hàng
        $orderModel = new \App\Models\OrderModel();
        $orderStats = $orderModel->getOrderStatsByCustomer($customerId);

        // Lấy thống kê phiếu xuất
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoiceStats = $invoiceModel->getInvoiceStatsByCustomer($customerId);

        // Lấy 10 phiếu xuất gần nhất
        $recentInvoices = $invoiceModel->getRecentInvoicesByCustomer($customerId, 10);

        // Lấy lịch sử giao dịch
        $transactions = $db->table('customer_transactions')
            ->select('customer_transactions.*, users.fullname as employee_name')
            ->join('users', 'users.id = customer_transactions.created_by', 'left')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return [
            'customer' => $customer,
            'balance' => $balance,
            'orderStats' => $orderStats,
            'invoiceStats' => $invoiceStats,
            'recentInvoices' => $recentInvoices,
            'transactions' => $transactions
        ];
    }
}
