<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\InvoiceModel;
use App\Models\InvoiceOrderModel;
use App\Models\InvoicePaymentModel;

class InvoiceController extends BaseController
{
    protected $invoicePaymentModel;

    public function __construct()
    {
        $this->invoicePaymentModel = new InvoicePaymentModel();
    }

    public function cart()
    {
        $cart = session()->get('cart') ?? [];

        if (!is_array($cart) || empty($cart)) {
            return view('invoices/cart', ['customerCart' => []]);
        }

        // Lấy thông tin chi tiết của các orders từ DB
        $orderModel = new \App\Models\OrderModel();
        $orders = $orderModel
            ->select('
            orders.id as order_id,
            orders.tracking_code,
            orders.order_code,
            customers.id as customer_id,
            customers.customer_code,
            customers.fullname as customer_name,
            orders.quantity,
            orders.total_weight,
            orders.length,
            orders.width,
            orders.height,
            orders.volume,
            orders.price_per_kg,
            orders.price_per_cubic_meter,
            orders.domestic_fee,
            orders.exchange_rate,
            orders.export_date,
            orders.created_at,
            orders.vietnam_stock_date
        ')
            ->join('customers', 'orders.customer_id = customers.id')
            ->whereIn('orders.id', $cart)
            ->findAll();

        // Tổ chức dữ liệu theo khách hàng
        $customerCart = [];
        foreach ($orders as $order) {
            // Nếu chưa có khách hàng trong danh sách, khởi tạo
            if (!isset($customerCart[$order['customer_id']])) {
                $customerCart[$order['customer_id']] = [
                    'customer_name' => $order['customer_name'],
                    'customer_code' => $order['customer_code'],
                    'orders' => []
                ];
            }

            // Tính toán chi phí và các thuộc tính liên quan đến đơn hàng
            $price_by_weight = $order['total_weight'] * $order['price_per_kg'];
            $price_by_volume = $order['volume'] * $order['price_per_cubic_meter'];
            $final_price = max($price_by_weight, $price_by_volume);
            $pricing_method = ($final_price == $price_by_weight) ? 'By Weight' : 'By Volume';

            $total_domestic_fee = $order['domestic_fee'] * $order['exchange_rate'];
            $total_price = $final_price + $total_domestic_fee;

            // Thêm thông tin đơn hàng vào danh sách của khách hàng
            $customerCart[$order['customer_id']]['orders'][] = [
                'order_id' => $order['order_id'],
                'tracking_code' => $order['tracking_code'],
                'order_code' => $order['order_code'],
                'quantity' => $order['quantity'],
                'total_weight' => $order['total_weight'],
                'dimensions' => "{$order['length']}x{$order['width']}x{$order['height']}",
                'volume' => $order['volume'],
                'price_per_kg' => $order['price_per_kg'],
                'price_per_cubic_meter' => $order['price_per_cubic_meter'],
                'domestic_fee' => $order['domestic_fee'],
                'exchange_rate' => $order['exchange_rate'],
                'export_date' => $order['export_date'],
                'created_at' => $order['created_at'],
                'vietnam_stock_date' => $order['vietnam_stock_date'],
                'price_by_weight' => $price_by_weight,
                'price_by_volume' => $price_by_volume,
                'final_price' => $final_price,
                'pricing_method' => $pricing_method,
                'total_domestic_fee' => $total_domestic_fee,
                'total_price' => $total_price
            ];
        }

        // Trả về view với dữ liệu đã tổ chức
        return view('invoices/cart', ['customerCart' => $customerCart]);
    }




    // Trong phương thức index()
    public function index()
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $paymentModel = new \App\Models\InvoicePaymentModel();
        $orderModel = new \App\Models\OrderModel(); // Thêm model OrderModel để tính total_amount và tìm theo tracking_code

        $perPage = 30; // Số lượng bản ghi mỗi trang, giống như OrderController

        // Lấy thông tin tìm kiếm từ GET
        $invoiceId = $this->request->getGet('invoice_id') ?? ''; // Thêm bộ lọc theo mã phiếu xuất (id)
        $trackingCode = $this->request->getGet('tracking_code') ?? ''; // Thêm bộ lọc theo mã vận chuyển
        $customerCode = $this->request->getGet('customer_code') ?? 'ALL';
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $currentPage = $this->request->getGet('page') ?? 1;

        // Cấu hình query
        $query = $invoiceModel
            ->select('invoices.*, customers.customer_code, customers.fullname, COUNT(orders.id) as order_count')
            ->join('customers', 'invoices.customer_id = customers.id', 'left')
            ->join('orders', 'invoices.id = orders.invoice_id', 'left')
            ->groupBy('invoices.id')
            ->orderBy('invoices.created_at', 'DESC');

        // Thêm điều kiện tìm kiếm
        if (!empty($invoiceId)) {
            $query->where('invoices.id', $invoiceId); // Tìm theo mã phiếu xuất (id)
        }

        if (!empty($trackingCode)) {
            // Tìm các invoice chứa order có tracking_code cụ thể
            $query->whereIn('invoices.id', function ($builder) use ($trackingCode) {
                $builder->select('invoice_id')
                    ->from('orders')
                    ->where('tracking_code', $trackingCode);
            });
        }

        if (!empty($customerCode) && $customerCode !== 'ALL') {
            $query->where('customers.customer_code', $customerCode);
        }

        if (!empty($fromDate)) {
            $query->where('invoices.created_at >=', $fromDate . ' 00:00:00');
        }

        if (!empty($toDate)) {
            $query->where('invoices.created_at <=', $toDate . ' 23:59:59');
        }

        // Lấy dữ liệu phân trang
        $invoices = $query->paginate($perPage, 'default', $currentPage);
        $data['pager'] = $invoiceModel->pager;

        // Tính total_amount và trạng thái thanh toán động cho mỗi invoice, giống detail.php
        foreach ($invoices as &$invoice) {
            // Tính total_amount dựa trên orders và shipping_fee, giống detail.php
            $orders = $orderModel->where('invoice_id', $invoice['id'])->findAll();
            $total = 0;
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
                $total += $finalPrice;
            }
            $totalAmount = $total + (int)($invoice['shipping_fee'] ?? 0); // Ép kiểu int cho shipping_fee

            $totalPaid = (float)$paymentModel->getTotalPaidByInvoice($invoice['id']); // Ép kiểu float

            // Debug để kiểm tra giá trị
            log_message('debug', "Invoice ID: {$invoice['id']}, Total Paid: {$totalPaid}, Total Amount: {$totalAmount}");

            // Tính trạng thái thanh toán động giống như trong detail.php
            $paymentStatus = 'Chưa thanh toán';
            if ($totalPaid > 0 && $totalPaid < $totalAmount) {
                $paymentStatus = 'Thanh toán một phần';
            } elseif ($totalPaid >= $totalAmount) {
                $paymentStatus = 'Đã thanh toán';
            }

            $invoice['total_paid'] = $totalPaid;
            $invoice['payment_status_dynamic'] = $paymentStatus;
            $invoice['total_amount'] = $totalAmount; // Gán total_amount tính toán động
        }

