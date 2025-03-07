<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceOrderModel extends Model
{
    protected $table = 'invoice_orders';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'invoice_id',
        'order_id'
    ];

    public function getOrdersByInvoice($invoiceId)
    {
        return $this->select('invoice_orders.*, orders.*')
            ->join('orders', 'orders.id = invoice_orders.order_id')
            ->where('invoice_orders.invoice_id', $invoiceId)
            ->findAll();
    }

    /**
     * Thêm đơn hàng vào phiếu xuất
     */
    public function addOrderToInvoice($invoiceId, $orderId)
    {
        return $this->insert([
            'invoice_id' => $invoiceId,
            'order_id'   => $orderId
        ]);
    }

    /**
     * Xóa tất cả đơn hàng khỏi phiếu
     */
    public function deleteOrdersByInvoice($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)->delete();
    }
}
