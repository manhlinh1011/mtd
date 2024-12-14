<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'fullname',
        'phone',
        'address',
        'zalo_link',
        'email',
        'customer_code',
        'price_per_kg',       // Thêm trường này
        'price_per_cubic_meter', // Thêm trường này
        'created_at'
    ];
}