        // Lấy danh sách khách hàng để hiển thị dropdown
        $customerModel = new \App\Models\CustomerModel();
        $data['customers'] = $customerModel->select('customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();

        // Truyền dữ liệu vào View
        $data['invoices'] = $invoices;
        $data['invoice_id'] = $invoiceId; // Truyền giá trị tìm kiếm mã phiếu xuất
        $data['tracking_code'] = $trackingCode; // Truyền giá trị tìm kiếm mã vận chuyển
        $data['customer_code'] = $customerCode;
        $data['from_date'] = $fromDate;
        $data['to_date'] = $toDate;

        return view('invoices/list', $data);
    }





    public function addOrderToCart()
    {
        $orderId = $this->request->getPost('order_id');
        $customerId = $this->request->getPost('customer_id');
        $customerName = $this->request->getPost('customer_name');
        $customerCode = $this->request->getPost('customer_code');

        // Tạo giỏ hàng session nếu chưa có
        $cart = session()->get('cart') ?? [];

        // Tạo session cho customer nếu chưa có
        if (!isset($cart["customer_{$customerId}"])) {
            $cart["customer_{$customerId}"] = [
                'customer_name' => $customerName,
                'customer_code' => $customerCode,
                'orders' => []
            ];
        }

        // Thêm order vào session
        $cart["customer_{$customerId}"]['orders'][] = [
            'order_id' => $orderId,
            'order_code' => $this->request->getPost('order_code'),
            'order_date' => $this->request->getPost('order_date'),
            'total_price' => $this->request->getPost('total_price')
        ];

        session()->set('cart', $cart);

        return redirect()->to('/invoices/cart')->with('success', 'Đã thêm đơn hàng vào giỏ');
    }

