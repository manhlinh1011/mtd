<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'customer_id',
        'created_by',
        'shipping_fee',
        'other_fee',
        'payment_status',
        'shipping_status',
        'notes',
        'created_at',
        'shipping_confirmed_by',
        'shipping_confirmed_at',
        'sub_customer_id'
    ];

    /**
     * Lấy thông tin chi tiết của một invoice
     */
    public function getInvoiceDetails($invoiceId)
    {
        return $this->select('invoices.*, customers.fullname AS customer_name, users.username AS creator_name')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->join('users', 'users.id = invoices.created_by', 'left') // 'left' để tránh lỗi nếu created_by là NULL
            ->where('invoices.id', $invoiceId)
            ->first();
    }

    // Cập nhật phương thức để tính toán động total_amount
    public function getRecentInvoicesByCustomer($customerId, $limit = 10)
    {
        $invoices = $this->select('
                invoices.*, 
                users.fullname as created_by_name,
                COUNT(DISTINCT o.id) as total_orders,
                SUM(o.total_weight) as total_weight,
                SUM(o.volume) as total_volume
            ')
            ->join('users', 'users.id = invoices.created_by', 'left')
            ->join('orders o', 'o.invoice_id = invoices.id', 'left')
            ->where('invoices.customer_id', $customerId)
            ->groupBy('invoices.id')
            ->orderBy('invoices.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

        // Tính toán dynamic_total cho mỗi invoice
        foreach ($invoices as &$invoice) {
            // Lấy tất cả đơn hàng của phiếu xuất
            $orders = $this->db->table('orders')
                ->where('invoice_id', $invoice['id'])
                ->get()
                ->getResultArray();

            $total = 0;
            foreach ($orders as $order) {
                $totalWeight = floatval($order['total_weight'] ?? 0);
                $pricePerKg = floatval($order['price_per_kg'] ?? 0);
                $volume = floatval($order['volume'] ?? 0);
                $pricePerCubicMeter = floatval($order['price_per_cubic_meter'] ?? 0);
                $domesticFee = floatval($order['domestic_fee'] ?? 0);
                $exchangeRate = floatval($order['exchange_rate'] ?? 0);
                $officialQuotaFee = floatval($order['official_quota_fee'] ?? 0);
                $vatTax = floatval($order['vat_tax'] ?? 0);
                $importTax = floatval($order['import_tax'] ?? 0);
                $otherTax = floatval($order['other_tax'] ?? 0);

                $priceByWeight = $totalWeight * $pricePerKg;
                $priceByVolume = $volume * $pricePerCubicMeter;
                $finalPrice = max($priceByWeight, $priceByVolume);
                $total += $finalPrice +
                    ($domesticFee * $exchangeRate) +
                    $officialQuotaFee +
                    $vatTax +
                    $importTax +
                    $otherTax;
            }

            // Cộng thêm phí giao hàng và phí khác
            $invoice['total_amount'] = $total + floatval($invoice['shipping_fee'] ?? 0) + floatval($invoice['other_fee'] ?? 0);
        }

        return $invoices;
    }

    // Cập nhật phương thức để sửa lỗi first()
    public function calculateDynamicTotal($invoiceId)
    {
        $builder = $this->builder('invoices')
            ->select('invoices.shipping_fee, invoices.other_fee')
            ->where('invoices.id', $invoiceId)
            ->join('orders', 'orders.invoice_id = invoices.id', 'left');

        // Sử dụng get()->getRow() thay vì first()
        $invoice = $builder->get()->getRowArray();
        if (!$invoice) {
            return 0;
        }

        // Tính tiền vận chuyển từ các đơn hàng liên quan
        $orderBuilder = $this->db->table('orders')
            ->select('total_weight, volume, price_per_kg, price_per_cubic_meter, domestic_fee, exchange_rate, official_quota_fee, vat_tax, import_tax, other_tax')
            ->where('invoice_id', $invoiceId);

        $orders = $orderBuilder->get()->getResultArray();
        $transportTotal = 0;

        foreach ($orders as $order) {
            $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
            $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
            $finalPrice = max($priceByWeight, $priceByVolume) +
                ($order['domestic_fee'] * $order['exchange_rate']) +
                $order['official_quota_fee'] +
                $order['vat_tax'] +
                $order['import_tax'] +
                $order['other_tax'];
            $transportTotal += $finalPrice;
        }

        // Tính tổng cộng (tiền vận chuyển + phí giao hàng + phí khác)
        return $transportTotal + $invoice['shipping_fee'] + $invoice['other_fee'];
    }

    public function getInvoiceStatsByCustomer($customerId)
    {
        $db = \Config\Database::connect();

        // Đếm tổng số phiếu xuất
        $totalInvoices = $db->table('invoices')
            ->where('customer_id', $customerId)
            ->countAllResults();

        // Đếm số phiếu xuất đã thanh toán
        $paidInvoices = $db->table('invoices')
            ->where('customer_id', $customerId)
            ->where('payment_status', 'paid')
            ->countAllResults();

        // Đếm số phiếu xuất chưa thanh toán
        $unpaidInvoices = $db->table('invoices')
            ->where('customer_id', $customerId)
            ->where('payment_status', 'unpaid')
            ->countAllResults();

        // Đếm số phiếu xuất đã giao
        $deliveredInvoices = $db->table('invoices')
            ->where('customer_id', $customerId)
            ->where('shipping_status', 'confirmed')
            ->countAllResults();

        // Đếm số phiếu xuất chưa giao
        $pendingInvoices = $db->table('invoices')
            ->where('customer_id', $customerId)
            ->where('shipping_status', 'pending')
            ->countAllResults();

        return [
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'unpaid_invoices' => $unpaidInvoices,
            'delivered_invoices' => $deliveredInvoices,
            'pending_invoices' => $pendingInvoices
        ];
    }
}
