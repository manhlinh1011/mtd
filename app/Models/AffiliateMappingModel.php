<?php

namespace App\Models;

use CodeIgniter\Model;

class AffiliateMappingModel extends Model
{
    protected $table = 'affiliate_mappings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id',
        'sub_customer_id',
        'aff_id'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Lấy mapping theo customer_id
    public function getMappingByCustomerId($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->findAll();
    }

    // Lấy mapping theo sub_customer_id
    public function getMappingBySubCustomerId($subCustomerId)
    {
        return $this->where('sub_customer_id', $subCustomerId)
            ->findAll();
    }

    // Lấy mapping theo aff_id
    public function getMappingByAffId($affId)
    {
        return $this->where('aff_id', $affId)
            ->findAll();
    }

    // Lấy mapping theo customer_id và sub_customer_id
    public function getMappingByCustomerAndSubCustomer($customerId, $subCustomerId)
    {
        return $this->where('customer_id', $customerId)
            ->where('sub_customer_id', $subCustomerId)
            ->first();
    }

    // Kiểm tra xem mapping đã tồn tại chưa
    public function mappingExists($customerId, $subCustomerId)
    {
        return $this->where('customer_id', $customerId)
            ->where('sub_customer_id', $subCustomerId)
            ->countAllResults() > 0;
    }

    // Tạo mapping mới
    public function createMapping($data)
    {
        return $this->insert($data);
    }

    // Cập nhật mapping
    public function updateMapping($id, $data)
    {
        return $this->update($id, $data);
    }

    // Xóa mapping
    public function deleteMapping($id)
    {
        return $this->delete($id);
    }

    // Xóa tất cả mapping của một customer
    public function deleteCustomerMappings($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->delete();
    }

    // Xóa tất cả mapping của một sub_customer
    public function deleteSubCustomerMappings($subCustomerId)
    {
        return $this->where('sub_customer_id', $subCustomerId)
            ->delete();
    }

    // Xóa tất cả mapping của một cộng tác viên
    public function deleteAffiliateMappings($affId)
    {
        return $this->where('aff_id', $affId)
            ->delete();
    }

    // Lấy danh sách khách hàng của một cộng tác viên
    public function getAffiliateCustomers($affId)
    {
        return $this->select('customers.*')
            ->join('customers', 'customers.id = affiliate_mappings.customer_id')
            ->where('affiliate_mappings.aff_id', $affId)
            ->findAll();
    }

    // Lấy danh sách khách hàng phụ của một cộng tác viên
    public function getAffiliateSubCustomers($affId)
    {
        return $this->select('sub_customers.*')
            ->join('sub_customers', 'sub_customers.id = affiliate_mappings.sub_customer_id')
            ->where('affiliate_mappings.aff_id', $affId)
            ->findAll();
    }

    // Lấy danh sách cộng tác viên của một khách hàng
    public function getCustomerAffiliates($customerId)
    {
        return $this->select('users.*')
            ->join('users', 'users.id = affiliate_mappings.aff_id')
            ->where('affiliate_mappings.customer_id', $customerId)
            ->findAll();
    }

    // Lấy danh sách cộng tác viên của một khách hàng phụ
    public function getSubCustomerAffiliates($subCustomerId)
    {
        return $this->select('users.*')
            ->join('users', 'users.id = affiliate_mappings.aff_id')
            ->where('affiliate_mappings.sub_customer_id', $subCustomerId)
            ->findAll();
    }
}
