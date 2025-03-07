<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\CustomerModel;
use App\Models\ProductTypeModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderController extends BaseController
{
    protected $orderModel, $customerModel, $productTypeModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
        $this->productTypeModel = new ProductTypeModel();
    }

    // Trong phương thức index()
    public function index()
    {
        $perPage = 30; // Số lượng bản ghi mỗi trang

        // Lấy thông tin tìm kiếm từ GET
        $trackingCode = $this->request->getGet('tracking_code') ?? '';
        $customerCode = $this->request->getGet('customer_code') ?? 'ALL';
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $currentPage = $this->request->getGet('page') ?? 1;

        // Cấu hình query
        $query = $this->orderModel
            ->select('orders.*, 
              customers.fullname AS customer_name, 
              customers.customer_code AS customer_code, 
              product_types.name AS product_type_name, 
              invoices.shipping_status AS invoice_shipping_status, 
              invoices.id AS invoice_id')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left') // Liên kết với invoices
            ->orderBy('orders.id', 'DESC');

        // Thêm điều kiện tìm kiếm
        if (!empty($trackingCode)) {
            $query->like('orders.tracking_code', $trackingCode);
        }

        if (!empty($customerCode) && $customerCode !== 'ALL') {
            $query->where('customers.customer_code', $customerCode);
        }

        if (!empty($fromDate)) {
            $query->where('orders.created_at >=', $fromDate . ' 00:00:00');
        }

        if (!empty($toDate)) {
            $query->where('orders.created_at <=', $toDate . ' 23:59:59');
        }

        // Lấy dữ liệu phân trang
        $data['orders'] = $query->paginate($perPage);
        $data['pager'] = $this->orderModel->pager;

        // Lấy danh sách khách hàng để hiển thị dropdown
        $customerModel = new \App\Models\CustomerModel();
        $data['customers'] = $customerModel->select('customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();

        // Truyền giá trị tìm kiếm vào View
        $data['tracking_code'] = $trackingCode;
        $data['customer_code'] = $customerCode;
        $data['from_date'] = $fromDate;
        $data['to_date'] = $toDate;

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


    public function delete($id)
    {
        $this->orderModel->delete($id);
        return redirect()->to('/orders');
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

        // Cập nhật trường vietnam_stock_date
        $update = $this->orderModel->update($id, [
            'vietnam_stock_date' => date('Y-m-d H:i:s') // CURRENT_TIMESTAMP
        ]);

        // Kiểm tra kết quả
        if ($update) {
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
        $trackingCode = $input['tracking_code'] ?? null;

        // Kiểm tra tracking_code có hợp lệ không
        if (empty($trackingCode)) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Mã vận chuyển không hợp lệ.',
                'tracking_code' => $trackingCode
            ]);
        }


        // Chỉ cập nhật khi `vietnam_stock_date` là NULL
        $update = $this->orderModel
            ->where('tracking_code', $trackingCode)
            ->where('vietnam_stock_date IS NULL') // Điều kiện: Chỉ cập nhật nếu `vietnam_stock_date` đang trống
            ->set('vietnam_stock_date', date('Y-m-d H:i:s')) // CURRENT_TIMESTAMP
            ->update();

        // Kiểm tra kết quả
        if ($update) {
            if ($this->orderModel->affectedRows() > 0) {
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
        $order = $this->orderModel
            ->select('orders.*, invoices.status as invoice_status')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left')
            ->find($id);

        if (!$order) {
            return redirect()->to('/orders')->with('error', 'Đơn hàng không tồn tại.');
        }

        // Kiểm tra trạng thái phiếu xuất
        if (in_array($order['invoice_status'], ['approved', 'completed'])) {
            return redirect()->to('/orders')->with('error', 'Không thể sửa đơn hàng đã được phê duyệt.');
        }

        $data = [
            'order' => $order,
            'customers' => $this->customerModel->findAll(),
            'productTypes' => $this->productTypeModel->findAll(),
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
                return redirect()->to('/orders')->with('error', 'Không thể sửa đơn hàng vì phiếu xuất đã được thanh toán.');
            }
        }

        // Lấy dữ liệu từ form
        $data = [
            'customer_id' => $this->request->getPost('customer_id'),
            'product_type_id' => $this->request->getPost('product_type_id'),
            'quantity' => $this->request->getPost('quantity'),
            'package_code' => $this->request->getPost('package_code'),
            'total_weight' => $this->request->getPost('total_weight'),
            'price_per_kg' => $this->request->getPost('price_per_kg'),
            'price_per_cubic_meter' => $this->request->getPost('price_per_cubic_meter'),
        ];

        // Cập nhật thông tin
        $this->orderModel->update($id, $data);

        return redirect()->to('/orders')->with('success', 'Đơn hàng đã được cập nhật.');
    }




    /**
     * Xuất danh sách đơn hàng ra file Excel
     */
    public function exportToExcel()
    {



        // Lấy bộ lọc từ request
        $trackingCode = $this->request->getGet('tracking_code') ?? '';
        $customerCode = $this->request->getGet('customer_code') ?? 'ALL';
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');

        // Cấu hình query với bộ lọc
        $query = $this->orderModel
            ->select('orders.*, customers.fullname AS customer_name, customers.customer_code AS customer_code, product_types.name AS product_type_name')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left');

        if (!empty($trackingCode)) {
            $query->like('orders.tracking_code', $trackingCode);
        }

        if (!empty($customerCode) && $customerCode !== 'ALL') {
            $query->where('customers.customer_code', $customerCode);
        }

        if (!empty($fromDate)) {
            $query->where('orders.created_at >=', $fromDate . ' 00:00:00');
        }

        if (!empty($toDate)) {
            $query->where('orders.created_at <=', $toDate . ' 23:59:59');
        }

        $orders = $query->findAll();

        // Khởi tạo Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tiêu đề cột
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Mã vận chuyển');
        $sheet->setCellValue('C1', 'Khách hàng');
        $sheet->setCellValue('D1', 'Loại hàng');
        $sheet->setCellValue('E1', 'Số lượng');
        $sheet->setCellValue('F1', 'Cân nặng (kg)');
        $sheet->setCellValue('G1', 'Ngày tạo');

        // Dữ liệu đơn hàng
        $row = 2;
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order['id']);
            $sheet->setCellValue('B' . $row, $order['tracking_code']);
            $sheet->setCellValue('C' . $row, $order['customer_name'] . ' (' . $order['customer_code'] . ')');
            $sheet->setCellValue('D' . $row, $order['product_type_name']);
            $sheet->setCellValue('E' . $row, $order['quantity']);
            $sheet->setCellValue('F' . $row, $order['total_weight']);
            $sheet->setCellValue('G' . $row, date('d-m-Y', strtotime($order['created_at'])));
            $row++;
        }

        // Xuất file Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Danh_sach_don_hang_' . date('Ymd_His') . '.xlsx';

        // Gửi file về client
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
