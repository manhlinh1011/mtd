<?php

namespace App\Models;

use CodeIgniter\Model;

class FinancialTransactionModel extends Model
{
    protected $table = 'financial_transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'fund_id',
        'type',
        'amount',
        'description',
        'status',
        'created_by',
        'approved_by',
        'created_at',
        'approved_at'
    ];
}
