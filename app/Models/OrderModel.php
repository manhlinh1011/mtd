<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tracking_code',
        'customer_id',
        'product_type_id',
        'package_index',
        'quantity',
        'total_weight',
        'domestic_fee',
        'order_code',
        'length',
        'width',
        'height',
        'volume',
        'notes',
        'export_date',
        'shipping_fee',
        'created_at',
        'vietnam_stock_date',       // Ngày nhập kho Việt Nam
        'price_per_kg',             // Giá 1 kg
        'price_per_cubic_meter',    // Giá 1 khối
        'exchange_rate'             // Tỷ giá tệ
    ];
}
