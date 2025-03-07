<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tracking_code',
        'customer_id',
        'product_type_id',
        'package_index',
        'package_code',
        'quantity',
        'total_weight',
        'domestic_fee',
        'order_code',
        'length',
        'width',
        'height',
        'volume',
        'notes',
        'export_date',
        'shipping_fee',
        'created_at',
        'vietnam_stock_date',       // Ngày nhập kho Việt Nam
        'price_per_kg',             // Giá 1 kg
        'price_per_cubic_meter',    // Giá 1 khối
        'exchange_rate',
        'invoice_id'
    ];

    /**
     * Lấy số lượng đơn đặt hàng trong 30 ngày gần nhất
     */
    public function getOrdersInLast30Days()
    {
        return $this->select("DATE(created_at) as order_date, COUNT(id) as total_orders")
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('order_date')
            ->orderBy('order_date', 'ASC')
            ->findAll();
    }

    public function getOrdersWithStatus()
    {
        return $this->select('
                orders.*, 
                invoices.status as invoice_status
            ')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left') // Liên kết bảng invoices qua cột invoice_id
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }


    public function getOrderStatsByCustomer($customerId)
    {
        $db = \Config\Database::connect();

        // Đếm tổng số đơn hàng
        $totalOrders = $db->table('orders')
            ->where('customer_id', $customerId)
            ->countAllResults();

        // Đếm số đơn tồn kho (invoice_id IS NULL)
        $inStock = $db->table('orders')
            ->where('customer_id', $customerId)
            ->where('invoice_id IS NULL') // Chỉ lấy đơn chưa có trong invoices
            ->countAllResults();

        // Đếm số đơn đang xuất (có invoice nhưng shipping_status = 'pending')
        $shipping = $db->table('orders o')
            ->join('invoices i', 'i.id = o.invoice_id', 'left')
            ->where('o.customer_id', $customerId)
            ->where('i.shipping_status', 'pending')
            ->countAllResults();

        // Đếm số đơn đã xuất (shipping_status = 'confirmed')
        $shipped = $db->table('orders o')
            ->join('invoices i', 'i.id = o.invoice_id', 'left')
            ->where('o.customer_id', $customerId)
            ->where('i.shipping_status', 'confirmed')
            ->countAllResults();

        return [
            'total_orders' => $totalOrders,
            'in_stock' => $inStock,
            'shipping' => $shipping,
            'shipped' => $shipped,
        ];
    }
}
