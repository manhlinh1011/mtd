<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerTransactionModel extends Model
{
    protected $table = 'customer_transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['customer_id', 'invoice_id', 'fund_id', 'transaction_type', 'amount', 'created_by', 'notes'];

    public function addTransaction($data)
    {
        return $this->insert($data);
    }

    // Cập nhật phương thức để lấy thêm thông tin nhân viên
    public function getCustomerTransactions($customerId)
    {
        return $this->select('customer_transactions.*, users.fullname as employee_name')
            ->join('users', 'users.id = customer_transactions.created_by', 'left')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
