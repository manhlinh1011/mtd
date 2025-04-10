<?php

namespace App\Models;

use CodeIgniter\Model;

class SubCustomerModel extends Model
{
    protected $table = 'sub_customers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id',
        'sub_customer_code',
        'fullname',
        'phone',
        'address',
        'zalo_link',
        'email',
        'created_at'
    ];

    // Nếu có beforeInsert, đảm bảo truy vấn rõ ràng
    protected $beforeInsert = ['checkUnique'];

    public function checkUnique(array $data)
    {
        if (isset($data['data']['sub_customer_code'])) {
            $exists = $this->where('sub_customers.sub_customer_code', $data['data']['sub_customer_code'])->first();
            if ($exists) {
                throw new \Exception('Mã phụ đã tồn tại.');
            }
        }
        return $data;
    }
}
