<?php

namespace App\Models;

use CodeIgniter\Model;

class ShippingManagerModel extends Model
{
    protected $table = 'shipping_managers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'invoice_id',
        'customer_id',
        'sub_customer_id',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'shipping_provider_id',
        'tracking_number',
        'shipping_fee',
        'status',
        'created_by',
        'confirmed_by',
        'created_at',
        'confirmed_at',
        'notes'

    ];

    // Dates
    protected $useTimestamps = false;
    //protected $dateFormat = 'datetime';
    //protected $createdField = 'created_at';
    //protected $updatedField = null;

    // Validation
    protected $validationRules = [
        'invoice_id' => 'required|numeric',
        'customer_id' => 'required|numeric',
        'sub_customer_id' => 'permit_empty|numeric',
        'receiver_name' => 'permit_empty|max_length[255]',
        'receiver_phone' => 'permit_empty|max_length[15]',
        'receiver_address' => 'permit_empty',
        'shipping_provider_id' => 'permit_empty|numeric',
        'tracking_number' => 'permit_empty|max_length[50]',
        'shipping_fee' => 'permit_empty|numeric',
        'status' => 'required|in_list[pending,delivered]',
        'created_by' => 'required|numeric'
    ];

    protected $validationMessages = [
        'invoice_id' => [
            'required' => 'ID phiếu xuất là bắt buộc',
            'numeric' => 'ID phiếu xuất phải là số'
        ],
        'customer_id' => [
            'required' => 'ID khách hàng là bắt buộc',
            'numeric' => 'ID khách hàng phải là số'
        ],
        'sub_customer_id' => [
            'numeric' => 'ID khách hàng phụ phải là số'
        ],
        'receiver_name' => [
            'max_length' => 'Tên người nhận không được vượt quá 255 ký tự'
        ],
        'receiver_phone' => [
            'max_length' => 'Số điện thoại không được vượt quá 15 ký tự'
        ],
        'shipping_provider_id' => [
            'numeric' => 'ID đơn vị vận chuyển phải là số'
        ],
        'tracking_number' => [
            'max_length' => 'Mã vận đơn không được vượt quá 50 ký tự'
        ],
        'shipping_fee' => [
            'numeric' => 'Phí vận chuyển phải là số'
        ],
        'status' => [
            'required' => 'Trạng thái là bắt buộc',
            'in_list' => 'Trạng thái không hợp lệ'
        ],
        'created_by' => [
            'required' => 'ID người tạo là bắt buộc',
            'numeric' => 'ID người tạo phải là số'
        ]
    ];

    // Relationships
    protected $afterFind = ['loadRelationships'];
    protected $afterInsert = ['loadRelationships'];
    protected $afterUpdate = ['loadRelationships'];

    protected function loadRelationships(array $data)
    {
        if (empty($data['data'])) {
            return $data;
        }

        $shippingProviderModel = new ShippingProviderModel();
        $customerModel = new \App\Models\CustomerModel();
        $subCustomerModel = new \App\Models\SubCustomerModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $userModel = new \App\Models\UserModel();

        if (isset($data['data'][0])) {
            // Multiple records
            foreach ($data['data'] as &$item) {
                if (isset($item['shipping_provider_id'])) {
                    $item['shipping_provider'] = $shippingProviderModel->find($item['shipping_provider_id']);
                }
                if (isset($item['customer_id'])) {
                    $item['customer'] = $customerModel->find($item['customer_id']);
                }
                if (isset($item['sub_customer_id'])) {
                    $item['sub_customer'] = $subCustomerModel->find($item['sub_customer_id']);
                }
                if (isset($item['invoice_id'])) {
                    $item['invoice'] = $invoiceModel->find($item['invoice_id']);
                }
                if (isset($item['created_by'])) {
                    $item['creator'] = $userModel->find($item['created_by']);
                }
                if (isset($item['confirmed_by'])) {
                    $item['confirmer'] = $userModel->find($item['confirmed_by']);
                }
            }
        } else {
            // Single record
            if (isset($data['data']['shipping_provider_id'])) {
                $data['data']['shipping_provider'] = $shippingProviderModel->find($data['data']['shipping_provider_id']);
            }
            if (isset($data['data']['customer_id'])) {
                $data['data']['customer'] = $customerModel->find($data['data']['customer_id']);
            }
            if (isset($data['data']['sub_customer_id'])) {
                $data['data']['sub_customer'] = $subCustomerModel->find($data['data']['sub_customer_id']);
            }
            if (isset($data['data']['invoice_id'])) {
                $data['data']['invoice'] = $invoiceModel->find($data['data']['invoice_id']);
            }
            if (isset($data['data']['created_by'])) {
                $data['data']['creator'] = $userModel->find($data['data']['created_by']);
            }
            if (isset($data['data']['confirmed_by'])) {
                $data['data']['confirmer'] = $userModel->find($data['data']['confirmed_by']);
            }
        }

        return $data;
    }

    public function getShippingDetails($shippingId)
    {
        return $this->select('shipping_managers.*,
                            customers.customer_code,
                            customers.thread_id_zalo as customer_thread_id_zalo,
                            customers.msg_zalo_type as customer_msg_zalo_type,
                            sub_customers.sub_customer_code,
                            sub_customers.thread_id_zalo as sub_customer_thread_id_zalo,
                            sub_customers.msg_zalo_type as sub_customer_msg_zalo_type,
                            shipping_managers.notes,
                            shipping_providers.name as shipping_provider_name,
                            users.fullname as confirmed_by_name')
            ->join('customers', 'customers.id = shipping_managers.customer_id')
            ->join('sub_customers', 'sub_customers.id = shipping_managers.sub_customer_id', 'left')
            ->join('shipping_providers', 'shipping_providers.id = shipping_managers.shipping_provider_id', 'left')
            ->join('users', 'users.id = shipping_managers.confirmed_by', 'left')
            ->where('shipping_managers.id', $shippingId)
            ->first();
    }

    public function getShippingByInvoiceId($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)->first();
    }

    public function getPendingShippings()
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getDeliveredShippings()
    {
        return $this->where('status', 'delivered')
            ->orderBy('confirmed_at', 'DESC')
            ->findAll();
    }

    public function confirmShipping($id, $confirmedBy)
    {
        return $this->update($id, [
            'status' => 'delivered',
            'confirmed_by' => $confirmedBy,
            'confirmed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function searchShippings($keyword)
    {
        $builder = $this->select('shipping_managers.*, 
            customers.customer_code,
            sub_customers.sub_customer_code,
            shipping_providers.name as shipping_provider_name,
            users.fullname as confirmed_by_name')
            ->join('customers', 'customers.id = shipping_managers.customer_id')
            ->join('sub_customers', 'sub_customers.id = shipping_managers.sub_customer_id', 'left')
            ->join('shipping_providers', 'shipping_providers.id = shipping_managers.shipping_provider_id', 'left')
            ->join('users', 'users.id = shipping_managers.confirmed_by', 'left');

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('shipping_managers.receiver_name', $keyword)
                ->orLike('shipping_managers.receiver_phone', $keyword)
                ->orLike('shipping_managers.receiver_address', $keyword)
                ->orLike('shipping_managers.tracking_number', $keyword)
                ->orLike('customers.customer_code', $keyword)
                ->orLike('sub_customers.sub_customer_code', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('shipping_managers.created_at', 'DESC')
            ->paginate(10); // Số lượng item trên mỗi trang
    }
}
