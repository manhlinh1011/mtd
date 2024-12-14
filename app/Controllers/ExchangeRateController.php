<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ExchangeRateModel;

class ExchangeRateController extends BaseController
{
    protected $exchangeRateModel;

    public function __construct()
    {
        $this->exchangeRateModel = new ExchangeRateModel();
    }

    // Hiển thị danh sách tỷ giá
    public function index()
    {
        $data['rates'] = $this->exchangeRateModel->getAllRates();
        return view('exchange_rates/index', $data);
    }

    // Hiển thị form cập nhật tỷ giá
    public function updateForm()
    {
        $data['latestRate'] = $this->exchangeRateModel->getLatestRate();
        return view('exchange_rates/update', $data);
    }

    // Xử lý cập nhật tỷ giá
    public function update()
    {
        $rate = $this->request->getPost('rate');

        // Kiểm tra giá trị hợp lệ
        if (!is_numeric($rate) || $rate <= 0) {
            return redirect()->back()->with('error', 'Tỷ giá phải là một số dương.');
        }

        // Thêm tỷ giá mới
        $this->exchangeRateModel->insert([
            'rate' => $rate,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Chuyển hướng về form cập nhật
        return redirect()->to('/exchange-rates/update-form')->with('success', 'Cập nhật tỷ giá thành công.');
    }

    // API: Lấy tỷ giá mới nhất
    public function getLatestRate()
    {
        $latestRate = $this->exchangeRateModel->getLatestRate();

        if (!$latestRate) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Không tìm thấy tỷ giá nào.',
                'data' => null
            ]);
        }

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Lấy tỷ giá mới nhất thành công.',
            'data' => [
                'rate' => (float) $latestRate['rate'],
                'updated_at' => $latestRate['updated_at']
            ]
        ]);
    }
}
