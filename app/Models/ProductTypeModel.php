<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductTypeModel extends Model
{
    protected $table = 'product_types';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description'];

    /**
     * Lấy danh sách loại hàng được đặt hàng và số lượng đơn đặt
     * @return array
     */
    public function getProductTypeStatistics()
    {
        return $this->select('product_types.name, COUNT(orders.id) as total_orders')
            ->join('orders', 'orders.product_type_id = product_types.id', 'inner')
            ->groupBy('product_types.id')
            ->orderBy('total_orders', 'DESC')
            ->findAll();
    }

    public function getProductTypeStatisticsFull()
    {
        return $this->select('product_types.name, product_types.description, product_types.id, COUNT(orders.id) as total_orders')
            ->join('orders', 'orders.product_type_id = product_types.id', 'left')
            ->groupBy('product_types.id')
            ->orderBy('total_orders', 'DESC')
            ->findAll();
    }
}
