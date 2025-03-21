<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemLogModel extends Model
{
    protected $table = 'system_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'entity_type',
        'entity_id',
        'action_type',
        'created_by',
        'details',
        'notes'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function addLog(array $data)
    {
        return $this->insert($data);
    }
}
