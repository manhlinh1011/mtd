<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\InvoiceModel;
use CodeIgniter\Controller;

class TrackingController extends Controller
{
    public function index()
    {
        return view('tracking');
    }

    public function check()
    {
        $trackingCode = trim($this->request->getGet('tracking_code'));

        if (!$trackingCode) {
            return view('tracking', ['error' => 'Vui lòng nhập mã vận đơn']);
        }

        $orderModel = new OrderModel();
        $invoiceModel = new InvoiceModel();

        // Lấy thông tin đơn hàng theo mã tracking_code
        $order = $orderModel
            ->select('orders.*, customers.customer_code')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->like('orders.tracking_code', $trackingCode, 'after')
            ->first();

        if (!$order) {
            return view('tracking', ['error' => 'Không tìm thấy đơn hàng với mã này']);
        }

        $trackingCode = $order['tracking_code'];
        // Lịch sử trạng thái đơn hàng (luôn theo thứ tự cố định)
        $statusHistory = [];

        // 1. Nhập kho Trung Quốc (LUÔN LUÔN ĐẦU TIÊN)
        if ($order['created_at']) {
            $statusHistory[] = [
                'time' => $order['created_at'],
                'status' => 'Nhập kho Trung Quốc'
            ];
        }

        // 2. Nhập kho Việt Nam
        if ($order['vietnam_stock_date']) {
            $statusHistory[] = [
                'time' => $order['vietnam_stock_date'],
                'status' => 'Nhập kho Việt Nam'
            ];
        }

        // 3. Đã tạo phiếu xuất (Có trong `invoices`)
        $invoice = $invoiceModel->where('id', $order['invoice_id'])->first();
        if ($invoice) {
            $statusHistory[] = [
                'time' => $invoice['created_at'],
                'status' => 'Đã tạo phiếu xuất'
            ];

            // 4. Đã xuất hàng (shipping_status = confirmed)
            if ($invoice['shipping_status'] == 'confirmed') {
                $statusHistory[] = [
                    'time' => $invoice['created_at'], // ✅ Sửa lỗi: lấy `created_at`
                    'status' => 'Đã xuất hàng'
                ];
            }
        }

        $weight = $order['total_weight'];
        $volume = $order['volume'];
        $domestic_fee = $order['domestic_fee'];
        $customer_code = $order['customer_code'] ?? null;

        return view('tracking', [
            'trackingCode' => $trackingCode,
            'statusHistory' => $statusHistory,
            'weight' => $weight,
            'volume' => $volume,
            'domestic_fee' => $domestic_fee,
            'customer_code' => $customer_code
        ]);
    }
}
