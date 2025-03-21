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
        'status',
        'shipping_status',
        'payment_status',
        'notes',
        'created_at',
        'shipping_confirmed_by',
        'shipping_confirmed_at'
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
        $invoices = $this->select('invoices.*, 
                            invoices.id, 
                            invoices.created_at, 
                            invoices.shipping_fee, 
                            invoices.other_fee, 
                            invoices.status, 
                            invoices.shipping_status, 
                            invoices.payment_status')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
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
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume);
                $domesticFee = $order['domestic_fee'] * $order['exchange_rate'];
                $total += $finalPrice + $domesticFee;
            }

            // Cộng thêm phí giao hàng và phí khác
            $invoice['dynamic_total'] = $total + (float)$invoice['shipping_fee'] + (float)$invoice['other_fee'];
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
            ->select('total_weight, volume, price_per_kg, price_per_cubic_meter, domestic_fee, exchange_rate')
            ->where('invoice_id', $invoiceId);

        $orders = $orderBuilder->get()->getResultArray();
        $transportTotal = 0;

        foreach ($orders as $order) {
            $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
            $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
            $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
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
