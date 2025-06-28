<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderInspectionModel extends Model
{
    protected $table = 'order_inspections';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tracking_code',
        'notes',
        'notify_checked',
        'created_at'
    ];


    /**
     * Lấy 1 đơn hàng cần kiểm tra chưa thông báo (đơn cũ nhất)
     * Chỉ lấy những đơn đã có trong bảng orders (đã về kho)
     */
    public function getPendingNotifications()
    {
        return $this->select('order_inspections.*')
            ->join('orders', 'orders.tracking_code = order_inspections.tracking_code', 'inner')
            ->where('order_inspections.notify_checked', 0)
            ->orderBy('order_inspections.created_at', 'ASC')
            ->limit(1)
            ->findAll();
    }

    /**
     * Đánh dấu đã thông báo
     */
    public function markAsNotified($id)
    {
        return $this->update($id, ['notify_checked' => 1]);
    }

    /**
     * Đánh dấu tất cả đã thông báo
     */
    public function markAllAsNotified()
    {
        return $this->where('notify_checked', 0)
            ->set(['notify_checked' => 1])
            ->update();
    }

    /**
     * Kiểm tra xem tracking code đã có trong danh sách kiểm tra chưa
     */
    public function isTrackingCodeExists($trackingCode)
    {
        return $this->where('tracking_code', $trackingCode)
            ->where('notify_checked', 0)
            ->countAllResults() > 0;
    }

    /**
     * Kiểm tra xem tracking code có tồn tại trong bảng orders không
     */
    public function isTrackingCodeInOrders($trackingCode)
    {
        return $this->db->table('orders')
            ->where('tracking_code', $trackingCode)
            ->countAllResults() > 0;
    }
}
