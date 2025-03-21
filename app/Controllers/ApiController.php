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

            // Lấy thông tin khách hàng để lấy giá mặc định
            $customer = $this->customerModel->find($data['customer_id']);
            if (!$customer) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Không tìm thấy thông tin khách hàng',
                    'error' => 'Customer ID không hợp lệ'
                ], 404);
            }

            // Bổ sung thông tin giá mặc định từ khách hàng vào đơn hàng
            $data['price_per_kg'] = $customer['price_per_kg'];
            $data['price_per_cubic_meter'] = $customer['price_per_cubic_meter'];

            //$data['price_per_kg'] = 18000;
            //$data['price_per_cubic_meter'] = 2500000;


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

    /**
     * API: Kiểm tra xem đơn hàng đã về Việt Nam hay chưa
     */
    public function checkVietnamStockStatus()
    {
        $trackingCode = $this->request->getGet('tracking_code');

        if (empty($trackingCode)) {
            return $this->response->setStatusCode(400)->setBody('0');
        }

        $order = $this->orderModel->where('tracking_code', $trackingCode)->first();

        if (!$order) {
            return $this->response->setStatusCode(404)->setBody('0');
        }

        // Nếu vietnam_stock_date là null thì trả về 0, ngược lại trả về 1
        $status = is_null($order['vietnam_stock_date']) ? '0' : '1';
        return $this->response->setStatusCode(200)->setBody($status);
    }

    public function updateVietnamStockDate()
    {
        try {
            // Nhận dữ liệu JSON từ body request
            $data = $this->request->getJSON(true);

            if (!$data || empty($data['tracking_code'])) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Dữ liệu không hợp lệ',
                    'error' => 'Yêu cầu tracking_code'
                ], 400);
            }

            $trackingCode = $data['tracking_code'];
            $order = $this->orderModel->where('tracking_code', $trackingCode)->first();

            if (!$order) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Không tìm thấy đơn hàng',
                    'error' => 'Tracking code không tồn tại'
                ], 404);
            }

            // Cập nhật vietnam_stock_date với thời gian hiện tại
            $currentTime = date('Y-m-d H:i:s');
            $this->orderModel->update($order['id'], [
                'vietnam_stock_date' => $currentTime
            ]);

            return $this->respond([
                'status' => 200,
                'message' => 'Cập nhật ngày nhập kho Việt Nam thành công',
                'data' => [
                    'tracking_code' => $trackingCode,
                    'vietnam_stock_date' => $currentTime
                ]
            ], 200);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 500,
                'message' => 'Lỗi trong quá trình cập nhật',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
