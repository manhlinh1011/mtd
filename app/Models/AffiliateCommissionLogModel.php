<?php

namespace App\Models;

use CodeIgniter\Model;

class AffiliateCommissionLogModel extends Model
{
    protected $table = 'affiliate_commission_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'aff_id',
        'order_id',
        'commission_amount',
        'commission_type',
        'change_reason',
        'changed_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'changed_at';
    protected $updatedField = 'changed_at';
    protected $returnType = 'array';

    // Các phương thức tùy chỉnh có thể thêm ở đây
}