    public function removeOrderFromCart($orderId)
    {
        $cart = session()->get('cart') ?? [];

        // Loại bỏ order_id khỏi giỏ hàng
        $cart = array_filter($cart, fn($id) => $id != $orderId);

        // Cập nhật lại session
        session()->set('cart', array_values($cart)); // Đảm bảo mảng được đánh lại chỉ số

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Đã xóa đơn hàng khỏi giỏ hàng.'
        ]);
    }


    public function create($customerId)
    {
        // Lấy danh sách các order từ session
        $cart = session()->get('cart') ?? [];

        // Lọc các order thuộc về customer_id hiện tại
        $orderIds = array_filter($cart, function ($orderId) use ($customerId) {
            $order = (new \App\Models\OrderModel())->find($orderId);
            return $order && $order['customer_id'] == $customerId;
        });

        if (empty($orderIds)) {
            return redirect()->to('/invoices/cart')->with('error', 'Không có đơn hàng nào trong giỏ cho khách hàng này.');
        }

        // Lấy thông tin chi tiết của các orders từ DB
        $orderModel = new \App\Models\OrderModel();
        $orders = $orderModel
            ->select('
            orders.id as order_id,
            orders.tracking_code,
            orders.order_code,
            customers.id as customer_id,
            customers.customer_code,
            customers.fullname as customer_name,
            orders.quantity,
            orders.total_weight,
            orders.length,
            orders.width,
            orders.height,
            orders.volume,
            orders.price_per_kg,
            orders.price_per_cubic_meter,
            orders.domestic_fee,
            orders.exchange_rate,
            orders.export_date,
            orders.created_at,
            orders.vietnam_stock_date
        ')
            ->join('customers', 'orders.customer_id = customers.id', 'left')
            ->whereIn('orders.id', $orderIds)
            ->findAll();

        if (empty($orders)) {
            return redirect()->to('/invoices/cart')->with('error', 'Không có đơn hàng nào hợp lệ để tạo hóa đơn.');
        }

        // Lấy thông tin khách hàng từ bảng customers
        $customer = (new \App\Models\CustomerModel())->find($customerId);

        if (!$customer) {
            return redirect()->to('/invoices/cart')->with('error', 'Không tìm thấy khách hàng này.');
        }

        return view('invoices/create', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }




    public function store($customerId)
    {
        $orderIds = explode(',', $this->request->getPost('order_ids'));
        $shippingFee = $this->request->getPost('shipping_fee');
        $otherFee = $this->request->getPost('other_fee');

        // Lấy giỏ hàng từ session
        $cart = session()->get('cart') ?? [];

        // Kiểm tra nếu giỏ hàng rỗng hoặc không có đơn hàng nào
        if (empty($cart)) {
            return redirect()->back()->with('modal_error', 'Giỏ hàng trống. Vui lòng thêm đơn hàng trước khi tạo phiếu xuất.');
        }

        // Kiểm tra nếu orderIds rỗng hoặc không khớp với cart
        if (empty($orderIds) || count($orderIds) === 0) {
            return redirect()->back()->with('modal_error', 'Không có đơn hàng nào để tạo phiếu xuất.');
        }

        // Lọc các orderIds có trong cart
        $validOrderIds = array_intersect($orderIds, $cart);
        if (empty($validOrderIds)) {
            return redirect()->back()->with('modal_error', 'Không có đơn hàng hợp lệ trong giỏ hàng để tạo phiếu xuất.');
        }

        // Kiểm tra xem các đơn hàng đã thuộc phiếu xuất nào chưa
        $orderModel = new \App\Models\OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $orders = $orderModel->whereIn('id', $validOrderIds)->findAll();

        foreach ($orders as $order) {
            if (!empty($order['invoice_id'])) {
                $existingInvoice = $invoiceModel->find($order['invoice_id']);
                if ($existingInvoice) {
                    $invoiceLink = base_url("/invoices/detail/" . $existingInvoice['id']);
                    return redirect()->back()->with('modal_error', "Đơn hàng <strong>{$order['tracking_code']}</strong> đã thuộc phiếu xuất <a href='{$invoiceLink}'>#{$existingInvoice['id']}</a>.");
                }
            }
        }

        // Tính tổng tiền từ orders
        $total = 0;
        foreach ($orders as $order) {
            $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
            $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
            $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
            $total += $finalPrice;
        }
        $totalAmount = $total + (float)$shippingFee + (float)$otherFee;

        // Tạo invoice
        $invoiceData = [
            'customer_id' => $customerId,
            'created_by' => session()->get('user_id'),
            'shipping_fee' => $shippingFee,
            'other_fee' => $otherFee,
            'total_amount' => $totalAmount,
            'shipping_status' => 'pending'
        ];
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoiceId = $invoiceModel->insert($invoiceData);

        if (!$invoiceId) {
            return redirect()->back()->with('modal_error', 'Tạo phiếu xuất thất bại.');
        }

        // Cập nhật invoice_id và trạng thái cho orders
        $orderModel->whereIn('id', $validOrderIds)->set(['invoice_id' => $invoiceId, 'status' => 'in_stock'])->update();

        // Xóa các đơn hàng đã tạo khỏi cart
        $updatedCart = array_diff($cart, $validOrderIds);
        session()->set('cart', $updatedCart);

        return redirect()->to("/invoices/detail/{$invoiceId}")->with('success', 'Tạo phiếu xuất thành công.');
    }



    public function detail($invoiceId)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();
        $customerModel = new \App\Models\CustomerModel();
        $userModel = new \App\Models\UserModel();
        $paymentModel = new \App\Models\InvoicePaymentModel();

        $invoice = $invoiceModel->find($invoiceId);
        if (!$invoice) {
            return redirect()->to('/invoices')->with('error', 'Phiếu xuất không tồn tại.');
        }

        $customer = $customerModel->find($invoice['customer_id']);
        if (!$customer) {
            return redirect()->to('/invoices')->with('error', 'Khách hàng không tồn tại.');
        }

        $orders = $orderModel
            ->select('orders.*, product_types.name as product_type_name')
            ->join('product_types', 'orders.product_type_id = product_types.id', 'left')
            ->where('orders.invoice_id', $invoiceId)
            ->findAll();

        $creator = $userModel->find($invoice['created_by']);
        $approver = $invoice['approved_by'] ? $userModel->find($invoice['approved_by']) : null;

        $totalPaid = $paymentModel->getTotalPaidByInvoice($invoiceId);

        // Tính lại total_amount với other_fee
        $total = 0;
        foreach ($orders as $order) {
            $gia_theo_cannang = ($order['total_weight'] * $order['price_per_kg']);
            $gia_theo_khoi = ($order['volume'] * $order['price_per_cubic_meter']);
            $gia_cuoi_cung = $gia_theo_cannang;
            if ($gia_theo_khoi > $gia_theo_cannang) {
                $gia_cuoi_cung = $gia_theo_khoi;
            }
            $gianoidia_trung = ($order['domestic_fee'] * $order['exchange_rate']);
            $tong_tien = $gia_cuoi_cung + $gianoidia_trung;
            $total += $tong_tien;
        }
        $totalAmount = $total + (float)$invoice['shipping_fee'] + (float)$invoice['other_fee']; // Cộng thêm other_fee

        $paymentStatus = 'Chưa thanh toán';
        if ($totalPaid > 0 && $totalPaid < $totalAmount) {
            $paymentStatus = 'Thanh toán một phần';
        } elseif ($totalPaid >= $totalAmount) {
            $paymentStatus = 'Đã thanh toán';
        }

        return view('invoices/detail', [
            'invoice' => $invoice,
            'customer' => $customer,
            'orders' => $orders,
            'creator' => $creator,
            'approver' => $approver,
            'payment_status' => $paymentStatus,
            'total_paid' => $totalPaid,
            'total_amount' => $totalAmount,
            'invoiceId' => $invoiceId
        ]);
    }


    public function cartAdd()
    {
        if ($this->request->isAJAX()) {
            $orderId = $this->request->getJSON()->order_id;

            // Kiểm tra nếu order đã tồn tại trong session
            $cart = session()->get('cart') ?? [];
            if (in_array($orderId, $cart)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Order đã có trong giỏ hàng.']);
            }

            // Thêm order_id vào session
            $cart[] = $orderId;
            session()->set('cart', $cart);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng.',
                'cart_count' => count($cart), // Số lượng sản phẩm hiện tại trong giỏ
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    }


    public function addToCart()
    {
        // Lấy order_id từ request
        $orderId = $this->request->getPost('order_id');

        // Kiểm tra order_id hợp lệ
        if (!$orderId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Order ID không hợp lệ.',
            ]);
        }

        // Lấy giỏ hàng từ session
        $cart = session()->get('cart') ?? [];

        // Thêm order_id vào giỏ hàng nếu chưa có
        if (!in_array($orderId, $cart)) {
            $cart[] = $orderId;
            session()->set('cart', $cart);
        }

        // Trả về JSON với số lượng sản phẩm trong giỏ hàng
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng.',
            'cart_count' => count($cart), // Số lượng sản phẩm hiện tại trong giỏ
        ]);
    }

    public function addToCartByTrackingCode()
    {
        $trackingCode = $this->request->getPost('tracking_code');

        if (empty($trackingCode)) {
            return redirect()->back()->with('error', 'Vui lòng nhập mã vận đơn.');
        }

        $orderModel = new \App\Models\OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();

        // Tìm đơn hàng theo mã vận đơn
        $order = $orderModel->where('tracking_code', $trackingCode)->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Mã vận đơn không tồn tại trong hệ thống.');
        }

        // Kiểm tra nếu đơn hàng đã được gán vào phiếu xuất nào chưa
        if (!empty($order['invoice_id'])) {
            $invoice = $invoiceModel->find($order['invoice_id']);
            if ($invoice) {
                $invoiceLink = base_url("/invoices/detail/" . $invoice['id']);
                return redirect()->back()->with('error', "Đơn hàng đã có trong phiếu xuất <a href='{$invoiceLink}'>#{$invoice['id']}</a>.");
            }
        }

        // Thêm order vào session cart nếu chưa có
        $cart = session()->get('cart') ?? [];

        if (in_array($order['id'], $cart)) {
            return redirect()->back()->with('success', 'Mã vận đơn đã có trong danh sách giỏ hàng.');
        }

        $cart[] = $order['id'];
        session()->set('cart', $cart);

        return redirect()->back()->with('success', 'Mã vận đơn đã được thêm vào giỏ hàng.');
    }

    public function confirmShipping($invoiceId)
    {
        if (!in_array(session('role'), ['Nhân viên', 'Quản lý'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền xác nhận giao hàng.');
        }

        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();

        // Lấy thông tin hóa đơn
        $invoice = $invoiceModel->find($invoiceId);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Phiếu xuất không tồn tại.');
        }

        // Kiểm tra nếu shipping_status đã là 'confirmed'
        if ($invoice['shipping_status'] === 'confirmed') {
            return redirect()->to("/invoices/detail/{$invoiceId}")->with('info', 'Phiếu xuất đã được xác nhận giao hàng trước đó.');
        }

        // Chuẩn bị dữ liệu cập nhật
        $updateData = [
            'shipping_status' => 'confirmed'
        ];

        // Thực hiện cập nhật hóa đơn
        if ($invoiceModel->update($invoiceId, $updateData)) {
            // Cập nhật trạng thái đơn hàng liên quan
            $orderModel->where('invoice_id', $invoiceId)->set(['status' => 'shipped'])->update();

            return redirect()->to("/invoices/detail/{$invoiceId}")->with('success', 'Đã xác nhận giao hàng thành công.');
        } else {
            return redirect()->back()->with('error', 'Không thể xác nhận giao hàng. Vui lòng kiểm tra lại.');
        }
    }


    public function addPayment($invoiceId)
    {
        try {
            if (!in_array(session('role'), ['Kế toán', 'Quản lý'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền nhập thanh toán.'
                ]);
            }

            $invoiceModel = new \App\Models\InvoiceModel();
            $customerModel = new \App\Models\CustomerModel();
            $orderModel = new \App\Models\OrderModel();
            $transactionModel = new \App\Models\CustomerTransactionModel();

            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu xuất không tồn tại.'
                ]);
            }

            // Tính total_amount động (giống trong detail và index)
            $orders = $orderModel->where('invoice_id', $invoiceId)->findAll();
            $total = 0;
            $invalidOrders = []; // Danh sách đơn hàng có giá = 0

            // Kiểm tra điều kiện 1: Tiền của các order phải > 0
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);

                if ($finalPrice <= 0) {
                    $invalidOrders[] = "#{$order['id']} (Mã vận chuyển: {$order['tracking_code']})";
                }
                $total += $finalPrice;
            }

            if (!empty($invalidOrders)) {
                $message = "Một số đơn hàng cần được cập nhật giá: " . implode(', ', $invalidOrders) . ". Vui lòng cập nhật giá cho 1kg hoặc 1 khối.";
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $message,
                    'modal_type' => 'invalid_price',
                    'invalid_orders' => $invalidOrders
                ]);
            }

            $totalAmount = $total + (float)($invoice['shipping_fee'] ?? 0) + (float)($invoice['other_fee'] ?? 0);

            // Lấy số dư của khách hàng bằng phương thức getCustomerBalance
            $customer = $customerModel->find($invoice['customer_id']);
            $currentBalance = $customerModel->getCustomerBalance($invoice['customer_id']);

            // Debug để kiểm tra giá trị
            log_message('debug', "Invoice ID: {$invoiceId}, Customer ID: {$invoice['customer_id']}, Current Balance: {$currentBalance}, Total Amount: {$totalAmount}");

            // Kiểm tra điều kiện 2: Chưa đủ số dư
            if ($currentBalance < $totalAmount) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không đủ số dư. Vui lòng nạp thêm tiền.',
                    'current_balance' => number_format($currentBalance, 2, ',', '.'),
                    'required_amount' => number_format($totalAmount, 2, ',', '.'),
                    'modal_type' => 'insufficient_balance'
                ]);
            }


            // Kiểm tra điều kiện 3: Phí giao hàng và phí khác là 0, kiểm tra confirm_zero_fees
            $confirmZeroFees = $this->request->getPost('confirm_zero_fees') === 'true';
            if ($invoice['shipping_fee'] == 0 && $invoice['other_fee'] == 0 && !$confirmZeroFees) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phí giao hàng và phí khác hiện tại là 0. Bạn có muốn tiếp tục thanh toán không?',
                    'modal_type' => 'zero_fees_warning',
                    'invoice_id' => $invoiceId,
                    'total_amount' => number_format($totalAmount, 0, ',', '.'),
                    'current_balance' => number_format($currentBalance, 0, ',', '.')
                ]);
            }

            // Nếu tất cả điều kiện đều thỏa mãn, thực hiện thanh toán
            // Trừ số dư của khách hàng
            $newBalance = $currentBalance - $totalAmount;
            $customerModel->update($invoice['customer_id'], ['balance' => $newBalance]);

            // Cập nhật trạng thái thanh toán của invoice thành 'paid'
            $invoiceModel->update($invoiceId, ['payment_status' => 'paid']);

            // Lưu lịch sử thanh toán
            $transactionData = [
                'customer_id' => $invoice['customer_id'],
                'invoice_id' => $invoiceId,
                'transaction_type' => 'payment',
                'amount' => -$totalAmount, // Ghi âm vì là thanh toán
                'created_by' => session()->get('user_id'),
                'notes' => 'Thanh toán phiếu xuất #' . $invoiceId
            ];
            $transactionModel->addTransaction($transactionData);

            // Xóa cache số dư nếu có
            cache()->delete("customer_balance_{$invoice['customer_id']}");

            // Trả về modal thông báo thanh toán thành công
            $customerDetailLink = base_url("customers/detail/{$invoice['customer_id']}");
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Thanh toán thành công. Số tiền đã thanh toán: ' . number_format($totalAmount, 2, ',', '.') . ' VNĐ. Số dư hiện tại: ' . number_format($newBalance, 2, ',', '.') . ' VNĐ.',
                'modal_type' => 'payment_success',
                'total_paid' => number_format($totalAmount, 2, ',', '.'),
                'new_balance' => number_format($newBalance, 2, ',', '.'),
                'customer_detail_link' => $customerDetailLink
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    // Xem danh sách thanh toán của hóa đơn
    public function viewPayments($invoiceId)
    {
        $data['payments'] = $this->invoicePaymentModel->getPaymentsByInvoice($invoiceId);
        $data['total_paid'] = $this->invoicePaymentModel->getTotalPaidByInvoice($invoiceId);
        $data['invoice_id'] = $invoiceId;

        return view('invoices/payments', $data);
    }

    // Hiển thị form thêm thanh toán
    public function createPayment($invoiceId)
    {
        $data['invoice_id'] = $invoiceId;
        return view('invoices/create_payment', $data);
    }

    // Lưu thanh toán mới
    public function storePayment($invoiceId)
    {
        $data = [
            'invoice_id' => $invoiceId,
            'amount' => $this->request->getPost('amount'),
            'payment_date' => $this->request->getPost('payment_date'),
            'payment_method' => $this->request->getPost('payment_method'),
            'note' => $this->request->getPost('note')
        ];

        if ($this->invoicePaymentModel->save($data)) {
            return redirect()->to("/invoices/payments/{$invoiceId}")->with('success', 'Thêm thanh toán thành công!');
        } else {
            return redirect()->back()->with('error', 'Không thể thêm thanh toán.');
        }
    }

    // Xóa thanh toán
    public function deletePayment($invoiceId, $paymentId)
    {
        if ($this->invoicePaymentModel->delete($paymentId)) {
            return redirect()->to("/invoices/payments/{$invoiceId}")->with('success', 'Xóa thanh toán thành công!');
        } else {
            return redirect()->back()->with('error', 'Không thể xóa thanh toán.');
        }
    }

    public function updateShippingFee($invoiceId)
    {
        try {
            if (!in_array(session('role'), ['Kế toán', 'Quản lý'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền sửa phí giao hàng.'
                ]);
            }

            $invoiceModel = new \App\Models\InvoiceModel();

            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu xuất không tồn tại.'
                ]);
            }

            // Trong phương thức updateShippingFee()
            $shippingFee = (int)$this->request->getPost('shipping_fee'); // Ép kiểu thành số nguyên
            if (is_nan($shippingFee)) { // Kiểm tra nếu không phải số
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phí giao hàng không hợp lệ.'
                ]);
            }

            // Cập nhật shipping_fee trong database
            $updateData = ['shipping_fee' => $shippingFee];
            if ($invoiceModel->update($invoiceId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Đã cập nhật phí giao hàng thành công.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể cập nhật phí giao hàng. Vui lòng thử lại.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function updateOtherFee($invoiceId)
    {
        try {
            if (!in_array(session('role'), ['Kế toán', 'Quản lý'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền sửa phí khác.'
                ]);
            }

            $invoiceModel = new \App\Models\InvoiceModel();

            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu xuất không tồn tại.'
                ]);
            }

            $otherFee = (int)$this->request->getPost('other_fee'); // Ép kiểu thành số nguyên
            if (is_nan($otherFee)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phí khác không hợp lệ.'
                ]);
            }

            $updateData = ['other_fee' => $otherFee];
            if ($invoiceModel->update($invoiceId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Đã cập nhật phí khác thành công.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể cập nhật phí khác. Vui lòng thử lại.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function deposit($customerId)
    {
        try {
            if (!in_array(session('role'), ['Kế toán', 'Quản lý'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền nạp tiền.'
                ]);
            }

            $customerModel = new \App\Models\CustomerModel();
            $transactionModel = new \App\Models\CustomerTransactionModel();

            $customer = $customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Khách hàng không tồn tại.'
                ]);
            }

            $amount = (float)$this->request->getPost('amount');
            if (!$amount || $amount <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Số tiền nạp không hợp lệ.'
                ]);
            }

            // Cập nhật số dư khách hàng
            $newBalance = $customer['balance'] + $amount;
            $customerModel->update($customerId, ['balance' => $newBalance]);

            // Lưu lịch sử nạp tiền
            $transactionData = [
                'customer_id' => $customerId,
                'transaction_type' => 'deposit',
                'amount' => $amount,
                'created_by' => session()->get('user_id'),
                'notes' => 'Nạp tiền cho khách hàng #' . $customerId
            ];
            $transactionModel->addTransaction($transactionData);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Nạp tiền thành công. Số dư hiện tại: ' . number_format($newBalance, 2, ',', '.'),
                'new_balance' => number_format($newBalance, 2, ',', '.')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    // Trong InvoiceController.php
    public function updateBulkPrices($invoiceId)
    {
        try {
            if (!in_array(session('role'), ['Kế toán', 'Quản lý'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền cập nhật giá.'
                ]);
            }

            $invoiceModel = new \App\Models\InvoiceModel();
            $orderModel = new \App\Models\OrderModel();

            // Kiểm tra invoice tồn tại
            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu xuất không tồn tại.'
                ]);
            }

            // Kiểm tra trạng thái thanh toán
            if ($invoice['payment_status'] === 'paid') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể cập nhật giá vì phiếu xuất đã được thanh toán.'
                ]);
            }

            $ordersData = $this->request->getPost('orders');
            if (!$ordersData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không có dữ liệu để cập nhật.'
                ]);
            }

            $updatedCount = 0;
            foreach ($ordersData as $orderId => $data) {
                // Lọc và chuyển đổi dữ liệu
                $updateData = [
                    'price_per_kg' => isset($data['price_per_kg']) ? (int) str_replace('.', '', $data['price_per_kg']) : 0,
                    'price_per_cubic_meter' => isset($data['price_per_cubic_meter']) ? (int) str_replace('.', '', $data['price_per_cubic_meter']) : 0,
                    'domestic_fee' => isset($data['domestic_fee']) ? (float) str_replace(',', '.', $data['domestic_fee']) : 0.00,
                ];

                // Kiểm tra order thuộc invoice này
                $existingOrder = $orderModel->where('id', $orderId)->where('invoice_id', $invoiceId)->first();
                if (!$existingOrder) {
                    continue; // Bỏ qua nếu order không thuộc invoice
                }

                // Kiểm tra xem có thay đổi không
                if (
                    $existingOrder['price_per_kg'] != $updateData['price_per_kg'] ||
                    $existingOrder['price_per_cubic_meter'] != $updateData['price_per_cubic_meter'] ||
                    $existingOrder['domestic_fee'] != $updateData['domestic_fee']
                ) {
                    if ($orderModel->update($orderId, $updateData)) {
                        $updatedCount++;
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Cập nhật thành công $updatedCount đơn hàng."
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
