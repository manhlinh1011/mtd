<?php

namespace App\Controllers;

use App\Models\AffiliateCommissionModel;
use App\Models\AffiliateCommissionLogModel;
use App\Models\UserModel;
use App\Models\OrderModel;

class AffiliateCommissionController extends BaseController
{
    protected $affiliateCommissionModel;
    protected $affiliateCommissionLogModel;
    protected $userModel;
    protected $orderModel;

    public function __construct()
    {
        $this->affiliateCommissionModel = new AffiliateCommissionModel();
        $this->affiliateCommissionLogModel = new AffiliateCommissionLogModel();
        $this->userModel = new UserModel();
        $this->orderModel = new OrderModel();
    }

    // Hiển thị danh sách hoa hồng
    public function index()
    {
        $data['commissions'] = $this->affiliateCommissionModel
            ->select('affiliate_commissions.*, users.fullname as aff_name, orders.order_code')
            ->join('users', 'users.id = affiliate_commissions.aff_id')
            ->join('orders', 'orders.id = affiliate_commissions.order_id')
            ->findAll();

        return view('affiliate_commission/index', $data);
    }

    // Hiển thị form tạo hoa hồng mới
    public function create()
    {
        $data['affiliates'] = $this->userModel->findAll();
        $data['orders'] = $this->orderModel->findAll();
        return view('affiliate_commission/create', $data);
    }

    // Lưu hoa hồng mới
    public function store()
    {
        $rules = [
            'aff_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'commission_amount' => 'required|numeric',
            'commission_type' => 'required|in_list[weight,volume,other]',
            'payment_status' => 'required|in_list[pending,approved,paid,cancelled]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'aff_id' => $this->request->getPost('aff_id'),
            'order_id' => $this->request->getPost('order_id'),
            'commission_amount' => $this->request->getPost('commission_amount'),
            'commission_type' => $this->request->getPost('commission_type'),
            'payment_status' => $this->request->getPost('payment_status'),
            'updated_by' => session()->get('user_id')
        ];

        if ($this->affiliateCommissionModel->insert($data)) {
            // Ghi log
            $this->affiliateCommissionLogModel->insert([
                'aff_id' => $data['aff_id'],
                'order_id' => $data['order_id'],
                'commission_amount' => $data['commission_amount'],
                'commission_type' => $data['commission_type'],
                'change_reason' => 'Tạo mới hoa hồng',
                'changed_by' => session()->get('user_id')
            ]);

            return redirect()->to('/affiliate-commission')->with('success', 'Tạo hoa hồng thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi tạo hoa hồng');
    }

    // Hiển thị form chỉnh sửa hoa hồng
    public function edit($id)
    {
        $data['commission'] = $this->affiliateCommissionModel->find($id);
        if (!$data['commission']) {
            return redirect()->to('/affiliate-commission')->with('error', 'Không tìm thấy hoa hồng');
        }

        $data['affiliates'] = $this->userModel->findAll();
        $data['orders'] = $this->orderModel->findAll();
        return view('affiliate_commission/edit', $data);
    }

    // Cập nhật hoa hồng
    public function update($id)
    {
        $rules = [
            'commission_amount' => 'required|numeric',
            'commission_type' => 'required|in_list[weight,volume,other]',
            'payment_status' => 'required|in_list[pending,approved,paid,cancelled]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $oldCommission = $this->affiliateCommissionModel->find($id);
        if (!$oldCommission) {
            return redirect()->to('/affiliate-commission')->with('error', 'Không tìm thấy hoa hồng');
        }

        $data = [
            'commission_amount' => $this->request->getPost('commission_amount'),
            'commission_type' => $this->request->getPost('commission_type'),
            'payment_status' => $this->request->getPost('payment_status'),
            'updated_by' => session()->get('user_id')
        ];

        if ($this->affiliateCommissionModel->update($id, $data)) {
            // Ghi log nếu có thay đổi
            if (
                $oldCommission['commission_amount'] != $data['commission_amount'] ||
                $oldCommission['commission_type'] != $data['commission_type'] ||
                $oldCommission['payment_status'] != $data['payment_status']
            ) {

                $this->affiliateCommissionLogModel->insert([
                    'aff_id' => $oldCommission['aff_id'],
                    'order_id' => $oldCommission['order_id'],
                    'commission_amount' => $data['commission_amount'],
                    'commission_type' => $data['commission_type'],
                    'change_reason' => 'Cập nhật hoa hồng',
                    'changed_by' => session()->get('user_id')
                ]);
            }

            return redirect()->to('/affiliate-commission')->with('success', 'Cập nhật hoa hồng thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật hoa hồng');
    }

    // Xem lịch sử thay đổi hoa hồng
    public function logs($id)
    {
        $data['commission'] = $this->affiliateCommissionModel
            ->select('affiliate_commissions.*, users.fullname as aff_name, orders.order_code')
            ->join('users', 'users.id = affiliate_commissions.aff_id')
            ->join('orders', 'orders.id = affiliate_commissions.order_id')
            ->find($id);

        if (!$data['commission']) {
            return redirect()->to('/affiliate-commission')->with('error', 'Không tìm thấy hoa hồng');
        }

        $data['logs'] = $this->affiliateCommissionLogModel
            ->select('affiliate_commission_logs.*, users.fullname as changed_by_name')
            ->join('users', 'users.id = affiliate_commission_logs.changed_by')
            ->where('affiliate_commission_logs.aff_id', $data['commission']['aff_id'])
            ->where('affiliate_commission_logs.order_id', $data['commission']['order_id'])
            ->orderBy('affiliate_commission_logs.changed_at', 'DESC')
            ->findAll();

        return view('affiliate_commission/logs', $data);
    }
}
