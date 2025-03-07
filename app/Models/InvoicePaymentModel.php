<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoicePaymentModel extends Model
{
    protected $table = 'invoice_payments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'invoice_id',
        'amount',
        'created_by', // Thay vì created_at, thêm created_by
        'notes'       // Thay note bằng notes (theo bảng)
    ];

    // Tắt tính năng timestamps vì bảng đã có payment_date tự động
    protected $useTimestamps = false;

    // Lấy tất cả thanh toán của một hóa đơn
    public function getPaymentsByInvoice($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)->findAll();
    }

    // Trong phương thức getTotalPaidByInvoice()
    public function getTotalPaidByInvoice($invoiceId)
    {
        return $this->selectSum('amount', 'total_paid')
            ->where('invoice_id', $invoiceId)
            ->first()['total_paid'] ?? 0.00; // Đảm bảo trả về float với giá trị mặc định 0.00
    }
}
