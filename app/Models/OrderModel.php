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
        'sub_customer_id',
        'aff_id',
        'aff_price_per_kg',
        'aff_price_per_cubic_meter',
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
        'created_by',
        'vietnam_stock_date',       // Ngày nhập kho Việt Nam
        'price_per_kg',             // Giá 1 kg
        'price_per_cubic_meter',    // Giá 1 khối
        'exchange_rate',
        'invoice_id',
        'official_quota_fee', // Phí hạn ngạch chính phủ
        'vat_tax', // Thuế VAT
        'import_tax', // Thuế NK
        'other_tax' // Thuế khác
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
                invoices.status as invoice_status,
                invoices.shipping_status,
                CASE 
                    WHEN orders.vietnam_stock_date IS NULL THEN "china_stock"
                    WHEN orders.invoice_id IS NULL THEN "in_stock"
                    WHEN invoices.shipping_status = "confirmed" THEN "shipped"
                    ELSE "pending_shipping"
                END AS order_status
            ')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left')
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

        // Đơn hàng kho Trung Quốc (vietnam_stock_date IS NULL)
        $chinaStock = $db->table('orders')
            ->where('customer_id', $customerId)
            ->where('vietnam_stock_date IS NULL')
            ->countAllResults();

        // Đơn hàng tồn kho (có vietnam_stock_date nhưng invoice_id IS NULL)
        $inStock = $db->table('orders')
            ->where('customer_id', $customerId)
            ->where('vietnam_stock_date IS NOT NULL')
            ->where('invoice_id IS NULL')
            ->countAllResults();

        // Đơn hàng chờ giao (có invoice_id nhưng shipping_status = 'pending')
        $pendingShipping = $db->table('orders o')
            ->join('invoices i', 'i.id = o.invoice_id', 'left')
            ->where('o.customer_id', $customerId)
            ->where('i.shipping_status', 'pending')
            ->countAllResults();

        // Đơn hàng đã giao (có invoice_id và shipping_status = 'confirmed')
        $shipped = $db->table('orders o')
            ->join('invoices i', 'i.id = o.invoice_id', 'left')
            ->where('o.customer_id', $customerId)
            ->where('i.shipping_status', 'confirmed')
            ->countAllResults();

        return [
            'total_orders' => $totalOrders,
            'china_stock' => $chinaStock,
            'in_stock' => $inStock,
            'pending_shipping' => $pendingShipping,
            'shipped' => $shipped,
        ];
    }

    /**
     * Format số volume với 3 chữ số sau dấu phẩy
     */
    public function formatVolume($volume)
    {
        return number_format($volume, 3, '.', '');
    }

    /**
     * Lấy thống kê số bao và số lô theo ngày
     */
    public function getPackageAndBatchStats($date)
    {
        $db = \Config\Database::connect();

        // Đếm số bao duy nhất
        $totalPackages = $db->table('orders')
            ->select('COUNT(DISTINCT package_code) as total_packages')
            ->where('DATE(created_at)', $date)
            ->where('package_code IS NOT NULL')
            ->get()
            ->getRow()
            ->total_packages ?? 0;

        // Đếm số lô duy nhất
        $totalBatches = $db->table('orders')
            ->select('COUNT(DISTINCT order_code) as total_batches')
            ->where('DATE(created_at)', $date)
            ->where('order_code IS NOT NULL')
            ->get()
            ->getRow()
            ->total_batches ?? 0;

        return [
            'total_packages' => $totalPackages,
            'total_batches' => $totalBatches
        ];
    }

    /**
     * Lấy thống kê đơn hàng với các điều kiện lọc
     */
    public function getOrderStatistics($filters = [])
    {
        // Xây dựng điều kiện where từ filters
        $whereConditions = $this->buildWhereConditions($filters);

        return [
            'total' => $this->getTotalOrders($whereConditions),
            'china_stock' => $this->getChinaStockOrders($whereConditions),
            'in_stock' => $this->getInStockOrders($whereConditions),
            'pending_shipping' => $this->getPendingShippingOrders($whereConditions),
            'shipped' => $this->getShippedOrders($whereConditions)
        ];
    }

    /**
     * Xây dựng điều kiện where từ filters
     */
    private function buildWhereConditions($filters)
    {
        $whereConditions = [];

        if (!empty($filters['tracking_code'])) {
            $whereConditions['orders.tracking_code'] = $filters['tracking_code'];
        }

        if (!empty($filters['customer_code']) && $filters['customer_code'] !== 'ALL') {
            $whereConditions['customers.customer_code'] = $filters['customer_code'];
        }

        if (!empty($filters['sub_customer_id']) && $filters['sub_customer_id'] !== 'ALL') {
            if ($filters['sub_customer_id'] === 'NONE') {
                $whereConditions['orders.sub_customer_id'] = null;
            } else {
                $whereConditions['orders.sub_customer_id'] = $filters['sub_customer_id'];
            }
        }

        if (!empty($filters['order_code'])) {
            $whereConditions['orders.order_code'] = $filters['order_code'];
        }

        if (!empty($filters['from_date'])) {
            $whereConditions['orders.created_at >='] = $filters['from_date'] . ' 00:00:00';
        }

        if (!empty($filters['to_date'])) {
            $whereConditions['orders.created_at <='] = $filters['to_date'] . ' 23:59:59';
        }

        if (!empty($filters['vn_from_date'])) {
            $whereConditions['orders.vietnam_stock_date >='] = $filters['vn_from_date'] . ' 00:00:00';
        }
        if (!empty($filters['vn_to_date'])) {
            $whereConditions['orders.vietnam_stock_date <='] = $filters['vn_to_date'] . ' 23:59:59';
        }

        // shipping_status sẽ được xử lý riêng ở từng hàm thống kê nếu cần

        return $whereConditions;
    }

    /**
     * Lấy tổng số đơn hàng
     */
    private function getTotalOrders($whereConditions)
    {
        return $this->select('orders.*')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->where($whereConditions)
            ->countAllResults();
    }

    /**
     * Lấy số đơn hàng ở kho TQ
     */
    private function getChinaStockOrders($whereConditions)
    {
        return $this->select('orders.*')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->where($whereConditions)
            ->where('vietnam_stock_date IS NULL')
            ->countAllResults();
    }

    /**
     * Lấy số đơn hàng tồn kho
     */
    private function getInStockOrders($whereConditions)
    {
        return $this->select('orders.*')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->where($whereConditions)
            ->where('vietnam_stock_date IS NOT NULL')
            ->where('orders.invoice_id IS NULL')
            ->countAllResults();
    }

    /**
     * Lấy số đơn hàng chờ giao
     */
    private function getPendingShippingOrders($whereConditions)
    {
        return $this->select('orders.*, i.shipping_confirmed_at')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->where($whereConditions)
            ->where('orders.invoice_id IS NOT NULL')
            ->where('i.shipping_status', 'pending')
            ->countAllResults();
    }

    /**
     * Lấy số đơn hàng đã giao
     */
    private function getShippedOrders($whereConditions)
    {
        return $this->select('orders.*, i.shipping_confirmed_at')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->where($whereConditions)
            ->where('i.shipping_status', 'confirmed')
            ->countAllResults();
    }
}
