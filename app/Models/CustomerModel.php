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
        'balance'
    ];

    public function getCustomersWithOrderCount()
    {
        // Lấy danh sách khách hàng kèm số lượng đơn hàng và thông tin phiếu xuất
        $customers = $this->select('
            customers.*, 
            COUNT(orders.id) as order_count,
            COUNT(DISTINCT invoices.id) as invoice_count, 
            COUNT(DISTINCT CASE WHEN invoices.payment_status = "paid" THEN invoices.id ELSE NULL END) as paid_invoice_count
        ')
            ->join('orders', 'orders.customer_id = customers.id', 'left')
            ->join('invoices', 'invoices.customer_id = customers.id', 'left')
            ->groupBy('customers.id')
            ->orderBy('customers.id', 'DESC')
            ->findAll();

        // Thêm số dư động cho từng khách hàng
        foreach ($customers as &$customer) {
            $customer['dynamic_balance'] = $this->getCustomerBalance($customer['id']);
            // Đảm bảo paid_invoice_count, invoice_count và order_count không null
            $customer['paid_invoice_count'] = $customer['paid_invoice_count'] ?? 0;
            $customer['invoice_count'] = $customer['invoice_count'] ?? 0;
            $customer['order_count'] = $customer['order_count'] ?? 0;
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
            $balance = $this->db->table('customer_transactions')
                ->selectSum('amount', 'balance')
                ->where('customer_id', $customerId)
                ->get()->getRow()->balance ?? 0;

            // Debug để kiểm tra giá trị
            log_message('debug', "Customer ID: {$customerId}, Calculated Balance: {$balance}");

            cache()->save($cacheKey, $balance, 3600); // Cache trong 1 giờ
        }

        return $balance;
    }
}
