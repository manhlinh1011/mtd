<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductTypeModel;
use App\Models\OrderModel;
use CodeIgniter\RESTful\ResourceController;

class ApiController extends ResourceController
{
    protected $customerModel;
    protected $productTypeModel;
    protected $orderModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->productTypeModel = new ProductTypeModel();
        $this->orderModel = new OrderModel();
    }

    public function getAllCustomers()
    {
        // Load toàn bộ danh sách khách hàng
        $customers = $this->customerModel->orderBy('customer_code', 'ASC')->findAll();

        // Trả dữ liệu về dạng JSON
        return $this->respond([
            'status' => 200,
            'message' => 'Danh sách khách hàng được tải thành công',
            'data' => $customers
        ], 200);
    }

    /**
     * API: Lấy danh sách phân loại hàng
     */
    public function getAllProductTypes()
    {
        try {
            // Lấy danh sách loại hàng
            $productTypes = $this->productTypeModel->findAll();

            // Trả về JSON
            return $this->respond([
                'status' => 200,
                'message' => 'Danh sách phân loại hàng được tải thành công',
                'data' => $productTypes
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return $this->respond([
                'status' => 500,
                'message' => 'Lỗi trong quá trình tải danh sách phân loại hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * API: Thêm đơn hàng mới
     */
    public function createOrder()
    {
        try {
            // Nhận dữ liệu JSON từ body request
            $data = $this->request->getJSON(true);

            // Kiểm tra dữ liệu có hợp lệ không
            if (!$data) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Dữ liệu gửi lên không hợp lệ',
                    'error' => 'Không tìm thấy dữ liệu trong body request'
                ], 400);
            }

            // Lưu dữ liệu vào bảng orders
            $this->orderModel->insert($data);

            return $this->respond([
                'status' => 200,
                'message' => 'Thêm đơn hàng thành công',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return $this->respond([
                'status' => 500,
                'message' => 'Lỗi trong quá trình thêm đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkTrackingCode()
    {
        $trackingCode = $this->request->getGet('tracking_code');
        if (empty($trackingCode)) {
            return $this->response->setStatusCode(400)->setBody('0');
        }
        $exists = $this->orderModel->where('tracking_code', $trackingCode)->first();
        if ($exists) {
            return $this->response->setStatusCode(200)->setBody('1');
        }
        return $this->response->setStatusCode(200)->setBody('0');
    }
}
