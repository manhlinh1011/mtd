<?php

namespace App\Models;

use CodeIgniter\Model;

class AffiliatePricingModel extends Model
{
    protected $table = 'affiliate_pricing';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'aff_id',
        'order_code',
        'aff_price_per_kg',
        'aff_price_per_cubic_meter',
        'start_date',
        'end_date'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function pricingExists($affId, $orderCode, $startDate, $endDate, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count
            FROM affiliate_pricing
            WHERE aff_id = ?
              AND order_code = ?
              AND start_date <= ?
              AND (end_date >= ? OR end_date IS NULL)";
        $params = [$affId, $orderCode, $endDate, $startDate];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->query($sql, $params)->getRowArray();
        return $result['count'] > 0;
    }

    public function pricingExistsUnique($affId, $orderCode, $startDate, $excludeId = null)
    {
        $builder = $this->builder();
        $builder->where('aff_id', $affId);
        $builder->where('order_code', $orderCode);
        $builder->where('start_date', $startDate);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    // Lấy bảng giá đang áp dụng tại 1 thời điểm
    public function getActivePricing($affId, $orderCode, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        return $this->where('aff_id', $affId)
            ->where('order_code', $orderCode)
            ->where('start_date <=', $date)
            ->groupStart()
            ->where('end_date >=', $date)
            ->orWhere('end_date IS NULL')
            ->groupEnd()
            ->first();
    }

    // Lấy lịch sử bảng giá theo aff_id và order_code
    public function getPricingHistory($affId, $orderCode)
    {
        return $this->where('aff_id', $affId)
            ->where('order_code', $orderCode)
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }

    // Tạo bảng giá mới
    public function createPricing($data)
    {
        return $this->insert($data);
    }

    // Cập nhật bảng giá
    public function updatePricing($id, $data)
    {
        return $this->update($id, $data);
    }

    // Xóa bảng giá
    public function deletePricing($id)
    {
        return $this->delete($id);
    }
}
