<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\TransactionActionConfigModel;

class CustomerTransactionModel extends Model
{
    protected $table = 'customer_transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['customer_id', 'invoice_id', 'fund_id', 'transaction_type', 'transaction_type_id', 'amount', 'created_by', 'notes', 'created_at', 'transaction_date'];

    public function addTransaction($data)
    {
        // Nếu chưa có transaction_type_id, tự động lấy theo action_code
        if (empty($data['transaction_type_id']) && !empty($data['transaction_type'])) {
            $configModel = new TransactionActionConfigModel();
            $typeId = $configModel->getTypeIdByAction($data['transaction_type']);
            if ($typeId) {
                $data['transaction_type_id'] = $typeId;
            }
        }
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
