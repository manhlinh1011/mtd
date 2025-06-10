<?php

namespace App\Models;

use CodeIgniter\Model;

class ShippingProviderModel extends Model
{
    protected $table = 'shipping_providers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'description' => 'permit_empty|max_length[1000]'
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Tên đơn vị vận chuyển là bắt buộc',
            'min_length' => 'Tên đơn vị vận chuyển phải có ít nhất 3 ký tự',
            'max_length' => 'Tên đơn vị vận chuyển không được vượt quá 100 ký tự'
        ],
        'description' => [
            'max_length' => 'Mô tả không được vượt quá 1000 ký tự'
        ]
    ];

    public function getAllProviders()
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    public function getProviderById($id)
    {
        return $this->find($id);
    }

    public function searchProviders($keyword)
    {
        return $this->like('name', $keyword)
            ->orLike('description', $keyword)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
