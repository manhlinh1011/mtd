<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\CustomerModel;
use App\Models\ProductTypeModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Config\WebhookConfig;

class OrderController extends BaseController
{
    protected $orderModel, $customerModel, $productTypeModel, $webhookConfig;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
        $this->productTypeModel = new ProductTypeModel();
        $this->webhookConfig = new WebhookConfig();
    }

    public function index()
    {
        $perPage = 30; // Số lượng bản ghi mỗi trang

        // Lấy thông tin tìm kiếm từ GET
        $filters = [
            'tracking_code' => trim($this->request->getGet('tracking_code') ?? ''),
            'customer_code' => $this->request->getGet('customer_code') ?? 'ALL',
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
            'shipping_status' => $this->request->getGet('shipping_status') ?? 'ALL',
            'order_code' => $this->request->getGet('order_code')
        ];

        // Lấy thông tin mã phụ từ GET
        $subCustomerId = $this->request->getGet('sub_customer_id') ?? 'ALL';

        // Lấy thống kê đơn hàng
        $data['orderStats'] = $this->orderModel->getOrderStatistics($filters);

        // Cấu hình query cho danh sách đơn hàng
        $query = $this->orderModel
            ->select('orders.*, 
              customers.fullname AS customer_name, 
              customers.customer_code AS customer_code, 
              product_types.name AS product_type_name, 
              i.shipping_confirmed_at, 
              i.id AS invoice_id,
              sub_customers.sub_customer_code')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->orderBy('orders.id', 'DESC');

        // Thêm điều kiện tìm kiếm cho danh sách
        if (!empty($filters['tracking_code'])) {
            $query->like('orders.tracking_code', $filters['tracking_code']);
        }

        if (!empty($filters['order_code'])) {
            $query->like('orders.order_code', $filters['order_code']);
        }

        if (!empty($filters['customer_code']) && $filters['customer_code'] !== 'ALL') {
            $query->where('customers.customer_code', $filters['customer_code']);

            // Kiểm tra nếu có lọc theo mã phụ
            if ($subCustomerId !== 'ALL') {
                if ($subCustomerId === 'NONE') {
                    // Lọc các đơn hàng không có mã phụ
                    $query->where('orders.sub_customer_id IS NULL');
                } else {
                    // Lọc theo mã phụ cụ thể
                    $query->where('orders.sub_customer_id', $subCustomerId);
                }
            }
        }

        if (!empty($filters['from_date'])) {
            $query->where('orders.created_at >=', $filters['from_date'] . ' 00:00:00');
        }

        if (!empty($filters['to_date'])) {
            $query->where('orders.created_at <=', $filters['to_date'] . ' 23:59:59');
        }

        // Thêm điều kiện lọc theo trạng thái giao hàng
        if (!empty($filters['shipping_status']) && $filters['shipping_status'] !== 'ALL') {
            switch ($filters['shipping_status']) {
                case 'china_stock':
                    $query->where('orders.vietnam_stock_date IS NULL');
                    break;
                case 'in_stock':
                    $query->where('orders.vietnam_stock_date IS NOT NULL')
                        ->where('orders.invoice_id IS NULL');
                    break;
                case 'pending_shipping':
                    $query->where('orders.invoice_id IS NOT NULL')
                        ->where('i.shipping_confirmed_at IS NULL');
                    break;
                case 'shipped':
                    $query->where('i.shipping_confirmed_at IS NOT NULL');
                    break;
            }
        }

        // Lấy dữ liệu phân trang
        $data['orders'] = $query->paginate($perPage);
        $data['pager'] = $this->orderModel->pager;

        // Lấy danh sách khách hàng để hiển thị dropdown
        $customerModel = new \App\Models\CustomerModel();
        $data['customers'] = $customerModel->select('customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();

        // Lấy danh sách mã lô để hiển thị dropdown
        $orderCodes = $this->orderModel->distinct()
            ->select('order_code')
            ->where('order_code IS NOT NULL')
            ->where('order_code !=', '')
            ->orderBy('order_code', 'ASC')
            ->findAll();

        // Chuyển đổi kết quả thành mảng đơn giản
        $data['order_codes'] = array_column($orderCodes, 'order_code');

        // Truyền giá trị tìm kiếm vào View
        $data['tracking_code'] = $filters['tracking_code'];
        $data['customer_code'] = $filters['customer_code'];
        $data['from_date'] = $filters['from_date'];
        $data['to_date'] = $filters['to_date'];
        $data['sub_customer_id'] = $subCustomerId;
        $data['shipping_status'] = $filters['shipping_status'];
        $data['order_code'] = $filters['order_code'];

        return view('orders/index', $data);
    }


    public function create()
    {
        $data['customers'] = $this->customerModel->findAll();
        $data['product_types'] = $this->productTypeModel->findAll();
        if ($this->request->getMethod() === 'post') {
            $this->orderModel->save($this->request->getPost());
            return redirect()->to('/orders');
        }
        return view('orders/create', $data);
    }


    public function delete($orderId)
    {
        try {
            // Kiểm tra quyền
            if (!in_array(session('role'), ['Quản lý'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa đơn hàng.'
                ]);
            }

            $orderModel = new \App\Models\OrderModel();
            $systemLogModel = new \App\Models\SystemLogModel();

            // Lấy thông tin đơn hàng
            $order = $orderModel->find($orderId);
            if (!$order) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Đơn hàng không tồn tại.'
                ]);
            }

            // Kiểm tra nếu đơn hàng đã nằm trong phiếu xuất (invoice_id != null)
            if ($order['invoice_id'] !== null) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Đơn hàng đã nằm trong phiếu xuất #{$order['invoice_id']}. Bạn không được phép xóa."
                ]);
            }

            // Lưu thông tin đơn hàng trước khi xóa vào log
            $details = json_encode([
                'order_data' => $order
            ]);

            $trackingCode = $order['tracking_code'];

            // Xóa đơn hàng
            $orderModel->delete($orderId);

            // Ghi log
            $systemLogModel->addLog([
                'entity_type' => 'order',
                'entity_id' => $orderId,
                'action_type' => 'delete',
                'created_by' => session()->get('user_id'),
                'details' => $details,
                'notes' => "Xóa đơn hàng #{$orderId} - Mã vận chuyển: {$trackingCode}"
            ]);

            // Lấy URL hiện tại từ request trước khi xóa
            $currentUrl = previous_url() ?: base_url('/orders');

            // Lưu thông báo vào session flashdata
            $message = "Xóa thành công đơn hàng <strong style='color: red;'>{$order['tracking_code']}</strong>";
            session()->setFlashdata('error', $message);

            // Redirect về URL hiện tại
            return redirect()->to($currentUrl);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateBulk()
    {
        $orders = $this->request->getPost('orders'); // Nhận dữ liệu từ form
        $currentPage = $this->request->getPost('current_page') ?? 1;

        if ($orders) {
            $updatedCount = 0;

            foreach ($orders as $id => $data) {
                // Lọc và chuyển đổi dữ liệu
                $updateData = [
                    'price_per_kg' => isset($data['price_per_kg']) ? (int) str_replace('.', '', $data['price_per_kg']) : 0,
                    'price_per_cubic_meter' => isset($data['price_per_cubic_meter']) ? (int) str_replace('.', '', $data['price_per_cubic_meter']) : 0,
                    'domestic_fee' => isset($data['domestic_fee']) ? (float) str_replace(',', '.', $data['domestic_fee']) : 0.00,
                ];

                // Lấy dữ liệu hiện tại từ database
                $existingData = $this->orderModel->find($id);

                // Kiểm tra xem dữ liệu mới có khác dữ liệu cũ không
                if (
                    isset($existingData) &&
                    (
                        $existingData['price_per_kg'] != $updateData['price_per_kg'] ||
                        $existingData['price_per_cubic_meter'] != $updateData['price_per_cubic_meter'] ||
                        $existingData['domestic_fee'] != $updateData['domestic_fee']
                    )
                ) {
                    // Cập nhật nếu có thay đổi
                    if ($this->orderModel->update($id, $updateData)) {
                        $updatedCount++;
                    }
                }
            }

            // Thông báo thành công
            return redirect()->to('/orders?page=' . $currentPage)
                ->with('success', 'Cập nhật thành công ' . $updatedCount . ' đơn hàng.');
        }

        // Thông báo lỗi nếu không có dữ liệu để cập nhật
        return redirect()->to('/orders?page=' . $currentPage)->with('error', 'Không có dữ liệu để cập nhật.');
    }



    public function updateVietnamStockDate()
    {
        // Lấy ID từ dữ liệu gửi lên
        $id = $this->request->getPost('id');

        // Kiểm tra ID có hợp lệ không
        if (empty($id) || !is_numeric($id)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'ID không hợp lệ.'
            ]);
        }

        // Lấy thông tin đơn hàng với thông tin khách hàng và khách hàng phụ
        $order = $this->orderModel
            ->select('orders.*, 
                customers.customer_code, 
                customers.fullname, 
                customers.thread_id_zalo_notify_order as customer_thread_id,
                customers.msg_zalo_type_notify_order as customer_msg_type,
                sub_customers.sub_customer_code,
                sub_customers.fullname as sub_customer_name,
                sub_customers.thread_id_zalo_notify_order as sub_customer_thread_id,
                sub_customers.msg_zalo_type_notify_order as sub_customer_msg_type')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->where('orders.id', $id)
            ->first();

        if (!$order) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Không tìm thấy đơn hàng.'
            ]);
        }

        // Cập nhật trường vietnam_stock_date
        $update = $this->orderModel->update($id, [
            'vietnam_stock_date' => date('Y-m-d H:i:s')
        ]);

        // Kiểm tra kết quả
        if ($update) {
            // Gửi thông báo qua webhook
            $this->notifyOrder($order['id']);

            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Cập nhật ngày nhập kho thành công.',
                'id' => $id
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Có lỗi xảy ra khi cập nhật ngày nhập kho.'
            ]);
        }
    }

    public function updateVietnamStockDateByTrackingCode()
    {
        // Lấy tracking_code từ dữ liệu gửi lên
        $data = $this->request->getJSON(true);
        $trackingCode = $data['tracking_code'] ?? null;

        // Kiểm tra tracking_code có hợp lệ không
        if (empty($trackingCode)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Mã vận chuyển không hợp lệ.',
                'tracking_code' => $trackingCode
            ]);
        }

        // Lấy thông tin đơn hàng với thông tin khách hàng và khách hàng phụ
        $order = $this->orderModel
            ->select('orders.*, 
                customers.customer_code, 
                customers.fullname, 
                customers.thread_id_zalo_notify_order as customer_thread_id,
                customers.msg_zalo_type_notify_order as customer_msg_type,
                sub_customers.sub_customer_code,
                sub_customers.fullname as sub_customer_name,
                sub_customers.thread_id_zalo_notify_order as sub_customer_thread_id,
                sub_customers.msg_zalo_type_notify_order as sub_customer_msg_type')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$order) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Không tìm thấy đơn hàng.',
                'tracking_code' => $trackingCode
            ]);
        }

        // Chỉ cập nhật khi `vietnam_stock_date` là NULL
        $update = $this->orderModel
            ->where('tracking_code', $trackingCode)
            ->where('vietnam_stock_date IS NULL')
            ->set('vietnam_stock_date', date('Y-m-d H:i:s'))
            ->update();

        // Kiểm tra kết quả
        if ($update) {
            if ($this->orderModel->affectedRows() > 0) {

                $this->notifyOrder($order['id']);

                return $this->response->setJSON([
                    'status' => 200,
                    'message' => 'Cập nhật ngày nhập kho thành công.',
                    'tracking_code' => $trackingCode
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 200,
                    'message' => 'Không có bản ghi nào cần cập nhật (ngày nhập kho đã tồn tại).',
                    'tracking_code' => $trackingCode
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Có lỗi xảy ra khi cập nhật ngày nhập kho.'
            ]);
        }
    }

    /**
     * Hiển thị form chỉnh sửa thông tin đơn hàng
     */
    public function edit($id)
    {
        $orderModel = new OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $subCustomerModel = new \App\Models\SubCustomerModel();

        // Lấy thông tin đơn hàng
        $order = $orderModel
            ->select('orders.*, invoices.payment_status as invoice_payment_status, invoices.shipping_status as invoice_shipping_status, invoices.id as invoice_id')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left')
            ->find($id);

        if (!$order) {
            return redirect()->to('/orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        // Lấy danh sách mã phụ cho khách hàng hiện tại
        $subCustomers = $subCustomerModel->where('customer_id', $order['customer_id'])->findAll();
        $hasSubCustomers = !empty($subCustomers);

        // Lịch sử trạng thái đơn hàng (timeline giống trang tracking)
        $statusHistory = [];
        if ($order['created_at']) {
            $statusHistory[] = [
                'time' => $order['created_at'],
                'status' => 'Nhập kho Trung Quốc'
            ];
        }
        if ($order['vietnam_stock_date']) {
            $statusHistory[] = [
                'time' => $order['vietnam_stock_date'],
                'status' => 'Nhập kho Việt Nam'
            ];
        }

        // Thông tin phiếu xuất chi tiết
        $invoiceDetails = null;
        if ($order['invoice_id']) {
            $invoiceDetails = $invoiceModel
                ->select('invoices.*, 
                     creator.fullname as creator_name, 
                     confirmer.fullname as confirmer_name, 
                     invoices.created_at as invoice_created_at, 
                     invoices.shipping_confirmed_at as shipping_confirmed_at')
                ->join('users as creator', 'creator.id = invoices.created_by', 'left')
                ->join('users as confirmer', 'confirmer.id = invoices.shipping_confirmed_by', 'left')
                ->where('invoices.id', $order['invoice_id'])
                ->first();

            if ($invoiceDetails) {
                $statusHistory[] = [
                    'time' => $invoiceDetails['created_at'],
                    'status' => 'Đã tạo phiếu xuất'
                ];
                if ($invoiceDetails['shipping_status'] === 'confirmed') {
                    $statusHistory[] = [
                        'time' => $invoiceDetails['shipping_confirmed_at'] ?? $invoiceDetails['created_at'],
                        'status' => 'Đã xuất hàng'
                    ];
                }

                // Nếu phiếu xuất đã thanh toán, cung cấp thông tin thanh toán mặc định
                if ($invoiceDetails['payment_status'] === 'paid') {
                    $invoiceDetails['payment_confirmer_name'] = 'Không có thông tin';
                    $invoiceDetails['payment_date'] = $invoiceDetails['updated_at'] ?? $invoiceDetails['created_at'];
                }
            }
        }

        // Tính giá trị đơn hàng
        $shippingCostByWeight = $order['total_weight'] * $order['price_per_kg'];
        $shippingCostByVolume = $order['volume'] * $order['price_per_cubic_meter'];
        $totalShippingCost = max($shippingCostByWeight, $shippingCostByVolume); // Lấy giá trị lớn hơn
        $exchangeRate = $order['exchange_rate'] > 0 ? $order['exchange_rate'] : 1; // Nếu không có tỷ giá, mặc định là 1
        $totalOrderValue = $totalShippingCost + ($order['domestic_fee'] * $exchangeRate);
        $totalOrderValue = $totalOrderValue + $order['official_quota_fee'] + $order['vat_tax'] + $order['import_tax'] + $order['other_tax'];



        // Kiểm tra giá và tạo thông báo lỗi nếu cần
        $orderValueError = null;
        if ($order['price_per_kg'] == 0 && $order['price_per_cubic_meter'] == 0) {
            $orderValueError = 'Cần nhập giá 1kg hoặc giá 1m³ để tính chi phí đơn hàng.';
        }

        $data = [
            'order' => $order,
            'customers' => $this->customerModel->findAll(),
            'productTypes' => $this->productTypeModel->findAll(),
            'statusHistory' => $statusHistory,
            'invoiceDetails' => $invoiceDetails,
            'totalOrderValue' => $totalOrderValue,
            'orderValueError' => $orderValueError, // Truyền thông báo lỗi
            'subCustomers' => $subCustomers,
            'hasSubCustomers' => $hasSubCustomers
        ];

        return view('orders/edit', $data);
    }

    /**
     * Xử lý cập nhật thông tin đơn hàng
     */
    public function update($id)
    {
        $order = $this->orderModel->find($id);

        if (!$order) {
            return redirect()->to('/orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        // Kiểm tra nếu đơn hàng đã liên kết với phiếu xuất
        if ($order['invoice_id'] !== null) {
            // Lấy thông tin phiếu xuất từ bảng invoices
            $invoiceModel = new \App\Models\InvoiceModel();
            $invoice = $invoiceModel->find($order['invoice_id']);

            if (!$invoice) {
                return redirect()->to('/orders')->with('error', 'Phiếu xuất liên quan không tồn tại.');
            }

            // Kiểm tra trạng thái thanh toán của phiếu xuất
            if ($invoice['payment_status'] === 'paid') {
                return redirect()->to('orders/edit/' . $id)->with('error', 'Không thể sửa đơn hàng vì phiếu xuất đã được thanh toán.');
            }
        }

        // Lấy dữ liệu từ form
        $data = [
            'customer_id' => $this->request->getPost('customer_id'),
            'product_type_id' => $this->request->getPost('product_type_id'),
            'quantity' => $this->request->getPost('quantity'),
            'package_code' => $this->request->getPost('package_code'),
            'order_code' => $this->request->getPost('order_code'),
            'total_weight' => $this->request->getPost('total_weight'),
            'price_per_kg' => $this->request->getPost('price_per_kg'),
            'price_per_cubic_meter' => $this->request->getPost('price_per_cubic_meter'),
            'exchange_rate' => $this->request->getPost('exchange_rate'),
            'domestic_fee' => $this->request->getPost('domestic_fee'),
            'official_quota_fee' => $this->request->getPost('official_quota_fee'),
            'vat_tax' => $this->request->getPost('vat_tax'),
            'import_tax' => $this->request->getPost('import_tax'),
            'other_tax' => $this->request->getPost('other_tax'),
            'volume' => $this->request->getPost('volume'),
            'length' => $this->request->getPost('length'),
            'width' => $this->request->getPost('width'),
            'height' => $this->request->getPost('height'),
            'notes' => $this->request->getPost('notes'),
        ];

        // Thêm mã phụ nếu được chọn
        $subCustomerId = $this->request->getPost('sub_customer_id');
        if (!empty($subCustomerId)) {
            $data['sub_customer_id'] = $subCustomerId;
        } else {
            // Nếu không chọn mã phụ, đặt là NULL
            $data['sub_customer_id'] = null;
        }

        // Cập nhật thông tin
        $this->orderModel->update($id, $data);

        return redirect()->to('orders/edit/' . $id)->with('success', 'Đơn hàng đã được cập nhật.');
    }



    public function importForm()
    {
        return view('orders/import');
    }

    public function preview()
    {
        if (!$this->request->getFile('excel_file')->isValid()) {
            return redirect()->to('/orders/import')->with('error', 'Vui lòng chọn file Excel hợp lệ.');
        }

        $file = $this->request->getFile('excel_file');
        $filePath = $file->getTempName();

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Lấy hai dòng tiêu đề
            $displayHeader = array_shift($rows); // Dòng tiếng Việt (STT, MÃ VẬN ĐƠN, ...)
            $technicalHeader = array_shift($rows); // Dòng kỹ thuật (stt, tracking_code, ...)

            if (!$technicalHeader) {
                return redirect()->to('/orders/import')->with('error', 'File Excel thiếu tiêu đề kỹ thuật.');
            }

            // Danh sách các cột bắt buộc (dựa trên tiêu đề kỹ thuật)
            $requiredColumns = [
                'stt',
                'tracking_code',
                'package_code',
                'order_code',
                'quantity',
                'domestic_fee',
                'total_weight',
                'volume',
                'length',
                'width',
                'height',
                'notes'
            ];

            // Kiểm tra các cột bắt buộc
            $missingColumns = [];
            foreach ($requiredColumns as $requiredColumn) {
                if (!in_array($requiredColumn, array_map('trim', array_map('strtolower', $technicalHeader)))) {
                    $missingColumns[] = $requiredColumn;
                }
            }

            if (!empty($missingColumns)) {
                $errorMessage = 'File Excel sai định dạng. Thiếu các cột: ' . implode(', ', $missingColumns);
                return redirect()->to('/orders/import')->with('error', $errorMessage);
            }

            // Nếu đủ cột, tiếp tục xử lý dữ liệu
            $data = [];
            foreach ($rows as $index => $row) {
                if (!empty(array_filter($row))) {
                    $data[] = $row;
                }
            }

            // Lưu file tạm để dùng trong bước import
            if (!is_dir(WRITEPATH . 'uploads')) {
                mkdir(WRITEPATH . 'uploads', 0775, true);
            }
            $newFileName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newFileName);
            $tempFilePath = WRITEPATH . 'uploads/' . $newFileName;

            return view('orders/import', [
                'displayHeader' => $displayHeader,
                'technicalHeader' => $technicalHeader,
                'data' => $data,
                'tempFilePath' => $tempFilePath
            ]);
        } catch (\Exception $e) {
            return redirect()->to('/orders/import')->with('error', 'Lỗi khi đọc file Excel: ' . $e->getMessage());
        }
    }

    public function import()
    {
        $tempFilePath = $this->request->getPost('temp_file_path');

        if (!$tempFilePath || !file_exists($tempFilePath)) {
            return redirect()->to('/orders/import')->with('error', 'File tạm không tồn tại hoặc đã hết hạn.');
        }

        try {
            $spreadsheet = IOFactory::load($tempFilePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Bỏ qua hai dòng tiêu đề
            $displayHeader = array_shift($rows);
            $technicalHeader = array_shift($rows);

            if (!$technicalHeader) {
                return redirect()->to('/orders/import')->with('error', 'File Excel thiếu tiêu đề kỹ thuật.');
            }

            $imported = 0;
            $failed = 0;
            $failureReasons = [];

            // Định nghĩa các cột có thể có trong file
            $columns = [
                'tracking_code',
                'customer_id',
                'package_code',
                'order_code',
                'quantity',
                'product_type_id',
                'domestic_fee',
                'exchange_rate',
                'total_weight',
                'volume',
                'price_per_kg',
                'price_per_cubic_meter',
                'length',
                'width',
                'height',
                'notes',
                'official_quota_fee',
                'vat_tax',
                'import_tax',
                'other_tax'
            ];

            // Ánh xạ tiêu đề kỹ thuật với cột trong database
            $columnMap = [];
            foreach ($technicalHeader as $index => $head) {
                $head = trim(strtolower($head)); // Loại bỏ khoảng trắng và chuyển thành chữ thường
                //log_message('debug', "Header index $index: '$head'");
                $columnMap[$index] = array_search($head, array_map('strtolower', $columns));
                //log_message('debug', "Mapped to column: " . ($columnMap[$index] !== false ? $columns[$columnMap[$index]] : 'null'));
            }

            // Lấy exchange rate từ hệ thống
            $exchangeRateModel = new \App\Models\ExchangeRateModel();
            $latestRate = $exchangeRateModel->orderBy('updated_at', 'DESC')->first();
            $defaultExchangeRate = $latestRate ? $latestRate['rate'] : 1;

            // Lấy tất cả tracking_code hiện có trong database
            $existingTrackingCodes = $this->orderModel->select('tracking_code')->findAll();
            $existingCodes = array_column($existingTrackingCodes, 'tracking_code');

            // Thêm danh sách tracking_code trong file để kiểm tra trùng lặp trong cùng file
            $trackingCodesInFile = [];

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                // Khởi tạo dữ liệu với giá trị mặc định
                $data = [
                    'tracking_code' => null,
                    'customer_id' => 196, // Mặc định là 196 (KHOTM)
                    'package_code' => null,
                    'order_code' => 'TMDT',
                    'quantity' => 1,
                    'product_type_id' => 27,
                    'domestic_fee' => 0,
                    'exchange_rate' => $defaultExchangeRate,
                    'total_weight' => 0, // Mặc định là 0
                    'volume' => 0, // Mặc định là 0
                    'price_per_kg' => 0, // Mặc định là 0
                    'price_per_cubic_meter' => 0, // Mặc định là 0
                    'length' => 0, // Mặc định là 0
                    'width' => 0, // Mặc định là 0
                    'height' => 0, // Mặc định là 0
                    'notes' => null,
                    'official_quota_fee' => 0,
                    'vat_tax' => 0,
                    'import_tax' => 0,
                    'other_tax' => 0
                ];

                // Ánh xạ dữ liệu từ file
                foreach ($columnMap as $colIndex => $colNameIndex) {
                    if ($colNameIndex !== false && isset($row[$colIndex])) {
                        $colName = $columns[$colNameIndex]; // Lấy tên cột từ $columns
                        $data[$colName] = $row[$colIndex];
                        //log_message('debug', "Row $index, Col $colIndex ($colName): " . $row[$colIndex]);
                    }
                }

                // Chuyển đổi kiểu dữ liệu
                $data['quantity'] = (float)($data['quantity'] ?? 1);
                $data['domestic_fee'] = (float)($data['domestic_fee'] ?? 0);
                $data['total_weight'] = (float)($data['total_weight'] ?? 0); // Đảm bảo là float, mặc định 0
                $data['volume'] = (float)($data['volume'] ?? 0); // Đảm bảo là float, mặc định 0
                $data['price_per_kg'] = (float)($data['price_per_kg'] ?? 0); // Đảm bảo là float, mặc định 0
                $data['price_per_cubic_meter'] = (float)($data['price_per_cubic_meter'] ?? 0); // Đảm bảo là float, mặc định 0
                $data['length'] = (float)($data['length'] ?? 0); // Đảm bảo là float, mặc định 0
                $data['width'] = (float)($data['width'] ?? 0); // Đảm bảo là float, mặc định 0
                $data['height'] = (float)($data['height'] ?? 0); // Đảm bảo là float, mặc định 0

                // Kiểm tra dữ liệu
                $errors = [];
                // Kiểm tra tracking_code
                if ($data['tracking_code'] === null || trim($data['tracking_code']) === '') {
                    $errors[] = 'Thiếu tracking_code';
                } else {
                    // Kiểm tra trùng lặp trong cùng file
                    if (in_array($data['tracking_code'], $trackingCodesInFile)) {
                        $errors[] = 'Trùng tracking_code trong file: ' . $data['tracking_code'];
                    } else {
                        $trackingCodesInFile[] = $data['tracking_code'];
                    }
                    // Kiểm tra trùng lặp với database
                    if (in_array($data['tracking_code'], $existingCodes)) {
                        $errors[] = 'Trùng tracking_code trong database: ' . $data['tracking_code'];
                    }
                }
                if ($data['total_weight'] < 0) {
                    $errors[] = 'Giá trị total_weight không hợp lệ';
                }
                if ($data['quantity'] <= 0) {
                    $errors[] = 'Quantity không hợp lệ';
                }
                if (!$this->customerModel->find($data['customer_id'])) {
                    $errors[] = 'customer_id không tồn tại: ' . $data['customer_id'];
                }
                if (!$this->productTypeModel->find($data['product_type_id'])) {
                    $errors[] = 'product_type_id không tồn tại: ' . $data['product_type_id'];
                }

                if (empty($errors)) {
                    $this->orderModel->insert($data);
                    $imported++;
                    $existingCodes[] = $data['tracking_code']; // Cập nhật danh sách
                    $successfulTrackingCodes[] = $data['tracking_code'];
                } else {
                    $failed++;
                    $failureReasons[$index + 3] = implode(', ', $errors); // +3 vì bỏ qua 2 dòng tiêu đề và index bắt đầu từ 0
                }
            }

            // Ghi log nếu có đơn hàng được import thành công
            if ($imported > 0) {
                $systemLogModel = new \App\Models\SystemLogModel();
                $logDetails = [
                    'imported_count' => $imported,
                    'tracking_codes' => $successfulTrackingCodes
                ];
                $systemLogModel->addLog([
                    'entity_type' => 'order',
                    'entity_id' => 0, // Không có ID cụ thể vì là import hàng loạt
                    'action_type' => 'import',
                    'created_by' => session()->get('user_id'),
                    'details' => json_encode($logDetails),
                    'notes' => "Import $imported đơn hàng từ file Excel."
                ]);
            }

            // Xóa file tạm sau khi xử lý
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            $message = "Tổng số bản ghi: " . (count($rows)) . ". <br>";
            $message .= "Import thành công: $imported đơn hàng. <br>";
            $message .= "Thất bại: $failed bản ghi.<br><hr/>";
            if (!empty($failureReasons)) {
                $message .= " Lý do thất bại: <br>" . implode('<br>', array_map(function ($key, $value) {
                    return "Dòng $key: $value";
                }, array_keys($failureReasons), $failureReasons));
            }

            return redirect()->to('/orders/import')->with('error', $message);
        } catch (\Exception $e) {
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            return redirect()->to('/orders/import')->with('error', 'Lỗi khi import file Excel: ' . $e->getMessage());
        }
    }

    public function vnCheck()
    {
        // Lấy danh sách khách hàng để hiển thị trong dropdown (nếu cần)
        $data['customers'] = $this->customerModel->findAll();
        return view('orders/vncheck', $data);
    }

    public function checkVietnamStock()
    {
        $trackingCode = trim($this->request->getPost('tracking_code'));
        if (empty($trackingCode)) {
            return '<div class="alert alert-danger">Vui lòng nhập mã vận đơn!</div>';
        }

        $order = $this->orderModel
            ->select('orders.*, customers.customer_code')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$order) {
            $result = ['status' => 'not_found'];
        } else {
            $statusHistory = [];
            if ($order['created_at']) {
                $statusHistory[] = [
                    'time' => $order['created_at'],
                    'status' => 'Nhập kho Trung Quốc'
                ];
            }
            if ($order['vietnam_stock_date']) {
                $statusHistory[] = [
                    'time' => $order['vietnam_stock_date'],
                    'status' => 'Nhập kho Việt Nam'
                ];
            }

            if ($order['vietnam_stock_date']) {
                $result = [
                    'status' => 'in_vn',
                    'order' => $order,
                    'statusHistory' => $statusHistory,
                    'notes' => $order['notes']
                ];
            } else {
                // Khởi tạo SubCustomerModel để lấy danh sách mã phụ nếu cần
                $subCustomerModel = new \App\Models\SubCustomerModel();

                $result = [
                    'status' => 'not_in_vn',
                    'order' => $order,
                    'statusHistory' => $statusHistory,
                    'isKHOTM' => ($order['customer_code'] === 'KHOTM'),
                    'customers' => $this->customerModel->findAll(),
                    'notes' => $order['notes']
                ];

                // Nếu không phải KHOTM và khách hàng đó có mã phụ, thêm danh sách mã phụ
                if ($order['customer_code'] !== 'KHOTM') {
                    $subCustomers = $subCustomerModel->where('customer_id', $order['customer_id'])->findAll();
                    $result['hasSubCustomers'] = !empty($subCustomers);
                    $result['subCustomers'] = $subCustomers;
                    $result['sub_customer_id'] = $order['sub_customer_id']; // Truyền sub_customer_id hiện tại
                }
            }
        }

        return view('orders/vncheck_result', array_merge($result, ['trackingCode' => $trackingCode]));
    }

    // Thêm phương thức API để lấy danh sách khách hàng phụ theo khách hàng chính
    public function getSubCustomers()
    {
        $customerId = $this->request->getGet('customer_id');

        if (!$customerId) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Không có ID khách hàng',
                'data' => []
            ]);
        }

        $subCustomerModel = new \App\Models\SubCustomerModel();
        $subCustomers = $subCustomerModel->where('customer_id', $customerId)->findAll();

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Lấy danh sách mã phụ thành công',
            'data' => $subCustomers
        ]);
    }

    // Thêm phương thức API để lấy danh sách khách hàng phụ theo mã khách hàng chính
    public function getSubCustomersByCode()
    {
        $customerCode = $this->request->getGet('customer_code');

        if (!$customerCode) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Không có mã khách hàng',
                'data' => []
            ]);
        }

        // Lấy ID khách hàng từ mã khách hàng
        $customer = $this->customerModel->where('customer_code', $customerCode)->first();
        if (!$customer) {
            return $this->response->setJSON([
                'status' => 404,
                'message' => 'Không tìm thấy khách hàng',
                'data' => []
            ]);
        }

        $subCustomerModel = new \App\Models\SubCustomerModel();
        $subCustomers = $subCustomerModel->where('customer_id', $customer['id'])->findAll();

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Lấy danh sách mã phụ thành công',
            'data' => $subCustomers
        ]);
    }

    public function updateCustomerAndStock()
    {
        try {
            $orderId = $this->request->getPost('order_id');
            $customerId = $this->request->getPost('customer_id');
            $subCustomerId = $this->request->getPost('sub_customer_id'); // Lấy ID mã phụ nếu có
            $trackingCode = $this->request->getPost('tracking_code');
            $notes = $this->request->getPost('notes'); // Nhận ghi chú từ form

            if (empty($orderId) || !is_numeric($orderId) || empty($customerId) || !is_numeric($customerId)) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => 'Dữ liệu không hợp lệ (order_id hoặc customer_id).'
                ]);
            }

            // Lấy thông tin đơn hàng trước khi cập nhật
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                return $this->response->setJSON([
                    'status' => 404,
                    'message' => 'Đơn hàng không tồn tại.'
                ]);
            }

            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'status' => 404,
                    'message' => 'Khách hàng không tồn tại.'
                ]);
            }

            $updateData = [
                'customer_id' => $customerId,
                'vietnam_stock_date' => date('Y-m-d H:i:s'),
                'notes' => $notes // Cập nhật ghi chú mới
            ];

            // Nếu có mã phụ được chọn, cập nhật thông tin mã phụ
            if (!empty($subCustomerId) && is_numeric($subCustomerId)) {
                $subCustomerModel = new \App\Models\SubCustomerModel();
                $subCustomer = $subCustomerModel->find($subCustomerId);

                if ($subCustomer && $subCustomer['customer_id'] == $customerId) {
                    $updateData['sub_customer_id'] = $subCustomerId;
                }
            }

            if ($order['price_per_kg'] == 0 && $order['price_per_cubic_meter'] == 0) {
                $updateData['price_per_kg'] = $customer['price_per_kg'] ?? 0;
                $updateData['price_per_cubic_meter'] = $customer['price_per_cubic_meter'] ?? 0;
            }

            $result = $this->orderModel->update($orderId, $updateData);
            if ($result) {
                // Lấy thông tin khách hàng mới sau khi cập nhật
                $updatedCustomer = $this->customerModel->find($customerId);

                // Lấy thông tin đơn hàng sau khi cập nhật với thông tin khách hàng và khách hàng phụ
                $updatedOrder = $this->orderModel
                    ->select('orders.*, 
                        customers.customer_code, 
                        customers.fullname, 
                        sub_customers.sub_customer_code,
                        sub_customers.fullname as sub_customer_name,
                        sub_customers.thread_id_zalo_notify_order as sub_customer_thread_id,
                        sub_customers.msg_zalo_type_notify_order as sub_customer_msg_type')
                    ->join('customers', 'customers.id = orders.customer_id', 'left')
                    ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
                    ->where('orders.id', $orderId)
                    ->first();

                // Gửi thông báo qua webhook
                $client = \Config\Services::curlrequest();

                // Kiểm tra và gửi thông báo dựa trên điều kiện
                if (empty($updatedOrder['sub_customer_id'])) {
                    // Trường hợp không có mã phụ
                    if (!empty($updatedCustomer['thread_id_zalo_notify_order'])) {
                        try {
                            $postData = [
                                'name' => $updatedOrder['fullname'],
                                'code' => $updatedOrder['customer_code'],
                                'total_weight' => $updatedOrder['total_weight'],
                                'thread_id_zalo_notify_order' => $updatedCustomer['thread_id_zalo_notify_order'],
                                'msg_zalo_type_notify_order' => $updatedCustomer['msg_zalo_type_notify_order'],
                                'tracking_code' => $updatedOrder['tracking_code']
                            ];

                            log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                            $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                                'headers' => [
                                    'Content-Type' => 'application/x-www-form-urlencoded'
                                ],
                                'form_params' => $postData
                            ]);

                            log_message('debug', 'Notification response: ' . $response->getBody());
                        } catch (\Exception $e) {
                            log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                        }
                    } else {
                        // Không gửi thông báo nếu không có thread_id_zalo_notify_order
                        log_message('debug', 'Không gửi thông báo cho khách hàng ' . $updatedOrder['customer_code'] . ' vì thiếu thread_id_zalo_notify_order');
                        return false;
                    }
                } else {
                    // Trường hợp có mã phụ
                    if (
                        $updatedCustomer['thread_id_zalo_notify_order'] === $updatedOrder['sub_customer_thread_id'] &&
                        !empty($updatedOrder['sub_customer_thread_id']) &&
                        !empty($updatedOrder['sub_customer_msg_type'])
                    ) {
                        // Gửi 1 post cho mã phụ nếu thread_id giống nhau
                        try {
                            $postData = [
                                'name' => $updatedOrder['sub_customer_name'],
                                'code' => $updatedOrder['sub_customer_code'],
                                'total_weight' => $updatedOrder['total_weight'],
                                'thread_id_zalo_notify_order' => $updatedOrder['sub_customer_thread_id'],
                                'msg_zalo_type_notify_order' => $updatedOrder['sub_customer_msg_type'],
                                'tracking_code' => $updatedOrder['tracking_code']
                            ];

                            log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                            $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                                'headers' => [
                                    'Content-Type' => 'application/x-www-form-urlencoded'
                                ],
                                'form_params' => $postData
                            ]);

                            log_message('debug', 'Notification response: ' . $response->getBody());
                        } catch (\Exception $e) {
                            log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                        }
                    } else {
                        // Gửi 2 post riêng biệt nếu thread_id khác nhau
                        if (!empty($updatedCustomer['thread_id_zalo_notify_order']) && !empty($updatedCustomer['msg_zalo_type_notify_order'])) {
                            try {
                                $postData = [
                                    'name' => $updatedOrder['fullname'],
                                    'code' => $updatedOrder['customer_code'],
                                    'total_weight' => $updatedOrder['total_weight'],
                                    'thread_id_zalo_notify_order' => $updatedCustomer['thread_id_zalo_notify_order'],
                                    'msg_zalo_type_notify_order' => $updatedCustomer['msg_zalo_type_notify_order'],
                                    'tracking_code' => $updatedOrder['tracking_code']
                                ];

                                log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                                $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                                    'headers' => [
                                        'Content-Type' => 'application/x-www-form-urlencoded'
                                    ],
                                    'form_params' => $postData
                                ]);

                                log_message('debug', 'Notification response: ' . $response->getBody());
                            } catch (\Exception $e) {
                                log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                            }
                        }

                        if (!empty($updatedOrder['sub_customer_thread_id']) && !empty($updatedOrder['sub_customer_msg_type'])) {
                            try {
                                $postData = [
                                    'name' => $updatedOrder['sub_customer_name'],
                                    'code' => $updatedOrder['sub_customer_code'],
                                    'total_weight' => $updatedOrder['total_weight'],
                                    'thread_id_zalo_notify_order' => $updatedOrder['sub_customer_thread_id'],
                                    'msg_zalo_type_notify_order' => $updatedOrder['sub_customer_msg_type'],
                                    'tracking_code' => $updatedOrder['tracking_code']
                                ];

                                log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                                $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                                    'headers' => [
                                        'Content-Type' => 'application/x-www-form-urlencoded'
                                    ],
                                    'form_params' => $postData
                                ]);

                                log_message('debug', 'Notification response: ' . $response->getBody());
                            } catch (\Exception $e) {
                                log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                            }
                        }
                    }
                }

                $statusHistory = [
                    ['time' => date('d/m/Y H:i', strtotime($order['created_at'])), 'status' => 'Nhập kho Trung Quốc'],
                    ['time' => date('d/m/Y H:i', strtotime(date('Y-m-d H:i:s'))), 'status' => 'Nhập kho Việt Nam']
                ];

                return $this->response->setJSON([
                    'status' => 200,
                    'message' => 'Cập nhật thành công.',
                    'order_id' => $orderId,
                    'trackingCode' => $trackingCode,
                    'statusHistory' => $statusHistory,
                    'notes' => $notes // Trả về ghi chú mới
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 500,
                    'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ]);
        }
    }



    public function exportVietnamStockToday()
    {
        // Đặt múi giờ Việt Nam (nếu chưa cấu hình toàn cục)
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $today = date('Y-m-d'); // Ví dụ: "2025-04-07"

        // Truy vấn các đơn hàng có vietnam_stock_date là hôm nay
        $query = $this->orderModel
            ->select('orders.*, 
              customers.fullname AS customer_name, 
              customers.customer_code AS customer_code, 
              product_types.name AS product_type_name, 
              invoices.shipping_status AS invoice_shipping_status, 
              invoices.id AS invoice_id,
              sub_customers.sub_customer_code') // Thêm sub_customer_code
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left') // Join với bảng sub_customers
            ->where("DATE(orders.vietnam_stock_date)", $today)
            ->orderBy('orders.id', 'DESC');

        $orders = $query->findAll();

        if (empty($orders)) {
            session()->setFlashdata('error', 'Không có đơn hàng nào nhập kho Việt Nam hôm nay (' . $today . ').');
            return redirect()->to('/orders');
        }

        // Sử dụng PhpSpreadsheet để tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Đặt tiêu đề với các cột riêng cho Dài, Rộng, Cao, thêm cột "Mã phụ" sau "Mã vận chuyển"
        $headers = [
            'ID',
            'Nhập TQ',
            'Nhập VN',
            'Mã vận chuyển',
            'Mã phụ', // Thêm cột Mã phụ
            'Mã lô',
            'Mã bao',
            'Khách hàng',
            'Hàng',
            'SL',
            'Số kg',
            'Dài',
            'Rộng',
            'Cao',
            'Khối',
            'Giá kg',
            'Giá Khối',
            'Phí tệ',
            'Tỷ giá',
            'Phí TQ',
            'Phí Chính ngạch',
            'Thuế VAT',
            'Thuế NK',
            'Thuế khác',
            'Tổng',
            'TT',
            'Trạng thái'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Thêm dữ liệu
        $row = 2;
        foreach ($orders as $order) {
            $gia_theo_cannang = $order['total_weight'] * $order['price_per_kg'];
            $gia_theo_khoi = $order['volume'] * $order['price_per_cubic_meter'];
            $gia_cuoi_cung = max($gia_theo_cannang, $gia_theo_khoi);
            $cach_tinh_gia = ($gia_theo_khoi > $gia_theo_cannang) ? 'TT' : 'KG';
            $gianoidia_trung = $order['domestic_fee'] * $order['exchange_rate'];

            $status = $order['invoice_id'] === null ? 'Tồn kho' : ($order['invoice_shipping_status'] === 'pending' ? 'Đang xuất' : 'Đã xuất');

            // Ghi các giá trị khác bằng fromArray, để trống cột "Mã vận chuyển"
            $sheet->fromArray([
                $order['id'],
                date('Y-m-d', strtotime($order['created_at'])),
                $order['vietnam_stock_date'],
                null, // Để trống cột "Mã vận chuyển", sẽ ghi riêng bằng setValueExplicit
                $order['sub_customer_code'] ?? '', // Cột Mã phụ, nếu không có thì để trống
                $order['order_code'],
                $order['package_code'],
                $order['customer_code'],
                $order['product_type_name'],
                $order['quantity'],
                $order['total_weight'],
                $order['length'],       // Cột Dài
                $order['width'],        // Cột Rộng
                $order['height'],       // Cột Cao
                $order['volume'],
                $order['price_per_kg'],
                $order['price_per_cubic_meter'],
                $order['domestic_fee'],
                $order['exchange_rate'],
                $gianoidia_trung,
                $order['official_quota_fee'],
                $order['vat_tax'],
                $order['import_tax'],
                $order['other_tax'],
                $gianoidia_trung + $gia_cuoi_cung + $order['official_quota_fee'] + $order['vat_tax'] + $order['import_tax'] + $order['other_tax'],
                $cach_tinh_gia,
                $status
            ], null, "A$row");

            // Ghi giá trị tracking_code bằng setValueExplicit để đảm bảo là text
            if (!empty($order['tracking_code'])) {
                $sheet->getCell('D' . $row)
                    ->setValueExplicit(
                        $order['tracking_code'],
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
            }

            // Định dạng cột "Mã vận chuyển" (cột D) là Text
            $sheet->getCell('D' . $row)->getStyle()->getNumberFormat()->setFormatCode('@');

            $row++;
        }

        // Định dạng toàn bộ cột "Mã vận chuyển" (cột D) là Text
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('@');

        // Định dạng cột "Khối" (cột O) để hiển thị 3 chữ số sau dấu phẩy
        $sheet->getStyle('O2:O' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('0.000');

        // Tự động điều chỉnh độ rộng cột
        foreach (range('A', 'W') as $columnID) { // Cập nhật range từ A đến W vì đã thêm 1 cột
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Tạo file Excel và tải xuống
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'DonHang_NhapKho_VN_' . $today . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    public function chinaStock()
    {
        $perPage = 30; // Số lượng bản ghi mỗi trang

        // Lấy thông tin tìm kiếm từ GET
        $days = $this->request->getGet('days') ?? 6;
        $customerCode = $this->request->getGet('customer_code') ?? 'ALL';

        // Tính ngày giới hạn dựa trên số ngày
        $dateLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Cấu hình query cho danh sách đơn hàng chưa về kho VN
        $query = $this->orderModel
            ->select('orders.*, 
            customers.fullname AS customer_name, 
            customers.customer_code AS customer_code, 
            product_types.name AS product_type_name, 
            i.shipping_confirmed_at, 
            i.id AS invoice_id')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->where('orders.vietnam_stock_date IS NULL')
            ->where('orders.created_at <=', $dateLimit)
            ->orderBy('orders.id', 'ASC');

        // Thêm điều kiện lọc theo mã khách hàng
        if ($customerCode !== 'ALL') {
            $query->where('customers.customer_code', $customerCode);
        }

        // Lấy dữ liệu phân trang
        $data['orders'] = $query->paginate($perPage);
        $data['pager'] = $this->orderModel->pager;

        // Lấy danh sách khách hàng cho dropdown
        $data['customers'] = $this->customerModel->select('customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();

        // Truyền giá trị tìm kiếm vào View
        $data['days'] = $days;
        $data['customer_code'] = $customerCode;

        return view('orders/china_stock', $data);
    }

    public function vietnamStock()
    {
        $perPage = 30; // Số lượng bản ghi mỗi trang

        // Lấy thông tin tìm kiếm từ GET
        $days = $this->request->getGet('days') ?? 4; // Mặc định 4 ngày
        $customerCode = $this->request->getGet('customer_code') ?? 'ALL';

        // Tính ngày giới hạn dựa trên số ngày
        $dateLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Cấu hình query cho danh sách đơn hàng đã về kho VN nhưng chưa giao
        $query = $this->orderModel
            ->select('orders.*, 
            customers.fullname AS customer_name, 
            customers.customer_code AS customer_code, 
            product_types.name AS product_type_name')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->where('orders.vietnam_stock_date IS NOT NULL') // Đã về kho VN
            ->where('orders.invoice_id IS NULL') // Chưa giao (không có invoice)
            ->where('orders.vietnam_stock_date <=', $dateLimit) // Lọc theo số ngày
            ->orderBy('orders.vietnam_stock_date', 'ASC');

        // Thêm điều kiện lọc theo mã khách hàng
        if ($customerCode !== 'ALL') {
            $query->where('customers.customer_code', $customerCode);
        }

        // Lấy dữ liệu phân trang
        $data['orders'] = $query->paginate($perPage);
        $data['pager'] = $this->orderModel->pager;

        // Lấy danh sách khách hàng cho dropdown
        $data['customers'] = $this->customerModel->select('customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();

        // Truyền giá trị tìm kiếm vào View
        $data['days'] = $days;
        $data['customer_code'] = $customerCode;

        return view('orders/vietnam_stock', $data);
    }

    /**
     * Gửi thông báo đơn hàng về kho VN
     * @param int $order_id ID của đơn hàng
     * @return bool Trả về true nếu gửi thông báo thành công, false nếu thất bại
     */
    private function notifyOrder($order_id)
    {
        try {
            // Lấy thông tin đơn hàng với thông tin khách hàng và khách hàng phụ
            $order = $this->orderModel
                ->select('orders.*, 
                    customers.customer_code, 
                    customers.fullname, 
                    customers.id as customer_id,
                    sub_customers.sub_customer_code,
                    sub_customers.fullname as sub_customer_name,
                    sub_customers.thread_id_zalo_notify_order as sub_customer_thread_id,
                    sub_customers.msg_zalo_type_notify_order as sub_customer_msg_type')
                ->join('customers', 'customers.id = orders.customer_id', 'left')
                ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
                ->where('orders.id', $order_id)
                ->first();

            if (!$order) {
                log_message('error', 'Không tìm thấy đơn hàng ID: ' . $order_id);
                return false;
            }

            // Lấy thông tin khách hàng
            $customer = $this->customerModel->find($order['customer_id']);
            if (!$customer) {
                log_message('error', 'Không tìm thấy khách hàng ID: ' . $order['customer_id']);
                return false;
            }

            // Gửi thông báo qua webhook
            $client = \Config\Services::curlrequest();

            // Kiểm tra và gửi thông báo dựa trên điều kiện
            if (empty($order['sub_customer_id'])) {
                // Trường hợp không có mã phụ
                if (!empty($customer['thread_id_zalo_notify_order'])) {
                    try {
                        $postData = [
                            'name' => $order['fullname'],
                            'code' => $order['customer_code'],
                            'total_weight' => $order['total_weight'],
                            'thread_id_zalo_notify_order' => $customer['thread_id_zalo_notify_order'],
                            'msg_zalo_type_notify_order' => $customer['msg_zalo_type_notify_order'],
                            'tracking_code' => $order['tracking_code']
                        ];

                        log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                        $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                            'headers' => [
                                'Content-Type' => 'application/x-www-form-urlencoded'
                            ],
                            'form_params' => $postData
                        ]);

                        log_message('debug', 'Notification response: ' . $response->getBody());
                        return true;
                    } catch (\Exception $e) {
                        log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                        return false;
                    }
                } else {
                    // Không gửi thông báo nếu không có thread_id_zalo_notify_order
                    log_message('debug', 'Không gửi thông báo cho khách hàng ' . $order['customer_code'] . ' vì thiếu thread_id_zalo_notify_order');
                    return false;
                }
            } else {
                // Trường hợp có mã phụ
                if (
                    $customer['thread_id_zalo_notify_order'] === $order['sub_customer_thread_id'] &&
                    !empty($order['sub_customer_thread_id'])
                ) {
                    // Gửi 1 post cho mã phụ nếu thread_id giống nhau
                    try {
                        $postData = [
                            'name' => $order['sub_customer_name'],
                            'code' => $order['sub_customer_code'],
                            'total_weight' => $order['total_weight'],
                            'thread_id_zalo_notify_order' => $order['sub_customer_thread_id'],
                            'msg_zalo_type_notify_order' => $order['sub_customer_msg_type'],
                            'tracking_code' => $order['tracking_code']
                        ];

                        log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                        $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                            'headers' => [
                                'Content-Type' => 'application/x-www-form-urlencoded'
                            ],
                            'form_params' => $postData
                        ]);

                        log_message('debug', 'Notification response: ' . $response->getBody());
                        return true;
                    } catch (\Exception $e) {
                        log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                        return false;
                    }
                } else {
                    $success = true;
                    // Gửi 2 post riêng biệt nếu thread_id khác nhau
                    if (!empty($customer['thread_id_zalo_notify_order'])) {
                        try {
                            $postData = [
                                'name' => $order['fullname'],
                                'code' => $order['customer_code'],
                                'total_weight' => $order['total_weight'],
                                'thread_id_zalo_notify_order' => $customer['thread_id_zalo_notify_order'],
                                'msg_zalo_type_notify_order' => $customer['msg_zalo_type_notify_order'],
                                'tracking_code' => $order['tracking_code']
                            ];

                            log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                            $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                                'headers' => [
                                    'Content-Type' => 'application/x-www-form-urlencoded'
                                ],
                                'form_params' => $postData
                            ]);

                            log_message('debug', 'Notification response: ' . $response->getBody());
                        } catch (\Exception $e) {
                            log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                            $success = false;
                        }
                    }

                    if (!empty($order['sub_customer_thread_id'])) {
                        try {
                            $postData = [
                                'name' => $order['sub_customer_name'],
                                'code' => $order['sub_customer_code'],
                                'total_weight' => $order['total_weight'],
                                'thread_id_zalo_notify_order' => $order['sub_customer_thread_id'],
                                'msg_zalo_type_notify_order' => $order['sub_customer_msg_type'],
                                'tracking_code' => $order['tracking_code']
                            ];

                            log_message('debug', 'Sending notification with data: ' . json_encode($postData));

                            $response = $client->request('POST', $this->webhookConfig->getWebhookUrl('webhook/thongbaodon'), [
                                'headers' => [
                                    'Content-Type' => 'application/x-www-form-urlencoded'
                                ],
                                'form_params' => $postData
                            ]);

                            log_message('debug', 'Notification response: ' . $response->getBody());
                        } catch (\Exception $e) {
                            log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
                            $success = false;
                        }
                    }
                    return $success;
                }
            }
            return false;
        } catch (\Exception $e) {
            log_message('error', 'Lỗi trong quá trình gửi thông báo: ' . $e->getMessage());
            return false;
        }
    }

    public function updateVietnamStockDateUI()
    {
        // Lấy tracking_code từ dữ liệu POST
        $trackingCode = $this->request->getPost('tracking_code');

        // Kiểm tra tracking_code có hợp lệ không
        if (empty($trackingCode)) {
            return '<div class="alert alert-danger">Mã vận chuyển không hợp lệ.</div>';
        }

        // Tìm đơn hàng theo tracking_code với thông tin khách hàng và khách hàng phụ
        $order = $this->orderModel
            ->select('orders.*, 
                customers.customer_code, 
                customers.fullname, 
                customers.id as customer_id,
                sub_customers.sub_customer_code,
                sub_customers.fullname as sub_customer_name,
                sub_customers.thread_id_zalo_notify_order as sub_customer_thread_id,
                sub_customers.msg_zalo_type_notify_order as sub_customer_msg_type')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$order) {
            return '<div class="alert alert-warning">Mã vận đơn <strong>' . htmlspecialchars($trackingCode) . '</strong> chưa có trong hệ thống.</div>';
        }

        // Chỉ cập nhật nếu vietnam_stock_date còn trống
        $update = $this->orderModel
            ->where('tracking_code', $trackingCode)
            ->where('vietnam_stock_date IS NULL')
            ->set('vietnam_stock_date', date('Y-m-d H:i:s'))
            ->update();

        // Tạo lịch sử trạng thái
        $statusHistory = [];
        if ($order['created_at']) {
            $statusHistory[] = [
                'time' => $order['created_at'],
                'status' => 'Nhập kho Trung Quốc'
            ];
        }

        if ($update && $this->orderModel->affectedRows() > 0) {
            $statusHistory[] = [
                'time' => date('Y-m-d H:i:s'),
                'status' => 'Nhập kho Việt Nam'
            ];

            // Gửi thông báo
            $this->notifyOrder($order['id']);

            return view('orders/vncheck_result', [
                'status' => 'in_vn',
                'order' => $order,
                'statusHistory' => $statusHistory,
                'trackingCode' => $trackingCode
            ]);
        } else {
            if ($order['vietnam_stock_date']) {
                $statusHistory[] = [
                    'time' => $order['vietnam_stock_date'],
                    'status' => 'Nhập kho Việt Nam'
                ];
                return view('orders/vncheck_result', [
                    'status' => 'in_vn',
                    'order' => $order,
                    'statusHistory' => $statusHistory,
                    'trackingCode' => $trackingCode
                ]);
            }
            return '<div class="alert alert-info">Không có bản ghi nào cần cập nhật (ngày nhập kho đã tồn tại).</div>';
        }
    }

    public function exportExcelByFilter()
    {
        try {
            // Lấy thông tin tìm kiếm từ GET
            $filters = [
                'tracking_code' => $this->request->getGet('tracking_code'),
                'customer_code' => $this->request->getGet('customer_code') ?? 'ALL',
                'from_date' => $this->request->getGet('from_date'),
                'to_date' => $this->request->getGet('to_date'),
                'shipping_status' => $this->request->getGet('shipping_status') ?? 'ALL',
                'order_code' => $this->request->getGet('order_code'),
                'sub_customer_id' => $this->request->getGet('sub_customer_id') ?? 'ALL',
                'export_type' => $this->request->getGet('export_type') ?? 'recent_1000' // recent_1000, all
            ];

            // Cấu hình query cho danh sách đơn hàng
            $query = $this->orderModel
                ->select('orders.*, 
                  customers.fullname AS customer_name, 
                  customers.customer_code AS customer_code, 
                  product_types.name AS product_type_name, 
                  i.shipping_confirmed_at, 
                  i.id AS invoice_id,
                  sub_customers.sub_customer_code')
                ->join('customers', 'customers.id = orders.customer_id', 'left')
                ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
                ->join('invoices i', 'i.id = orders.invoice_id', 'left')
                ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
                ->orderBy('orders.id', 'DESC');

            // Thêm điều kiện tìm kiếm cho danh sách
            if (!empty($filters['tracking_code'])) {
                $query->like('orders.tracking_code', $filters['tracking_code']);
            }

            if (!empty($filters['order_code'])) {
                $query->like('orders.order_code', $filters['order_code']);
            }

            if (!empty($filters['customer_code']) && $filters['customer_code'] !== 'ALL') {
                $query->where('customers.customer_code', $filters['customer_code']);

                // Kiểm tra nếu có lọc theo mã phụ
                if ($filters['sub_customer_id'] !== 'ALL') {
                    if ($filters['sub_customer_id'] === 'NONE') {
                        // Lọc các đơn hàng không có mã phụ
                        $query->where('orders.sub_customer_id IS NULL');
                    } else {
                        // Lọc theo mã phụ cụ thể
                        $query->where('orders.sub_customer_id', $filters['sub_customer_id']);
                    }
                }
            }

            if (!empty($filters['from_date'])) {
                $query->where('orders.created_at >=', $filters['from_date'] . ' 00:00:00');
            }

            if (!empty($filters['to_date'])) {
                $query->where('orders.created_at <=', $filters['to_date'] . ' 23:59:59');
            }

            // Thêm điều kiện lọc theo trạng thái giao hàng
            if (!empty($filters['shipping_status']) && $filters['shipping_status'] !== 'ALL') {
                switch ($filters['shipping_status']) {
                    case 'china_stock':
                        $query->where('orders.vietnam_stock_date IS NULL');
                        break;
                    case 'in_stock':
                        $query->where('orders.vietnam_stock_date IS NOT NULL')
                            ->where('orders.invoice_id IS NULL');
                        break;
                    case 'pending_shipping':
                        $query->where('orders.invoice_id IS NOT NULL')
                            ->where('i.shipping_confirmed_at IS NULL');
                        break;
                    case 'shipped':
                        $query->where('i.shipping_confirmed_at IS NOT NULL');
                        break;
                }
            }

            // Giới hạn số lượng bản ghi dựa trên export_type
            if ($filters['export_type'] === 'recent_1000') {
                $query->limit(1000);
            }

            // Lấy dữ liệu
            $orders = $query->findAll();

            if (empty($orders)) {
                session()->setFlashdata('error', 'Không có đơn hàng nào để xuất Excel.');
                return redirect()->to('/orders');
            }

            // Tạo file Excel bằng PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Đặt tiêu đề các cột
            $headers = [
                'ID',
                'Nhập TQ',
                'Nhập VN',
                'Mã vận chuyển',
                'Mã lô',
                'Mã bao',
                'Khách hàng',
                'Mã phụ', // Đưa mã phụ sau mã khách hàng
                'Hàng',
                'SL',
                'Số kg',
                'Dài',
                'Rộng',
                'Cao',
                'Khối',
                'Giá kg',
                'Giá Khối',
                'Phí tệ',
                'Tỷ giá',
                'Phí TQ',
                'Phí Chính ngạch',
                'Thuế VAT',
                'Thuế NK',
                'Thuế khác',
                'Cước vận chuyển',
                'Tổng',
                'TT',
                'Trạng thái'
            ];
            $sheet->fromArray($headers, null, 'A1');

            // Thêm dữ liệu
            $row = 2;
            foreach ($orders as $order) {
                $gia_theo_cannang = $order['total_weight'] * $order['price_per_kg'];
                $gia_theo_khoi = $order['volume'] * $order['price_per_cubic_meter'];
                $gia_cuoi_cung = max($gia_theo_cannang, $gia_theo_khoi); // Lấy giá trị lớn hơn
                $cach_tinh_gia = ($gia_theo_khoi > $gia_theo_cannang) ? 'TT' : 'KG';
                $gianoidia_trung = $order['domestic_fee'] * $order['exchange_rate'];

                $status = $order['vietnam_stock_date'] === null ? 'Kho TQ' : ($order['invoice_id'] === null ? 'Tồn kho' : ($order['shipping_confirmed_at'] !== null ? 'Đã giao' : 'Chờ giao'));

                $sheet->fromArray([
                    $order['id'],
                    date('Y-m-d', strtotime($order['created_at'])),
                    $order['vietnam_stock_date'] ? date('Y-m-d', strtotime($order['vietnam_stock_date'])) : '',
                    null, // Mã vận chuyển
                    $order['order_code'],
                    $order['package_code'],
                    $order['customer_code'],
                    $order['sub_customer_code'] ?? '', // Mã phụ ngay sau mã khách hàng
                    $order['product_type_name'],
                    $order['quantity'],
                    $order['total_weight'],
                    $order['length'],
                    $order['width'],
                    $order['height'],
                    $order['volume'],
                    $order['price_per_kg'],
                    $order['price_per_cubic_meter'],
                    $order['domestic_fee'],
                    $order['exchange_rate'],
                    $gianoidia_trung,
                    $order['official_quota_fee'],
                    $order['vat_tax'],
                    $order['import_tax'],
                    $order['other_tax'],
                    $gia_cuoi_cung,
                    $gianoidia_trung + $gia_cuoi_cung + $order['official_quota_fee'] + $order['vat_tax'] + $order['import_tax'] + $order['other_tax'],
                    $cach_tinh_gia,
                    $status
                ], null, "A$row");

                // Ghi giá trị tracking_code bằng setValueExplicit để đảm bảo là text
                if (!empty($order['tracking_code'])) {
                    $sheet->getCell('D' . $row)
                        ->setValueExplicit(
                            $order['tracking_code'],
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                }

                // Định dạng cột "Mã vận chuyển" (cột D) là Text
                $sheet->getCell('D' . $row)->getStyle()->getNumberFormat()->setFormatCode('@');

                $row++;
            }

            // Định dạng toàn bộ cột "Mã vận chuyển" (cột D) là Text
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('@');

            // Định dạng cột "Khối" (cột O) để hiển thị 3 chữ số sau dấu phẩy
            $sheet->getStyle('O2:O' . $lastRow)
                ->getNumberFormat()
                ->setFormatCode('0.000');

            // Tự động điều chỉnh độ rộng cột
            foreach (range('A', 'Z') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Tạo tên file dựa trên bộ lọc
            $filename = 'DonHang_' . date('Y-m-d_H-i-s');
            if ($filters['customer_code'] !== 'ALL') {
                $filename .= '_' . $filters['customer_code'];
            }
            if ($filters['export_type'] === 'recent_1000') {
                $filename .= '_1000gannhat';
            } else {
                $filename .= '_tatca';
            }
            $filename .= '.xlsx';

            // Tạo file Excel và tải xuống
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit();
        } catch (\Exception $e) {
            log_message('error', 'Lỗi export Excel: ' . $e->getMessage());
            session()->setFlashdata('error', 'Có lỗi xảy ra khi xuất Excel: ' . $e->getMessage());
            return redirect()->to('/orders');
        }
    }

    public function getOrderCount()
    {
        try {
            // Lấy thông tin tìm kiếm từ GET
            $filters = [
                'tracking_code' => $this->request->getGet('tracking_code'),
                'customer_code' => $this->request->getGet('customer_code') ?? 'ALL',
                'from_date' => $this->request->getGet('from_date'),
                'to_date' => $this->request->getGet('to_date'),
                'shipping_status' => $this->request->getGet('shipping_status') ?? 'ALL',
                'order_code' => $this->request->getGet('order_code'),
                'sub_customer_id' => $this->request->getGet('sub_customer_id') ?? 'ALL'
            ];

            // Cấu hình query để đếm số lượng
            $query = $this->orderModel
                ->select('COUNT(*) as total_count')
                ->join('customers', 'customers.id = orders.customer_id', 'left')
                ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
                ->join('invoices i', 'i.id = orders.invoice_id', 'left')
                ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left');

            // Thêm điều kiện tìm kiếm
            if (!empty($filters['tracking_code'])) {
                $query->like('orders.tracking_code', $filters['tracking_code']);
            }

            if (!empty($filters['order_code'])) {
                $query->like('orders.order_code', $filters['order_code']);
            }

            if (!empty($filters['customer_code']) && $filters['customer_code'] !== 'ALL') {
                $query->where('customers.customer_code', $filters['customer_code']);

                if ($filters['sub_customer_id'] !== 'ALL') {
                    if ($filters['sub_customer_id'] === 'NONE') {
                        $query->where('orders.sub_customer_id IS NULL');
                    } else {
                        $query->where('orders.sub_customer_id', $filters['sub_customer_id']);
                    }
                }
            }

            if (!empty($filters['from_date'])) {
                $query->where('orders.created_at >=', $filters['from_date'] . ' 00:00:00');
            }

            if (!empty($filters['to_date'])) {
                $query->where('orders.created_at <=', $filters['to_date'] . ' 23:59:59');
            }

            if (!empty($filters['shipping_status']) && $filters['shipping_status'] !== 'ALL') {
                switch ($filters['shipping_status']) {
                    case 'china_stock':
                        $query->where('orders.vietnam_stock_date IS NULL');
                        break;
                    case 'in_stock':
                        $query->where('orders.vietnam_stock_date IS NOT NULL')
                            ->where('orders.invoice_id IS NULL');
                        break;
                    case 'pending_shipping':
                        $query->where('orders.invoice_id IS NOT NULL')
                            ->where('i.shipping_confirmed_at IS NULL');
                        break;
                    case 'shipped':
                        $query->where('i.shipping_confirmed_at IS NOT NULL');
                        break;
                }
            }

            $result = $query->first();
            $totalCount = $result['total_count'] ?? 0;

            return $this->response->setJSON([
                'success' => true,
                'total_count' => $totalCount
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Lỗi đếm số lượng đơn hàng: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function zeroPrice()
    {
        $perPage = 20;
        // Lấy thông tin tìm kiếm từ GET
        $filters = [
            'tracking_code' => $this->request->getGet('tracking_code'),
            'customer_code' => $this->request->getGet('customer_code') ?? 'ALL',
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
            'shipping_status' => $this->request->getGet('shipping_status') ?? 'ALL',
            'order_code' => $this->request->getGet('order_code')
        ];
        $subCustomerId = $this->request->getGet('sub_customer_id') ?? 'ALL';

        // Cấu hình query cho danh sách đơn hàng chưa có giá
        $query = $this->orderModel
            ->select('orders.*, customers.customer_code, customers.fullname as customer_name, product_types.name as product_type_name, sub_customers.sub_customer_code, i.shipping_confirmed_at, i.id AS invoice_id')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->where('orders.price_per_kg', 0)
            ->where('orders.price_per_cubic_meter', 0);

        if (!empty($filters['tracking_code'])) {
            $query->like('orders.tracking_code', $filters['tracking_code']);
        }
        if (!empty($filters['order_code'])) {
            $query->like('orders.order_code', $filters['order_code']);
        }
        if (!empty($filters['customer_code']) && $filters['customer_code'] !== 'ALL') {
            $query->where('customers.customer_code', $filters['customer_code']);
            if ($subCustomerId !== 'ALL') {
                if ($subCustomerId === 'NONE') {
                    $query->where('orders.sub_customer_id IS NULL');
                } else {
                    $query->where('orders.sub_customer_id', $subCustomerId);
                }
            }
        }
        if (!empty($filters['from_date'])) {
            $query->where('orders.created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $query->where('orders.created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        if (!empty($filters['shipping_status']) && $filters['shipping_status'] !== 'ALL') {
            switch ($filters['shipping_status']) {
                case 'china_stock':
                    $query->where('orders.vietnam_stock_date IS NULL');
                    break;
                case 'in_stock':
                    $query->where('orders.vietnam_stock_date IS NOT NULL')
                        ->where('orders.invoice_id IS NULL');
                    break;
                case 'pending_shipping':
                    $query->where('orders.invoice_id IS NOT NULL')
                        ->where('i.shipping_confirmed_at IS NULL');
                    break;
                case 'shipped':
                    $query->where('i.shipping_confirmed_at IS NOT NULL');
                    break;
            }
        }
        $orders = $query->orderBy('orders.created_at', 'DESC')->paginate($perPage);
        $pager = $this->orderModel->pager;

        // Lấy danh sách khách hàng để hiển thị dropdown
        $customerModel = new \App\Models\CustomerModel();
        $customers = $customerModel->select('customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();
        // Lấy danh sách mã lô để hiển thị dropdown
        $orderCodes = $this->orderModel->distinct()
            ->select('order_code')
            ->where('order_code IS NOT NULL')
            ->where('order_code !=', '')
            ->orderBy('order_code', 'ASC')
            ->findAll();
        $order_codes = array_column($orderCodes, 'order_code');

        $totalOrders = $this->orderModel
            ->where('price_per_kg', 0)
            ->where('price_per_cubic_meter', 0)
            ->countAllResults();

        $data = [
            'title' => 'Đơn hàng chưa có giá',
            'orders' => $orders,
            'pager' => $pager,
            'stats' => [
                'total' => $totalOrders,
            ],
            'customers' => $customers,
            'order_codes' => $order_codes,
            'tracking_code' => $filters['tracking_code'],
            'customer_code' => $filters['customer_code'],
            'from_date' => $filters['from_date'],
            'to_date' => $filters['to_date'],
            'sub_customer_id' => $subCustomerId,
            'shipping_status' => $filters['shipping_status'],
            'order_code' => $filters['order_code'],
        ];
        return view('orders/zero_price', $data);
    }
}
