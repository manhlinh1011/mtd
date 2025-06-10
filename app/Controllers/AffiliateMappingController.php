<?php

namespace App\Controllers;

use App\Models\AffiliateMappingModel;
use App\Models\UserModel;
use App\Models\CustomerModel;
use App\Models\SubCustomerModel;

class AffiliateMappingController extends BaseController
{
    protected $affiliateMappingModel;
    protected $userModel;
    protected $customerModel;
    protected $subCustomerModel;

    public function __construct()
    {
        $this->affiliateMappingModel = new AffiliateMappingModel();
        $this->userModel = new UserModel();
        $this->customerModel = new CustomerModel();
        $this->subCustomerModel = new SubCustomerModel();
    }

    // Hiển thị danh sách mapping
    public function index()
    {
        $data['mappings'] = $this->affiliateMappingModel
            ->select('affiliate_mappings.*, users.fullname as aff_name, customers.fullname as customer_name, customers.customer_code, sub_customers.fullname as sub_customer_name, sub_customers.sub_customer_code as sub_customer_code')
            ->join('users', 'users.id = affiliate_mappings.aff_id')
            ->join('customers', 'customers.id = affiliate_mappings.customer_id')
            ->join('sub_customers as sub_customers', 'sub_customers.id = affiliate_mappings.sub_customer_id', 'left')
            ->findAll();

        return view('affiliate_mapping/index', $data);
    }

    // Hiển thị form tạo mapping mới 
    public function create()
    {
        $data['affiliates'] = $this->userModel->findAll();
        $data['customers'] = $this->customerModel->findAll();
        $data['subCustomers'] = $this->subCustomerModel->findAll();

        return view('affiliate_mapping/create', $data);
    }

    // Lưu mapping mới
    public function store()
    {
        $rules = [
            'customer_id' => 'required|numeric',
            'aff_id' => 'required|numeric',
            'sub_customer_id' => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'customer_id' => $this->request->getPost('customer_id'),
            'aff_id' => $this->request->getPost('aff_id'),
            'sub_customer_id' => $this->request->getPost('sub_customer_id') ?: null
        ];

        // Kiểm tra mapping đã tồn tại
        if ($this->affiliateMappingModel->mappingExists($data['customer_id'], $data['sub_customer_id'])) {
            return redirect()->back()->withInput()->with('error', 'Mapping này đã tồn tại');
        }

        if ($this->affiliateMappingModel->createMapping($data)) {
            return redirect()->to('/affiliate-mapping')->with('success', 'Tạo mapping thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo mapping');
    }

    // Hiển thị form chỉnh sửa mapping
    public function edit($id)
    {
        $data['mapping'] = $this->affiliateMappingModel->find($id);
        if (!$data['mapping']) {
            return redirect()->to('/affiliate-mapping')->with('error', 'Không tìm thấy mapping');
        }

        $data['affiliates'] = $this->userModel->findAll();
        $data['customers'] = $this->customerModel->findAll();
        $data['subCustomers'] = $this->subCustomerModel->findAll();

        return view('affiliate_mapping/edit', $data);
    }

    // Cập nhật mapping
    public function update($id)
    {
        $rules = [
            'customer_id' => 'required|numeric',
            'aff_id' => 'required|numeric',
            'sub_customer_id' => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'customer_id' => $this->request->getPost('customer_id'),
            'aff_id' => $this->request->getPost('aff_id'),
            'sub_customer_id' => $this->request->getPost('sub_customer_id') ?: null
        ];

        // Kiểm tra mapping đã tồn tại (trừ mapping hiện tại)
        $existingMapping = $this->affiliateMappingModel->getMappingByCustomerAndSubCustomer($data['customer_id'], $data['sub_customer_id']);
        if ($existingMapping && $existingMapping['id'] != $id) {
            return redirect()->back()->withInput()->with('error', 'Mapping này đã tồn tại');
        }

        if ($this->affiliateMappingModel->updateMapping($id, $data)) {
            return redirect()->to('/affiliate-mapping')->with('success', 'Cập nhật mapping thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật mapping');
    }

    // Xóa mapping
    public function delete($id)
    {
        if ($this->affiliateMappingModel->deleteMapping($id)) {
            return redirect()->to('/affiliate-mapping')->with('success', 'Xóa mapping thành công');
        }

        return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa mapping');
    }

    // Lấy danh sách khách hàng của cộng tác viên
    public function getAffiliateCustomers($affId)
    {
        $customers = $this->affiliateMappingModel->getAffiliateCustomers($affId);
        return $this->response->setJSON($customers);
    }

    // Lấy danh sách khách hàng phụ của cộng tác viên
    public function getAffiliateSubCustomers($affId)
    {
        $subCustomers = $this->affiliateMappingModel->getAffiliateSubCustomers($affId);
        return $this->response->setJSON($subCustomers);
    }

    // Lấy danh sách cộng tác viên của khách hàng
    public function getCustomerAffiliates($customerId)
    {
        $affiliates = $this->affiliateMappingModel->getCustomerAffiliates($customerId);
        return $this->response->setJSON($affiliates);
    }

    // Lấy danh sách cộng tác viên của khách hàng phụ
    public function getSubCustomerAffiliates($subCustomerId)
    {
        $affiliates = $this->affiliateMappingModel->getSubCustomerAffiliates($subCustomerId);
        return $this->response->setJSON($affiliates);
    }

    // Lấy danh sách khách hàng phụ của một khách hàng
    public function getCustomerSubCustomers($customerId)
    {
        $subCustomers = $this->subCustomerModel->where('customer_id', $customerId)->findAll();
        return $this->response->setJSON($subCustomers);
    }
}
