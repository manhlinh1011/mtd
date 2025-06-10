<?php

namespace App\Models;

use CodeIgniter\Model;

class AffiliateCommissionModel extends Model
{
    protected $table = 'affiliate_commissions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'aff_id',
        'order_id',
        'commission_amount',
        'commission_type',
        'payment_status',
        'financial_transaction_id',
        'updated_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    // Các phương thức tùy chỉnh có thể thêm ở đây
}
