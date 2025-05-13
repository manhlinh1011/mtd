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


            /// Kiểm tra sub_customer_id (nếu có)
            if (!empty($data['sub_customer_id'])) {
                $subCustomerModel = new \App\Models\SubCustomerModel();
                $subCustomer = $subCustomerModel->find($data['sub_customer_id']);
                if (!$subCustomer || $subCustomer['customer_id'] != $data['customer_id']) {
                    return $this->respond([
                        'status' => 404,
                        'message' => 'Không tìm thấy mã phụ hoặc mã phụ không thuộc khách hàng này',
                        'error' => 'Sub Customer ID không hợp lệ'
                    ], 404);
                }
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

    /**
     * API: Lấy danh sách mã phụ (sub customers) theo customer_id
     */
    public function getSubCustomers()
    {
        try {
            $customerId = $this->request->getGet('customer_id');

            if (empty($customerId) || !is_numeric($customerId)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Customer ID không hợp lệ',
                    'error' => 'Yêu cầu customer_id hợp lệ'
                ], 400);
            }

            // Load model SubCustomerModel
            $subCustomerModel = new \App\Models\SubCustomerModel();

            // Lấy danh sách mã phụ theo customer_id
            $subCustomers = $subCustomerModel->where('customer_id', $customerId)->findAll();

            return $this->respond([
                'status' => 200,
                'message' => 'Danh sách mã phụ được tải thành công',
                'data' => $subCustomers
            ], 200);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 500,
                'message' => 'Lỗi trong quá trình tải danh sách mã phụ',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * API: Thêm đơn hàng TMDT
     */
    public function AddTMDT()
    {
        try {
            // Kiểm tra token
            $apiToken = $this->request->getHeaderLine('mtd');
            $validToken = 'bh3o7gT1YgL1mOMR6H1PWBuAauPhq6io5OF0v32r5cXwl7tHoEMdS09v2dvgJgG3'; // Token cố định
            if (!$apiToken || $apiToken !== $validToken) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Xác thực thất bại',
                    'error' => 'Token không hợp lệ'
                ], 401);
            }

            // Nhận dữ liệu từ getPost
            $data = $this->request->getPost();

            // Kiểm tra dữ liệu đầu vào
            if (empty($data['ma_van_don']) || empty($data['can_nang'])) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Dữ liệu không hợp lệ',
                    'error' => 'Yêu cầu ma_van_don, can_nang và ma_bao'
                ], 400);
            }

            // Kiểm tra mã vận đơn đã tồn tại chưa
            $existingOrder = $this->orderModel->where('tracking_code', $data['ma_van_don'])->first();
            if ($existingOrder) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Mã vận đơn đã tồn tại',
                    'error' => 'Mã vận đơn đã được sử dụng'
                ], 400);
            }

            // Lấy tỷ giá mới nhất từ bảng exchange_rates
            $db = \Config\Database::connect();
            $exchangeRate = $db->table('exchange_rates')
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$exchangeRate) {
                return $this->respond([
                    'status' => 500,
                    'message' => 'Không tìm thấy tỷ giá',
                    'error' => 'Chưa có tỷ giá nào được thiết lập'
                ], 500);
            }

            // Lấy thông tin khách hàng để lấy giá mặc định
            $customer = $this->customerModel->find(196);
            if (!$customer) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Không tìm thấy thông tin khách hàng',
                    'error' => 'Customer ID không hợp lệ'
                ], 404);
            }

            // Chuẩn bị dữ liệu đơn hàng
            $orderData = [
                'tracking_code' => $data['ma_van_don'],
                'total_weight' => $data['can_nang'],
                'package_code' => $data['ma_bao'],
                'customer_id' => 196,
                'product_type_id' => 27,
                'package_index' => 0,
                'quantity' => 1,
                'order_code' => 'TMDT',
                'exchange_rate' => $exchangeRate['rate'],
                'price_per_kg' => $customer['price_per_kg'],
                'price_per_cubic_meter' => $customer['price_per_cubic_meter'],
                'domestic_fee' => 0, // Giá trị mặc định
                'shipping_fee' => 0, // Giá trị mặc định
                'volume' => 0.000, // Giá trị mặc định
                'length' => 0, // Giá trị mặc định
                'width' => 0, // Giá trị mặc định
                'height' => 0 // Giá trị mặc định
            ];

            // Lưu đơn hàng
            $this->orderModel->insert($orderData);

            return $this->respond([
                'status' => 200,
                'message' => 'Thêm đơn hàng TMDT thành công',
            ], 200);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 500,
                'message' => 'Lỗi trong quá trình thêm đơn hàng TMDT',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
