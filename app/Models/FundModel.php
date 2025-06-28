<?php

namespace App\Models;

use CodeIgniter\Model;

class FundModel extends Model
{
    protected $table = 'funds';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'account_number', 'bank_name', 'account_holder', 'payment_qr'];

    public function getFunds()
    {
        return $this->findAll();
    }

    public function getBalance($fundId)
    {
        // Tính tổng chi từ financial_transactions
        $expense = $this->db->table('financial_transactions')
            ->where('fund_id', $fundId)
            ->where('type', 'expense')
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount ?? 0;

        // Tính tổng thu từ financial_transactions
        $income = $this->db->table('financial_transactions')
            ->where('fund_id', $fundId)
            ->where('type', 'income')
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount ?? 0;

        // Tính tổng nạp tiền từ customer_transactions
        $deposit = $this->db->table('customer_transactions')
            ->where('fund_id', $fundId)
            ->where('transaction_type', 'deposit')
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount ?? 0;

        // Số dư = Thu + Nạp tiền - Chi - Thanh toán
        return ($income + $deposit) - $expense;
    }

    public function getTotalIncome($fundId)
    {
        // Tính tổng thu từ financial_transactions
        $income = $this->db->table('financial_transactions')
            ->where('fund_id', $fundId)
            ->where('type', 'income')
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount ?? 0;

        // Tính tổng nạp tiền từ customer_transactions
        $deposit = $this->db->table('customer_transactions')
            ->where('fund_id', $fundId)
            ->where('transaction_type', 'deposit')
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount ?? 0;

        return $deposit + $income;
    }

    public function getTotalExpense($fundId)
    {
        return $this->db->table('financial_transactions')
            ->where('fund_id', $fundId)
            ->where('type', 'expense')
            ->selectSum('amount')
            ->get()
            ->getRow()
            ->amount ?? 0;
    }
}
