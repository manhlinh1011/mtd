<?php

namespace App\Models;

use CodeIgniter\Model;

class ExchangeRateModel extends Model
{
    protected $table = 'exchange_rates';
    protected $primaryKey = 'id';
    protected $allowedFields = ['rate', 'updated_at'];

    // Lấy danh sách tỷ giá, sắp xếp theo ID giảm dần
    public function getAllRates()
    {
        return $this->orderBy('id', 'DESC')->findAll();
    }

    // Thêm tỷ giá mới
    public function addRate($rate)
    {
        return $this->insert([
            'rate' => $rate,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // Lấy tỷ giá mới nhất
    public function getLatestRate()
    {
        return $this->orderBy('id', 'DESC')->first();
    }
}
