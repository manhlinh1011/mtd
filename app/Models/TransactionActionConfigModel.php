<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionActionConfigModel extends Model
{
    protected $table = 'transaction_action_config';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'action_code',
        'transaction_type_id',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'action_code' => 'required|max_length[50]|is_unique[transaction_action_config.action_code,id,{id}]',
        'transaction_type_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'action_code' => [
            'required' => 'Mã action là bắt buộc',
            'max_length' => 'Mã action không được vượt quá 50 ký tự',
            'is_unique' => 'Mã action đã tồn tại'
        ],
        'transaction_type_id' => [
            'required' => 'Loại giao dịch là bắt buộc',
            'integer' => 'Loại giao dịch phải là số nguyên'
        ]
    ];

    /**
     * Lấy transaction_type_id theo action_code
     */
    public function getTypeIdByAction($actionCode)
    {
        $row = $this->where('action_code', $actionCode)->first();
        return $row ? $row['transaction_type_id'] : null;
    }
}
