<?php

namespace App\Controllers;

use App\Models\AffiliatePricingModel;
use App\Models\UserModel;
use App\Models\OrderModel;

class AffiliatePricingController extends BaseController
{
    protected $affiliatePricingModel;
    protected $userModel;
    protected $orderModel;

    public function __construct()
    {
        $this->affiliatePricingModel = new AffiliatePricingModel();
        $this->userModel = new UserModel();
        $this->orderModel = new OrderModel();
    }

    // Hiển thị danh sách bảng giá
    public function index()
    {
        $data['pricings'] = $this->affiliatePricingModel->select('affiliate_pricing.*, users.fullname as aff_name')
            ->join('users', 'users.id = affiliate_pricing.aff_id')
            ->findAll();

        return view('affiliate_pricing/index', $data);
    }

    // Hiển thị form tạo bảng giá mới
    public function create()
    {
        $data['affiliates'] = $this->userModel->findAll();
        $data['orderCodes'] = $this->orderModel->select('order_code')->distinct()->findAll();

        return view('affiliate_pricing/create', $data);
    }

    // Lưu bảng giá mới
    public function store()
    {
        $rules = [
            'aff_id' => 'required|numeric',
            'order_code' => 'required',
            'aff_price_per_kg' => 'required|numeric',
            'aff_price_per_cubic_meter' => 'required|numeric',
            'start_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $endDate = $this->request->getPost('end_date');
        $data = [
            'aff_id' => $this->request->getPost('aff_id'),
            'order_code' => $this->request->getPost('order_code'),
            'aff_price_per_kg' => $this->request->getPost('aff_price_per_kg'),
            'aff_price_per_cubic_meter' => $this->request->getPost('aff_price_per_cubic_meter'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $endDate ? $endDate : null
        ];

        // Kiểm tra trùng lặp theo unique key (aff_id, order_code, start_date)
        if ($this->affiliatePricingModel->pricingExistsUnique($data['aff_id'], $data['order_code'], $data['start_date'])) {
            return redirect()->back()->withInput()->with('error', 'Bảng giá cho cộng tác viên, mã lô và ngày bắt đầu này đã tồn tại!');
        }

        if ($this->affiliatePricingModel->createPricing($data)) {
            return redirect()->to('/affiliate-pricing')->with('success', 'Tạo bảng giá thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo bảng giá');
    }

    // Hiển thị form chỉnh sửa bảng giá
    public function edit($id)
    {
        $data['pricing'] = $this->affiliatePricingModel->find($id);
        if (!$data['pricing']) {
            return redirect()->to('/affiliate-pricing')->with('error', 'Không tìm thấy bảng giá');
        }

        $data['affiliates'] = $this->userModel->findAll();
        $data['orderCodes'] = $this->orderModel->select('order_code')->distinct()->findAll();

        return view('affiliate_pricing/edit', $data);
    }

    // Cập nhật bảng giá
    public function update($id)
    {
        $rules = [
            'aff_id' => 'required|numeric',
            'order_code' => 'required',
            'aff_price_per_kg' => 'required|numeric',
            'aff_price_per_cubic_meter' => 'required|numeric',
            'start_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $endDate = $this->request->getPost('end_date');
        $data = [
            'aff_id' => $this->request->getPost('aff_id'),
            'order_code' => $this->request->getPost('order_code'),
            'aff_price_per_kg' => $this->request->getPost('aff_price_per_kg'),
            'aff_price_per_cubic_meter' => $this->request->getPost('aff_price_per_cubic_meter'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $endDate ? $endDate : null
        ];

        // Kiểm tra trùng lặp khi cập nhật (trừ chính bản ghi hiện tại)
        if ($this->affiliatePricingModel->pricingExistsUnique($data['aff_id'], $data['order_code'], $data['start_date'], $id)) {
            return redirect()->back()->withInput()->with('error', 'Bảng giá cho cộng tác viên, mã lô và ngày bắt đầu này đã tồn tại!');
        }

        if ($this->affiliatePricingModel->updatePricing($id, $data)) {
            return redirect()->to('/affiliate-pricing')->with('success', 'Cập nhật bảng giá thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật bảng giá');
    }

    // Xóa bảng giá
    public function delete($id)
    {
        if ($this->affiliatePricingModel->deletePricing($id)) {
            return redirect()->to('/affiliate-pricing')->with('success', 'Xóa bảng giá thành công');
        }

        return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa bảng giá');
    }

    // Lấy bảng giá của cộng tác viên
    public function getAffiliatePricing($affId)
    {
        $pricing = $this->affiliatePricingModel->getAffiliatePricing($affId);
        return $this->response->setJSON($pricing);
    }

    // Lấy bảng giá theo mã đơn hàng
    public function getPricingByOrderCode($orderCode)
    {
        $pricing = $this->affiliatePricingModel->getPricingByOrderCode($orderCode);
        return $this->response->setJSON($pricing);
    }
}
