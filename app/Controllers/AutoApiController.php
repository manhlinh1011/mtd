<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class AutoApiController extends ResourceController
{
    /**
     * API: Tạo đơn hàng tự động cho n8n
     * Đầu vào: JSON gồm tracking_code, quantity, product_type, customer_code, sub_customer_code, total_weight, volume
     */
    public function createAutoOrder()
    {
        $db = \Config\Database::connect();
        $request = $this->request->getJSON(true);
        if (!$request) {
            return $this->respond([
                "status" => 200,
                "success" => false,
                "message" => "Dữ liệu đầu vào không hợp lệ"
            ], 200);
        }

        // Trim toàn bộ dữ liệu đầu vào
        foreach ($request as $key => $value) {
            if (is_string($value)) {
                $request[$key] = trim($value);
            }
        }

        // Danh sách các trường bắt buộc
        $requiredFields = ['tracking_code', 'customer_code', 'sub_customer_code', 'product_type', 'quantity', 'total_weight', 'volume'];
        foreach ($requiredFields as $field) {
            if (!isset($request[$field]) || (is_string($request[$field]) && trim($request[$field]) === '') || (is_null($request[$field]))) {
                // Nếu product_type rỗng thì gán 'TẠP', các trường khác thì báo lỗi
                if ($field === 'product_type') {
                    $request['product_type'] = 'TẠP';
                } else {
                    return $this->respond([
                        'status' => 200,
                        'success' => false,
                        'message' => 'Thiếu hoặc rỗng trường bắt buộc: ' . $field
                    ], 200);
                }
            }
        }

        // Xử lý tracking_code: nếu có nhiều dòng thì nối lại bằng dấu gạch ngang
        $tracking_code_input = $request['tracking_code'] ?? '';
        $tracking_code_original = $tracking_code_input;
        if (strpos($tracking_code_input, "\n") !== false || strpos($tracking_code_input, "\r") !== false) {
            // Chuẩn hóa về \n, tách các dòng, trim từng dòng, loại bỏ dòng rỗng
            $lines = preg_split('/\r\n|\r|\n/', $tracking_code_input);
            $lines = array_filter(array_map('trim', $lines));
            $tracking_code_input = implode('-', $lines);
        }
        $has_space_in_tracking_code = strpos($tracking_code_input, ' ') !== false;
        $tracking_code_processed = str_replace(' ', '', $tracking_code_input);
        $request['tracking_code'] = $tracking_code_processed;

        // 1. Kiểm tra customer_code
        $customer = $db->table('customers')->where('customer_code', $request['customer_code'])->get()->getRowArray();
        if (!$customer) {
            return $this->respond([
                "status" => 200,
                "success" => false,
                "message" => "Không tìm thấy khách hàng với customer_code: " . $request['customer_code']
            ], 200);
        }
        $customer_id = $customer['id'];

        // 1.1. Kiểm tra tracking_code đã tồn tại chưa
        $existingOrder = $db->table('orders')->where('tracking_code', $request['tracking_code'])->get()->getRowArray();
        if ($existingOrder) {
            // Lấy sub_customer_code nếu có
            $sub_customer_code = null;
            if (!empty($existingOrder['sub_customer_id'])) {
                $sub = $db->table('sub_customers')->where('id', $existingOrder['sub_customer_id'])->get()->getRowArray();
                if ($sub) {
                    $sub_customer_code = $sub['sub_customer_code'];
                }
            }
            $existingOrder['sub_customer_code'] = $sub_customer_code;
            // Luôn trả về trường notes, nếu không có thì là chuỗi rỗng
            $existingOrder['notes'] = $existingOrder['notes'] ?? '';
            return $this->respond([
                'status' => 200,
                'success' => false,
                'message' => 'Mã vận chuyển (tracking_code) đã tồn tại trong hệ thống',
                'order' => $existingOrder
            ], 200);
        }

        // 2. Kiểm tra/tạo product_type
        $product_type_name = mb_strtoupper($request['product_type'], 'UTF-8');
        $productType = $db->table('product_types')->where('name', $product_type_name)->get()->getRowArray();
        if (!$productType) {
            $db->table('product_types')->insert([
                'name' => $product_type_name,
                'description' => $request['product_type']
            ]);
            $product_type_id = $db->insertID();
        } else {
            $product_type_id = $productType['id'];
        }

        // 3. Kiểm tra/tạo sub_customer_code nếu có
        $sub_customer_id = null;
        if (!empty($request['sub_customer_code'])) {
            $input_sub_code = $request['sub_customer_code'];
            $slug_sub_code = $this->slugify($input_sub_code);
            $subCustomer = $db->table('sub_customers')
                ->where('sub_customer_code', $slug_sub_code)
                ->get()->getRowArray();
            $need_create_sub = false;
            if ($subCustomer) {
                if ($subCustomer['customer_id'] != $customer_id) {
                    // Đã tồn tại nhưng không thuộc customer này, tạo mới với tiền tố customer_code
                    $slug_sub_code = $this->slugify($customer['customer_code'] . '-' . $input_sub_code);
                    $need_create_sub = true;
                }
            } else {
                $need_create_sub = true;
            }
            if ($need_create_sub) {
                $db->table('sub_customers')->insert([
                    'customer_id' => $customer_id,
                    'sub_customer_code' => $slug_sub_code,
                    'fullname' => $input_sub_code,
                    'phone' => '1234567890',
                    'address' => 'Hà Nội'
                ]);
                $sub_customer_id = $db->insertID();
            } else {
                $sub_customer_id = $subCustomer['id'];
            }
        }

        $created_at = date('Y-m-d H:i:s');
        $vietnam_stock_date = date('Y-m-d H:i:s', strtotime($created_at) + 5);
        // 4. Tạo đơn hàng
        $orderData = [
            'tracking_code' => $request['tracking_code'],
            'quantity' => $request['quantity'],
            'product_type_id' => $product_type_id,
            'customer_id' => $customer_id,
            'total_weight' => $request['total_weight'],
            'volume' => $request['volume'],
            'order_code' => 'CNM',
            'created_at' => $created_at,
            'vietnam_stock_date' => $vietnam_stock_date
        ];
        if ($sub_customer_id !== null) {
            $orderData['sub_customer_id'] = $sub_customer_id;
        }
        $db->table('orders')->insert($orderData);
        $order_id = $db->insertID();
        $order = $db->table('orders')->where('id', $order_id)->get()->getRowArray();

        $new_tracking_code = '';
        if ($tracking_code_processed !== trim($tracking_code_original)) {
            $db->table('orders')->where('id', $order_id)->update([
                'notes' => 'Tạo mã vận đơn mới'
            ]);
            $order['notes'] = 'Tạo mã vận đơn mới';
            $new_tracking_code = $tracking_code_processed;
        }
        // Lấy sub_customer_code nếu có
        $sub_customer_code = null;
        if (!empty($order['sub_customer_id'])) {
            $sub = $db->table('sub_customers')->where('id', $order['sub_customer_id'])->get()->getRowArray();
            if ($sub) {
                $sub_customer_code = $sub['sub_customer_code'];
            }
        }
        $order['sub_customer_code'] = $sub_customer_code;
        // Luôn trả về trường notes, nếu không có thì là chuỗi rỗng
        $order['notes'] = $order['notes'] ?? '';

        return $this->respond([
            'status' => 200,
            'success' => true,
            'message' => 'Tạo đơn hàng thành công',
            'data' => $order,
            'new_tracking_code' => $new_tracking_code
        ], 200);
    }

    /**
     * Hàm slugify: chuyển chuỗi thành dạng slug (viết hoa, không dấu, thay ký tự lạ bằng gạch ngang)
     */
    private function slugify($str)
    {
        $str = mb_strtoupper($str, 'UTF-8');
        $str = $this->removeVietnamese($str);
        $str = preg_replace('/[^A-Z0-9]+/u', '-', $str);
        $str = trim($str, '-');
        return $str;
    }

    /**
     * Loại bỏ dấu tiếng Việt
     */
    private function removeVietnamese($str)
    {
        $unicode = [
            'a' => ['á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'],
            'A' => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ'],
            'e' => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'],
            'E' => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị'],
            'I' => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị'],
            'o' => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'],
            'O' => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ'],
            'u' => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'],
            'U' => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự'],
            'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
            'Y' => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ'],
            'd' => ['đ'],
            'D' => ['Đ'],
        ];

        // Loại bỏ ký tự đặc biệt trước
        $specialChars = ['`', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '=', '{', '}', '[', ']', '|', '\\', ':', ';', '"', "'", '<', '>', ',', '.', '?', '/'];
        $str = str_replace($specialChars, '', $str);

        // Loại bỏ dấu tiếng Việt
        foreach ($unicode as $nonUnicode => $uniList) {
            $str = str_replace($uniList, $nonUnicode, $str);
        }

        // Chỉ giữ lại chữ cái, số và dấu gạch ngang
        $str = preg_replace('/[^A-Za-z0-9\-]/u', '', $str);

        return $str;
    }
}
