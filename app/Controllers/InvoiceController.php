<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\CustomerTransactionModel;
use App\Models\SystemLogModel;
use App\Models\InvoiceOrderModel;
use App\Models\ShippingManagerModel;

class InvoiceController extends BaseController
{

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function cart()
    {
        $cart = session()->get('cart') ?? [];

        if (!is_array($cart) || empty($cart)) {
            return view('invoices/cart', ['customerCart' => []]);
        }

        // Lấy danh sách order_id từ giỏ hàng
        $orderIds = array_column($cart, 'order_id');

        // Kiểm tra nếu $orderIds rỗng
        if (empty($orderIds)) {
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
            orders.vietnam_stock_date,
            orders.official_quota_fee,
            orders.vat_tax,
            orders.import_tax,
            orders.other_tax,
            sub_customers.id as sub_customer_id,
            sub_customers.sub_customer_code
        ')
            ->join('customers', 'orders.customer_id = customers.id')
            ->join('sub_customers', 'orders.sub_customer_id = sub_customers.id', 'left')
            ->whereIn('orders.id', $orderIds)
            ->findAll();

        // Tổ chức dữ liệu theo khách hàng và mã phụ
        $customerCart = [];
        foreach ($orders as $order) {
            $customerId = $order['customer_id'];
            $subCustomerId = $order['sub_customer_id'] ?? 'no_sub'; // Nếu không có mã phụ, dùng 'no_sub' để nhóm

            // Nếu chưa có khách hàng trong danh sách, khởi tạo
            if (!isset($customerCart[$customerId])) {
                $customerCart[$customerId] = [
                    'customer_name' => $order['customer_name'],
                    'customer_code' => $order['customer_code'],
                    'sub_customers' => []
                ];
            }

            // Nếu chưa có mã phụ trong danh sách của khách hàng, khởi tạo
            if (!isset($customerCart[$customerId]['sub_customers'][$subCustomerId])) {
                $customerCart[$customerId]['sub_customers'][$subCustomerId] = [
                    'sub_customer_code' => $order['sub_customer_code'] ?? 'Không có mã phụ',
                    'orders' => []
                ];
            }

            // Tính toán chi phí và các thuộc tính liên quan đến đơn hàng
            $price_by_weight = $order['total_weight'] * $order['price_per_kg'];
            $price_by_volume = $order['volume'] * $order['price_per_cubic_meter'];
            $final_price = max($price_by_weight, $price_by_volume);
            $pricing_method = ($final_price == $price_by_weight) ? 'By Weight' : 'By Volume';

            $total_domestic_fee = $order['domestic_fee'] * $order['exchange_rate'];
            $total_price = $final_price + $total_domestic_fee +
                $order['official_quota_fee'] +
                $order['vat_tax'] +
                $order['import_tax'] +
                $order['other_tax'];

            // Thêm thông tin đơn hàng vào danh sách của mã phụ
            $customerCart[$customerId]['sub_customers'][$subCustomerId]['orders'][] = [
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
                'total_price' => $total_price,
                'official_quota_fee' => $order['official_quota_fee'],
                'vat_tax' => $order['vat_tax'],
                'import_tax' => $order['import_tax'],
                'other_tax' => $order['other_tax']
            ];
        }

        // Trả về view với dữ liệu đã tổ chức
        return view('invoices/cart', ['customerCart' => $customerCart]);
    }



    // Trong phương thức index()
    public function index()
    {
        $invoiceModel = new \App\Models\InvoiceModel();

        $orderModel = new \App\Models\OrderModel(); // Thêm model OrderModel để tính total_amount và tìm theo tracking_code

        $shippingManagerModel = new \App\Models\ShippingManagerModel(); // Khởi tạo ShippingManagerModel

        $perPage = 30; // Số lượng bản ghi mỗi trang, giống như OrderController

        // Lấy thông tin tìm kiếm từ GET
        $invoiceId = $this->request->getGet('invoice_id') ?? ''; // Thêm bộ lọc theo mã phiếu xuất (id)
        $trackingCode = $this->request->getGet('tracking_code') ?? ''; // Thêm bộ lọc theo mã vận chuyển
        $customerCode = $this->request->getGet('customer_code') ?? 'ALL';
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $currentPage = $this->request->getGet('page') ?? 1;

        // Get the request object
        $request = service('request');

        // Get all query parameters (e.g., customer_code, from_date, to_date, etc.)
        $queryParams = $request->getGet();

        // Pass the query parameters to the view
        $data['queryParams'] = $queryParams;

        // Cấu hình query
        $query = $invoiceModel
            ->select('invoices.*, customers.customer_code, customers.fullname, COUNT(orders.id) as order_count, sub_customers.sub_customer_code')
            ->join('customers', 'invoices.customer_id = customers.id', 'left')
            ->join('orders', 'invoices.id = orders.invoice_id', 'left')
            ->join('sub_customers', 'sub_customers.id = invoices.sub_customer_id', 'left')
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
                $finalPrice = max($priceByWeight, $priceByVolume) +
                    ($order['domestic_fee'] * $order['exchange_rate']) +
                    $order['official_quota_fee'] +
                    $order['vat_tax'] +
                    $order['import_tax'] +
                    $order['other_tax'];
                $total += $finalPrice;
            }
            $totalAmount = $total + (int)($invoice['shipping_fee'] ?? 0); // Ép kiểu int cho shipping_fee



            // Tính trạng thái thanh toán động giống như trong detail.php
            $paymentStatus = 'Chưa thanh toán';
            if ($invoice['payment_status'] == 'paid') {
                $paymentStatus = 'Đã thanh toán';
            } elseif ($invoice['payment_status'] == 'unpaid') {
                $paymentStatus = 'Chưa thanh toán';
            }

            $invoice['payment_status_dynamic'] = $paymentStatus;
            $invoice['total_amount'] = $totalAmount; // Gán total_amount tính toán động

            // Kiểm tra xem phiếu xuất này đã có yêu cầu giao hàng chưa
            $existingShipping = $shippingManagerModel->where('invoice_id', $invoice['id'])->first();
            $invoice['has_shipping_request'] = ($existingShipping !== null); // Thêm trường mới vào dữ liệu invoice
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
        $cart = array_filter($cart, fn($item) => $item['order_id'] != $orderId);

        // Cập nhật lại session
        session()->set('cart', array_values($cart));

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Đã xóa đơn hàng khỏi giỏ hàng.'
        ]);
    }


    public function create($customerId)
    {
        // Lấy sub_customer_id từ query string (có thể là NULL)
        $subCustomerId = $this->request->getGet('sub_customer_id') ?? null;

        // Lấy danh sách các order từ session
        $cart = session()->get('cart') ?? [];

        // Lọc các order thuộc về customer_id và sub_customer_id hiện tại
        $orderIds = [];
        foreach ($cart as $item) {
            if ($item['customer_id'] == $customerId) {
                // Nếu sub_customer_id là NULL, lấy các đơn hàng không có mã phụ
                if ($subCustomerId === null && $item['sub_customer_id'] === null) {
                    $orderIds[] = $item['order_id'];
                }
                // Nếu sub_customer_id không NULL, lấy các đơn hàng có mã phụ khớp
                elseif ($subCustomerId !== null && $item['sub_customer_id'] == $subCustomerId) {
                    $orderIds[] = $item['order_id'];
                }
            }
        }

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
            orders.vietnam_stock_date,
            orders.official_quota_fee,
            orders.vat_tax,
            orders.import_tax,
            orders.other_tax,
            sub_customers.id as sub_customer_id,
            sub_customers.sub_customer_code
        ')
            ->join('customers', 'orders.customer_id = customers.id', 'left')
            ->join('sub_customers', 'orders.sub_customer_id = sub_customers.id', 'left')
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
            'sub_customer_id' => $subCustomerId
        ]);
    }




    public function store($customerId)
    {
        $orderIds = explode(',', $this->request->getPost('order_ids'));
        $subCustomerId = $this->request->getPost('sub_customer_id');
        $shippingFee = $this->request->getPost('shipping_fee');
        $otherFee = $this->request->getPost('other_fee');
        $notes = $this->request->getPost('notes');

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

        // Lọc các orderIds có trong cart và thuộc customer_id, sub_customer_id
        $validOrderIds = [];
        foreach ($cart as $item) {
            if (in_array($item['order_id'], $orderIds) && $item['customer_id'] == $customerId) {
                if (($subCustomerId === null && $item['sub_customer_id'] === null) ||
                    ($subCustomerId !== null && $item['sub_customer_id'] == $subCustomerId)
                ) {
                    $validOrderIds[] = $item['order_id'];
                }
            }
        }

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
            $finalPrice = max($priceByWeight, $priceByVolume) +
                ($order['domestic_fee'] * $order['exchange_rate']) +
                $order['official_quota_fee'] +
                $order['vat_tax'] +
                $order['import_tax'] +
                $order['other_tax'];
            $total += $finalPrice;
        }
        $totalAmount = $total + (float)$shippingFee + (float)$otherFee;

        // Tạo invoice với sub_customer_id (nếu có)
        $invoiceData = [
            'customer_id' => $customerId,
            'sub_customer_id' => empty($subCustomerId) ? null : $subCustomerId, // Đảm bảo null nếu không có giá trị
            'created_by' => session()->get('user_id'),
            'shipping_fee' => empty($shippingFee) ? 0 : $shippingFee,
            'other_fee' => empty($otherFee) ? 0 : $otherFee,
            'total_amount' => $totalAmount,
            'notes' => $notes
        ];

        try {
            // Bắt đầu transaction
            $this->db->transStart();

            // Thêm phiếu xuất
            $invoiceId = $invoiceModel->insert($invoiceData);

            if (!$invoiceId) {
                throw new \Exception('Tạo phiếu xuất thất bại.');
            }

            // Cập nhật invoice_id và trạng thái cho orders
            $orderModel->whereIn('id', $validOrderIds)
                ->set(['invoice_id' => $invoiceId, 'status' => 'in_stock'])
                ->update();

            // Xóa các đơn hàng đã tạo khỏi cart
            $updatedCart = array_filter($cart, fn($item) => !in_array($item['order_id'], $validOrderIds));
            session()->set('cart', array_values($updatedCart));

            // Kết thúc transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Lỗi trong quá trình tạo phiếu xuất.');
            }

            return redirect()->to("/invoices/detail/{$invoiceId}")->with('success', 'Tạo phiếu xuất thành công.');
        } catch (\Exception $e) {
            log_message('error', 'Lỗi tạo phiếu xuất: ' . $e->getMessage());
            return redirect()->back()->with('modal_error', 'Lỗi: ' . $e->getMessage());
        }
    }



    public function detail($invoiceId)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();
        $customerModel = new \App\Models\CustomerModel();
        $userModel = new \App\Models\UserModel();

        // Lấy thông tin phiếu xuất kèm mã phụ
        $invoice = $invoiceModel
            ->select('invoices.*, sub_customers.sub_customer_code')
            ->join('sub_customers', 'sub_customers.id = invoices.sub_customer_id', 'left')
            ->find($invoiceId);

        if (!$invoice) {
            return redirect()->to('/invoices')->with('error', 'Phiếu xuất không tồn tại.');
        }

        $customer = $customerModel->find($invoice['customer_id']);
        if (!$customer) {
            return redirect()->to('/invoices')->with('error', 'Khách hàng không tồn tại.');
        }

        $orders = $orderModel
            ->select('orders.*, product_types.name as product_type_name, sub_customers.sub_customer_code')
            ->join('product_types', 'orders.product_type_id = product_types.id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->where('orders.invoice_id', $invoiceId)
            ->findAll();

        // Lấy danh sách khách hàng để hiển thị trong modal chuyển khách hàng
        $customers = $customerModel->select('id, customer_code, fullname')->orderBy('customer_code', 'ASC')->findAll();

        $creator = $userModel->find($invoice['created_by']);
        $shipping_confirmed_by = $userModel->find($invoice['shipping_confirmed_by']);


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
            $tong_tien = $gia_cuoi_cung + $gianoidia_trung +
                $order['official_quota_fee'] +
                $order['vat_tax'] +
                $order['import_tax'] +
                $order['other_tax'];
            $total += $tong_tien;
        }
        $totalAmount = $total + (float)$invoice['shipping_fee'] + (float)$invoice['other_fee']; // Cộng thêm other_fee

        $paymentStatus = 'Chưa thanh toán';
        if ($invoice['payment_status'] == 'paid') {
            $paymentStatus = 'Đã thanh toán';
        } elseif ($invoice['payment_status'] == 'unpaid') {
            $paymentStatus = 'Chưa thanh toán';
        }

        return view('invoices/detail', [
            'invoice' => $invoice,
            'customer' => $customer,
            'orders' => $orders,
            'creator' => $creator,
            'payment_status' => $paymentStatus,
            'total_amount' => $totalAmount,
            'invoiceId' => $invoiceId,
            'shipping_confirmed_by' => $shipping_confirmed_by,
            'customers' => $customers // Truyền danh sách khách hàng vào view
        ]);
    }


    public function cartAdd()
    {
        $orderId = $this->request->getPost('order_id');

        if (empty($orderId) || !is_numeric($orderId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID đơn hàng không hợp lệ.'
            ]);
        }

        $orderModel = new \App\Models\OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $customerModel = new \App\Models\CustomerModel();

        $order = $orderModel->find($orderId);

        if (!$order) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại.'
            ]);
        }

        if (empty($order['vietnam_stock_date'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Đơn hàng chưa về kho Việt Nam. Vui lòng đợi đơn hàng về kho trước khi thêm vào giỏ hàng.'
            ]);
        }

        if (!empty($order['invoice_id'])) {
            $invoice = $invoiceModel->find($order['invoice_id']);
            if ($invoice) {
                $invoiceLink = base_url("/invoices/detail/" . $invoice['id']);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Đơn hàng đã có trong phiếu xuất <a href='{$invoiceLink}'>#{$invoice['id']}</a>."
                ]);
            }
        }

        // Kiểm tra điều kiện quá hạn thanh toán
        $customer = $customerModel->find($order['customer_id']);
        if ($customer) {
            $paymentLimitDays = $customer['payment_limit_days'] ?? 15; // Mặc định 15 ngày nếu không có giá trị

            // Lấy danh sách phiếu xuất chưa thanh toán của khách hàng
            $unpaidInvoices = $invoiceModel
                ->where('customer_id', $order['customer_id'])
                ->where('payment_status', 'unpaid')
                ->findAll();

            $overdueInvoices = [];
            foreach ($unpaidInvoices as $invoice) {
                $daysSinceCreation = (time() - strtotime($invoice['created_at'])) / (60 * 60 * 24);
                if ($daysSinceCreation > $paymentLimitDays) {
                    $overdueInvoices[] = $invoice;
                }
            }

            if (!empty($overdueInvoices)) {
                $invoiceCount = count($overdueInvoices);
                $customerName = $customer['fullname'];
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Khách hàng <b>{$customer['customer_code']} - {$customer['fullname']}</b> có <b>{$invoiceCount}</b> phiếu xuất quá <b>{$paymentLimitDays}</b> ngày chưa thanh toán."
                ]);
            }
        }

        $cart = session()->get('cart') ?? [];

        if (!is_array($cart)) {
            log_message('error', 'Dữ liệu giỏ hàng trong session không phải là mảng: ' . json_encode($cart));
            $cart = [];
        }

        $orderExists = false;
        foreach ($cart as $item) {
            if (!is_array($item) || !isset($item['order_id'])) {
                log_message('error', 'Mục trong giỏ hàng không hợp lệ: ' . json_encode($item));
                continue;
            }

            if ($item['order_id'] == $order['id']) {
                $orderExists = true;
                break;
            }
        }

        if ($orderExists) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Đơn hàng đã có trong giỏ hàng.'
            ]);
        }

        $cart[] = [
            'order_id' => $order['id'],
            'customer_id' => $order['customer_id'],
            'sub_customer_id' => $order['sub_customer_id']
        ];
        session()->set('cart', $cart);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Đơn hàng đã được thêm vào giỏ hàng!',
            'cart_count' => count($cart) // Trả về số lượng đơn hàng trong giỏ
        ]);
    }

    public function cartCheck()
    {
        $orderId = $this->request->getPost('order_id');

        if (empty($orderId) || !is_numeric($orderId)) {
            return $this->response->setJSON([
                'success' => false,
                'exists' => false,
                'message' => 'ID đơn hàng không hợp lệ.'
            ]);
        }

        $cart = session()->get('cart') ?? [];

        if (!is_array($cart)) {
            log_message('error', 'Dữ liệu giỏ hàng trong session không phải là mảng: ' . json_encode($cart));
            return $this->response->setJSON([
                'success' => true,
                'exists' => false,
                'message' => 'Giỏ hàng trống.'
            ]);
        }

        $orderExists = false;
        foreach ($cart as $item) {
            if (!is_array($item) || !isset($item['order_id'])) {
                log_message('error', 'Mục trong giỏ hàng không hợp lệ: ' . json_encode($item));
                continue;
            }

            if ($item['order_id'] == $orderId) {
                $orderExists = true;
                break;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'exists' => $orderExists,
            'message' => $orderExists ? 'Đơn hàng đã có trong giỏ hàng.' : 'Đơn hàng chưa có trong giỏ hàng.'
        ]);
    }

    public function cartCount()
    {
        $cart = session()->get('cart') ?? [];
        if (!is_array($cart)) {
            log_message('error', 'Dữ liệu giỏ hàng trong session không phải là mảng: ' . json_encode($cart));
            $cart = [];
        }
        return $this->response->setJSON([
            'success' => true,
            'cart_count' => count($cart)
        ]);
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
        $trackingCode = trim($this->request->getPost('tracking_code'));

        if (empty($trackingCode)) {
            return redirect()->back()->with('error', 'Vui lòng nhập mã vận đơn.');
        }

        $orderModel = new \App\Models\OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $customerModel = new \App\Models\CustomerModel();

        $order = $orderModel->where('tracking_code', $trackingCode)->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Mã vận đơn không tồn tại trong hệ thống.');
        }

        if (empty($order['vietnam_stock_date'])) {
            $checkStockLink = base_url('/orders/vncheck');
            return redirect()->back()->with('error', "Đơn hàng <strong>{$trackingCode}</strong> chưa được nhập kho Việt Nam. Vui lòng kiểm tra tại <a href='{$checkStockLink}'>đây</a>.");
        }

        if (!empty($order['invoice_id'])) {
            $invoice = $invoiceModel->find($order['invoice_id']);
            if ($invoice) {
                $invoiceLink = base_url("/invoices/detail/" . $invoice['id']);
                return redirect()->back()->with('error', "Đơn hàng đã có trong phiếu xuất <a href='{$invoiceLink}'>#{$invoice['id']}</a>.");
            }
        }

        // Kiểm tra điều kiện quá hạn thanh toán
        $customer = $customerModel->find($order['customer_id']);
        if ($customer) {
            $paymentLimitDays = $customer['payment_limit_days'] ?? 15; // Mặc định 15 ngày nếu không có giá trị

            // Lấy danh sách phiếu xuất chưa thanh toán của khách hàng
            $unpaidInvoices = $invoiceModel
                ->where('customer_id', $order['customer_id'])
                ->where('payment_status', 'unpaid')
                ->findAll();

            $overdueInvoices = [];
            foreach ($unpaidInvoices as $invoice) {
                $daysSinceCreation = (time() - strtotime($invoice['created_at'])) / (60 * 60 * 24);
                if ($daysSinceCreation > $paymentLimitDays) {
                    $overdueInvoices[] = $invoice;
                }
            }

            if (!empty($overdueInvoices)) {
                $invoiceCount = count($overdueInvoices);
                $customerName = $customer['fullname'];
                $customerCode = $customer['customer_code'];
                return redirect()->to(base_url('invoices/cart'))->with(
                    'error',
                    "Đơn hàng <b>{$trackingCode}</b> không thể thêm vào giỏ hàng. <br> 
                    Khách hàng <b>{$customerCode} - {$customerName}</b> có <b>{$invoiceCount}</b> phiếu xuất quá <b>{$paymentLimitDays}</b> ngày chưa thanh toán."
                );
            }
        }

        $cart = session()->get('cart') ?? [];

        if (!is_array($cart)) {
            log_message('error', 'Dữ liệu giỏ hàng trong session không phải là mảng: ' . json_encode($cart));
            $cart = [];
        }

        $orderExists = false;
        foreach ($cart as $item) {
            if (!is_array($item) || !isset($item['order_id'])) {
                log_message('error', 'Mục trong giỏ hàng không hợp lệ: ' . json_encode($item));
                continue;
            }

            if ($item['order_id'] == $order['id']) {
                $orderExists = true;
                break;
            }
        }

        if ($orderExists) {
            return redirect()->back()->with('success', 'Mã vận đơn đã có trong danh sách giỏ hàng.');
        }

        $cart[] = [
            'order_id' => $order['id'],
            'customer_id' => $order['customer_id'],
            'sub_customer_id' => $order['sub_customer_id']
        ];
        session()->set('cart', $cart);

        return redirect()->to(base_url('invoices/cart'))->with('success', 'Mã vận đơn đã được thêm vào giỏ hàng!');
    }

    public function confirmShipping($invoiceId)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();

        // Lấy thông tin hóa đơn
        $invoice = $invoiceModel->find($invoiceId);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Phiếu xuất không tồn tại.');
        }

        if ($invoice['shipping_status'] === 'confirmed') {
            return redirect()->to("/invoices/detail/{$invoiceId}")->with('error', 'Phiếu xuất đã được xác nhận giao hàng trước đó.');
        }

        // Chuẩn bị dữ liệu cập nhật
        $updateData = [
            'shipping_status' => 'confirmed',
            'shipping_confirmed_by' => session()->get('user_id'), // Gán ID người xác nhận
            'shipping_confirmed_at' => date('Y-m-d H:i:s') // Gán thời gian xác nhận
        ];

        // Thực hiện cập nhật hóa đơn
        if ($invoiceModel->update($invoiceId, $updateData)) {
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

            // Kiểm tra nếu giao hàng chưa được xác nhận
            // if ($invoice['shipping_status'] !== 'confirmed') {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'message' => 'Phiếu xuất phải được xác nhận giao hàng trước khi thanh toán.',
            //         'modal_type' => 'shipping_not_confirmed'
            //     ]);
            // }

            // Tính total_amount động (giống trong detail và index)
            $orders = $orderModel->where('invoice_id', $invoiceId)->findAll();
            $total = 0;
            $invalidOrders = []; // Danh sách đơn hàng có giá = 0

            // Kiểm tra điều kiện 1: Tiền của các order phải > 0
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']) + $order['official_quota_fee'] + $order['vat_tax'] + $order['import_tax'] + $order['other_tax'];

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
            $currentBalance = (float)$customerModel->getCustomerBalance($invoice['customer_id']);

            // Debug để kiểm tra giá trị
            log_message('debug', "Invoice ID: {$invoiceId}, Customer ID: {$invoice['customer_id']}, Current Balance: {$currentBalance}, Total Amount: {$totalAmount}");

            // Kiểm tra điều kiện 2: Chưa đủ số dư
            // Sử dụng epsilon để so sánh số thực
            $epsilon = 0.0001;
            if (abs($currentBalance - $totalAmount) > $epsilon && $currentBalance < $totalAmount) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không đủ số dư. Vui lòng nạp thêm tiền.',
                    'current_balance' => number_format($currentBalance, 0, ',', '.'),
                    'required_amount' => number_format($totalAmount, 0, ',', '.'),
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


    // Hiển thị form thêm thanh toán
    public function createPayment($invoiceId)
    {
        $data['invoice_id'] = $invoiceId;
        return view('invoices/create_payment', $data);
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

    public function delete($invoiceId)
    {
        try {
            // Kiểm tra quyền truy cập - chỉ cho phép admin
            if (session()->get('role') !== 'Quản lý') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa phiếu xuất.'
                ]);
            }

            // Kiểm tra ID phiếu xuất
            if (!$invoiceId || !is_numeric($invoiceId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID phiếu xuất không hợp lệ.'
                ]);
            }

            $invoiceModel = new \App\Models\InvoiceModel();
            $orderModel = new \App\Models\OrderModel();
            $systemLogModel = new \App\Models\SystemLogModel();
            $customerModel = new \App\Models\CustomerModel();

            // Lấy thông tin phiếu xuất
            $invoice = $invoiceModel->find($invoiceId);
            if (!$invoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu xuất không tồn tại.'
                ]);
            }

            // Lấy thông tin khách hàng
            $customer = $customerModel->find($invoice['customer_id']);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng.'
                ]);
            }

            // Kiểm tra trạng thái thanh toán
            if ($invoice['payment_status'] === 'paid') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể xóa phiếu xuất vì đã được thanh toán.'
                ]);
            }

            // Lấy danh sách order_ids của phiếu xuất để ghi log
            $orders = $orderModel->where('invoice_id', $invoiceId)->findAll();
            $orderIds = array_column($orders, 'id');

            // Xóa phiếu xuất
            $invoiceModel->delete($invoiceId);

            // Ghi log
            $systemLogModel->addLog([
                'entity_type' => 'invoice',
                'entity_id' => $invoiceId,
                'action_type' => 'delete',
                'created_by' => session()->get('user_id'),
                'details' => json_encode([
                    'invoice_data' => $invoice,
                    'order_ids' => $orderIds,
                    'customer_data' => [
                        'id' => $customer['id'],
                        'customer_code' => $customer['customer_code'],
                        'fullname' => $customer['fullname']
                    ]
                ]),
                'notes' => "Xóa phiếu xuất #{$invoiceId} của khách hàng {$customer['customer_code']} - {$customer['fullname']}."
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Xóa phiếu xuất thành công.'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function reassignOrder($orderId)
    {
        $orderModel = new OrderModel();
        $invoiceModel = new InvoiceModel();
        $customerModel = new CustomerModel();
        $transactionModel = new CustomerTransactionModel();
        $systemLogModel = new SystemLogModel();

        try {
            // Kiểm tra dữ liệu đầu vào
            if (empty($orderId) || !is_numeric($orderId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID đơn hàng không hợp lệ.'
                ]);
            }

            $newCustomerId = $this->request->getPost('new_customer_id');
            if (empty($newCustomerId) || !is_numeric($newCustomerId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID khách hàng mới không hợp lệ.'
                ]);
            }

            // Lấy thông tin đơn hàng và phiếu xuất
            $order = $orderModel->find($orderId);
            if (!$order) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Đơn hàng không tồn tại.'
                ]);
            }

            $originalInvoiceId = $order['invoice_id'];
            if (empty($originalInvoiceId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Đơn hàng không thuộc phiếu xuất nào.'
                ]);
            }

            $originalInvoice = $invoiceModel->find($originalInvoiceId);
            if (!$originalInvoice) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu xuất gốc không tồn tại.'
                ]);
            }

            $originalCustomerId = $order['customer_id'];
            if (empty($originalCustomerId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Khách hàng gốc không tồn tại trong đơn hàng.'
                ]);
            }

            // Kiểm tra khách hàng mới
            $newCustomer = $customerModel->find($newCustomerId);
            if (!$newCustomer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Khách hàng mới không tồn tại.'
                ]);
            }

            // Tính số tiền đơn hàng
            $orderAmount = $this->calculateOrderAmount($order);
            if ($orderAmount < 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Số tiền đơn hàng không hợp lệ (âm).'
                ]);
            }

            // Bắt đầu giao dịch
            $this->db->transBegin();

            // Loại bỏ đơn hàng khỏi phiếu xuất và gán khách hàng mới
            $orderModel->update($orderId, ['invoice_id' => null]);
            $orderModel->update($orderId, ['customer_id' => $newCustomerId]);

            // Xử lý tài chính nếu phiếu xuất ban đầu đã thanh toán
            if ($originalInvoice['payment_status'] === 'paid') {
                $originalCustomer = $customerModel->find($originalCustomerId);
                if (!$originalCustomer) {
                    throw new \Exception('Không tìm thấy thông tin khách hàng gốc.');
                }

                $newBalance = $originalCustomer['balance'] + $orderAmount;
                $customerModel->update($originalCustomerId, ['balance' => $newBalance]);

                $transactionData = [
                    'customer_id' => $originalCustomerId,
                    'invoice_id' => $originalInvoiceId,
                    'transaction_type' => 'deposit',
                    'amount' => $orderAmount,
                    'notes' => "Hoàn tiền do chuyển đơn hàng #$orderId khỏi phiếu xuất #$originalInvoiceId",
                    'created_by' => session()->get('user_id')
                ];
                $transactionModel->insert($transactionData);
            }

            // Ghi log hành động
            $this->logAction($orderId, $originalInvoice, $newCustomerId, $orderAmount);

            // Kiểm tra trạng thái giao dịch
            if ($this->db->transStatus() === false) {
                $error = $this->db->error(); // Lấy thông tin lỗi từ database
                $this->db->transRollback();
                log_message('error', "Lỗi giao dịch trong reassignOrder: " . json_encode($error));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Lỗi hệ thống: Không thể hoàn tất giao dịch. Vui lòng liên hệ quản trị viên.',
                    'error_details' => $error['message'] ?? 'Không có chi tiết lỗi.'
                ]);
            }

            // Commit giao dịch nếu thành công
            $this->db->transCommit();
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Chuyển đơn hàng thành công.'
            ]);
        } catch (\Exception $e) {
            // Rollback giao dịch nếu có lỗi ngoài dự kiến
            if ($this->db->transStatus() !== false) {
                $this->db->transRollback();
            }
            log_message('error', "Lỗi trong reassignOrder: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    private function logAction($orderId, $originalInvoice, $newCustomerId, $orderAmount)
    {
        $logModel = new SystemLogModel();
        $logModel->insert([
            'entity_type' => 'order',
            'entity_id' => $orderId,
            'action_type' => 'reassign',
            'created_by' => session('user_id'),
            'details' => json_encode([
                'order_id' => $orderId,
                'original_invoice_id' => $originalInvoice['id'],
                'original_customer_id' => $originalInvoice['customer_id'],
                'new_customer_id' => $newCustomerId,
                'amount_affected' => $orderAmount,
                'original_payment_status' => $originalInvoice['payment_status'],
                'new_payment_status' => $originalInvoice['payment_status'], // Giữ nguyên trạng thái ban đầu
            ]),
            'notes' => "Loại bỏ và chuyển đơn hàng #$orderId từ phiếu xuất #{$originalInvoice['id']} (khách hàng #{$originalInvoice['customer_id']}) sang khách hàng #$newCustomerId. Số tiền ảnh hưởng: " . number_format($orderAmount) . " đ.",
        ]);
    }

    private function calculateOrderAmount($order)
    {
        // Logic tính toán giá trị đơn hàng dựa trên cách tính trong detail()
        $priceByWeight = floatval($order['total_weight'] ?? 0) * floatval($order['price_per_kg'] ?? 0);
        $priceByVolume = floatval($order['volume'] ?? 0) * floatval($order['price_per_cubic_meter'] ?? 0);
        $domesticFee = floatval($order['domestic_fee'] ?? 0) * floatval($order['exchange_rate'] ?? 0);
        $officialQuotaFee = floatval($order['official_quota_fee'] ?? 0);
        $vatTax = floatval($order['vat_tax'] ?? 0);
        $importTax = floatval($order['import_tax'] ?? 0);
        $otherTax = floatval($order['other_tax'] ?? 0);

        return max($priceByWeight, $priceByVolume) + $domesticFee +
            $officialQuotaFee + $vatTax + $importTax + $otherTax;
    }


    public function overdue()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = 30;
            $days = $this->request->getGet('days') ?? 7;
            $customerCode = $this->request->getGet('customer_code') ?? 'ALL';

            // Khởi tạo model
            $invoiceModel = new \App\Models\InvoiceModel();
            $orderModel = new \App\Models\OrderModel();

            // Xây dựng truy vấn chính
            $builder = $this->db->table('invoices i')
                ->select('
                i.id,
                i.shipping_fee,
                i.other_fee,
                i.payment_status,
                i.notes,
                i.created_at,
                i.shipping_confirmed_at,
                c.fullname as customer_name,
                c.customer_code,
                c.id as customer_id,
                u.fullname as creator_name,
                COUNT(DISTINCT o.id) as total_orders,
                SUM(o.total_weight) as total_weight,
                SUM(o.volume) as total_volume,
                DATEDIFF(CURDATE(), DATE(i.created_at)) as days_overdue
            ')
                ->join('customers c', 'c.id = i.customer_id', 'left')
                ->join('users u', 'u.id = i.created_by', 'left')
                ->join('orders o', 'o.invoice_id = i.id', 'left')
                ->where('i.payment_status', 'unpaid')
                ->where('DATEDIFF(CURDATE(), DATE(i.created_at)) >=', $days)
                ->groupBy('i.id, i.shipping_fee, i.other_fee, i.payment_status, i.notes, i.created_at, i.shipping_confirmed_at, c.fullname, c.customer_code, c.id, u.fullname')
                ->orderBy('i.created_at', 'ASC');

            // Thêm điều kiện lọc theo khách hàng
            if (!empty($customerCode) && $customerCode !== 'ALL') {
                $builder->where('c.customer_code', $customerCode);
            }

            // Lấy tổng số phiếu xuất chưa thanh toán (không phụ thuộc vào điều kiện ngày)
            $totalBuilder = $this->db->table('invoices i')
                ->where('i.payment_status', 'unpaid');
            if (!empty($customerCode) && $customerCode !== 'ALL') {
                $totalBuilder->join('customers c', 'c.id = i.customer_id')
                    ->where('c.customer_code', $customerCode);
            }
            $totalInvoices = $totalBuilder->countAllResults();

            // Lấy tổng số phiếu xuất quá hạn
            $countBuilder = clone $builder;
            $total = $countBuilder->countAllResults();

            // Tính toán thông tin tổng quan
            $overviewBuilder = clone $builder;
            $overviewData = $overviewBuilder->get()->getResultArray();

            // Tính số khách hàng quá hạn
            $uniqueCustomers = array_unique(array_column($overviewData, 'customer_id'));
            $overdueCustomersCount = count($uniqueCustomers);

            // Tính tổng tiền quá hạn
            $totalOverdueAmount = 0;
            $maxDaysOverdue = 0;
            $invoices = [];

            foreach ($overviewData as $invoice) {
                $orders = $orderModel->where('invoice_id', $invoice['id'])->findAll();
                $total_amount = 0;
                foreach ($orders as $order) {
                    $totalWeight = floatval($order['total_weight'] ?? 0);
                    $pricePerKg = floatval($order['price_per_kg'] ?? 0);
                    $volume = floatval($order['volume'] ?? 0);
                    $pricePerCubicMeter = floatval($order['price_per_cubic_meter'] ?? 0);
                    $domesticFee = floatval($order['domestic_fee'] ?? 0);
                    $exchangeRate = floatval($order['exchange_rate'] ?? 0);
                    $officialQuotaFee = floatval($order['official_quota_fee'] ?? 0);
                    $vatTax = floatval($order['vat_tax'] ?? 0);
                    $importTax = floatval($order['import_tax'] ?? 0);
                    $otherTax = floatval($order['other_tax'] ?? 0);

                    $priceByWeight = $totalWeight * $pricePerKg;
                    $priceByVolume = $volume * $pricePerCubicMeter;
                    $finalPrice = max($priceByWeight, $priceByVolume) +
                        ($domesticFee * $exchangeRate) +
                        $officialQuotaFee +
                        $vatTax +
                        $importTax +
                        $otherTax;
                    $total_amount += $finalPrice;
                }
                $shippingFee = floatval($invoice['shipping_fee'] ?? 0);
                $otherFee = floatval($invoice['other_fee'] ?? 0);
                $invoice['total_amount'] = $total_amount + $shippingFee + $otherFee;
                $totalOverdueAmount += $invoice['total_amount'];
                $maxDaysOverdue = max($maxDaysOverdue, (int)$invoice['days_overdue']);
                $invoices[] = $invoice; // Lưu lại để dùng cho phân trang
            }



            // Thêm phân trang
            $start = ($page - 1) * $perPage;
            $invoices = array_slice($invoices, $start, $perPage);

            // Lấy danh sách khách hàng để hiển thị dropdown
            $customerModel = new \App\Models\CustomerModel();
            $customers = $customerModel->select('customer_code, fullname')
                ->where('customer_code IS NOT NULL')
                ->orderBy('customer_code', 'ASC')
                ->findAll();

            // Tạo đối tượng pager
            $pager = service('pager');
            $pager->setPath('invoices/overdue');
            $pager->makeLinks($page, $perPage, $total);


            $data = [
                'invoices' => $invoices,
                'pager' => $pager,
                'days' => $days,
                'customer_code' => $customerCode,
                'customers' => $customers,
                'total' => $total,
                'totalInvoices' => $totalInvoices,
                'perPage' => $perPage,
                'page' => $page,
                // Dữ liệu cho thông tin tổng quan
                'current_date' => date('d/m/Y'),
                'overdue_customers_count' => $overdueCustomersCount,
                'overdue_invoices_count' => $total,
                'total_overdue_amount' => $totalOverdueAmount,
                'max_days_overdue' => $maxDaysOverdue,
            ];

            return view('invoices/overdue', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in overdue method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại.' . $e->getMessage());
        }
    }

    public function pending()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = 30;
            $days = $this->request->getGet('days') ?? 7;
            $customerCode = $this->request->getGet('customer_code') ?? 'ALL';

            // Khởi tạo model
            $invoiceModel = new \App\Models\InvoiceModel();
            $orderModel = new \App\Models\OrderModel();

            // Xây dựng truy vấn chính
            $builder = $this->db->table('invoices i')
                ->select('
                    i.id,
                    i.shipping_fee,
                    i.other_fee,
                    i.payment_status,
                    i.notes,
                    i.created_at,
                    i.shipping_confirmed_at,
                    c.fullname as customer_name,
                    c.customer_code,
                    c.id as customer_id,
                    u.fullname as creator_name,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(o.total_weight) as total_weight,
                    SUM(o.volume) as total_volume,
                    DATEDIFF(CURDATE(), DATE(i.created_at)) as waiting_days
                ')
                ->join('customers c', 'c.id = i.customer_id', 'left')
                ->join('users u', 'u.id = i.created_by', 'left')
                ->join('orders o', 'o.invoice_id = i.id', 'left')
                ->where('i.shipping_confirmed_at', null)
                ->groupBy('i.id, i.shipping_fee, i.other_fee, i.payment_status, i.notes, i.created_at, i.shipping_confirmed_at, c.fullname, c.customer_code, c.id, u.fullname')
                ->orderBy('i.created_at', 'ASC');

            // Thêm điều kiện lọc theo khách hàng
            if (!empty($customerCode) && $customerCode !== 'ALL') {
                $builder->where('c.customer_code', $customerCode);
            }

            // Lấy tổng số phiếu xuất chưa giao hàng (không phụ thuộc vào điều kiện ngày)
            $totalBuilder = $this->db->table('invoices i')
                ->where('i.shipping_confirmed_at', null);
            if (!empty($customerCode) && $customerCode !== 'ALL') {
                $totalBuilder->join('customers c', 'c.id = i.customer_id')
                    ->where('c.customer_code', $customerCode);
            }
            $totalInvoices = $totalBuilder->countAllResults();

            // Lấy tổng số phiếu xuất chưa giao hàng
            $countBuilder = clone $builder;
            $total = $countBuilder->countAllResults();

            // Thêm phân trang
            $builder->limit($perPage, ($page - 1) * $perPage);

            $invoices = $builder->get()->getResultArray();

            // Tính total_amount cho mỗi invoice
            foreach ($invoices as &$invoice) {
                try {
                    // Lấy danh sách đơn hàng của phiếu xuất
                    $orders = $orderModel->where('invoice_id', $invoice['id'])->findAll();

                    // Tính tổng tiền từ các đơn hàng
                    $total_amount = 0;
                    foreach ($orders as $order) {
                        $totalWeight = floatval($order['total_weight'] ?? 0);
                        $pricePerKg = floatval($order['price_per_kg'] ?? 0);
                        $volume = floatval($order['volume'] ?? 0);
                        $pricePerCubicMeter = floatval($order['price_per_cubic_meter'] ?? 0);
                        $domesticFee = floatval($order['domestic_fee'] ?? 0);
                        $exchangeRate = floatval($order['exchange_rate'] ?? 0);
                        $officialQuotaFee = floatval($order['official_quota_fee'] ?? 0);
                        $vatTax = floatval($order['vat_tax'] ?? 0);
                        $importTax = floatval($order['import_tax'] ?? 0);
                        $otherTax = floatval($order['other_tax'] ?? 0);

                        $priceByWeight = $totalWeight * $pricePerKg;
                        $priceByVolume = $volume * $pricePerCubicMeter;
                        $finalPrice = max($priceByWeight, $priceByVolume) +
                            ($domesticFee * $exchangeRate) +
                            $officialQuotaFee +
                            $vatTax +
                            $importTax +
                            $otherTax;
                        $total_amount += $finalPrice;
                    }

                    // Cộng thêm phí giao hàng và phí khác
                    $shippingFee = floatval($invoice['shipping_fee'] ?? 0);
                    $otherFee = floatval($invoice['other_fee'] ?? 0);
                    $invoice['total_amount'] = $total_amount + $shippingFee + $otherFee;
                } catch (\Exception $e) {
                    log_message('error', 'Error calculating total amount for invoice ' . $invoice['id'] . ': ' . $e->getMessage());
                    $invoice['total_amount'] = 0;
                }
            }

            // Lấy danh sách khách hàng để hiển thị dropdown
            $customerModel = new \App\Models\CustomerModel();
            $customers = $customerModel->select('customer_code, fullname')
                ->where('customer_code IS NOT NULL')
                ->orderBy('customer_code', 'ASC')
                ->findAll();

            // Tạo đối tượng pager
            $pager = service('pager');
            $pager->setPath('invoices/pending');
            $pager->makeLinks($page, $perPage, $total);

            $data = [
                'invoices' => $invoices,
                'pager' => $pager,
                'days' => $days,
                'customer_code' => $customerCode,
                'customers' => $customers,
                'total' => $total,
                'totalInvoices' => $totalInvoices,
                'perPage' => $perPage,
                'page' => $page
            ];

            return view('invoices/pending', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error in pending method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại.');
        }
    }

    public function exportExcel($invoiceId)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $customerModel = new \App\Models\CustomerModel();
        $orderModel = new \App\Models\OrderModel();

        // Lấy thông tin phiếu xuất
        $invoice = $invoiceModel->find($invoiceId);
        if (!$invoice) {
            session()->setFlashdata('error', 'Phiếu xuất không tồn tại.');
            return redirect()->to('/invoices');
        }

        // Lấy thông tin khách hàng
        $customer = $customerModel->find($invoice['customer_id']);
        if (!$customer) {
            session()->setFlashdata('error', 'Khách hàng không tồn tại.');
            return redirect()->to('/invoices');
        }

        // Lấy danh sách đơn hàng trong phiếu xuất
        $orders = $orderModel
            ->select('orders.*, product_types.name AS product_type_name, sub_customers.sub_customer_code')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
            ->where('orders.invoice_id', $invoiceId)
            ->findAll();

        if (empty($orders)) {
            session()->setFlashdata('error', 'Không có đơn hàng nào trong phiếu xuất #' . $invoiceId);
            return redirect()->to('/invoices');
        }

        // Tạo file Excel bằng PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Đặt tiêu đề các cột giống detail.php
        $headers = [
            '#',
            'Mã phụ',
            'Mã vận chuyển',
            'Mã bao',
            'Hàng',
            'Số lượng',
            'KL (kg)',
            'Kích thước',
            'Khối m³',
            'Giá/kg',
            'Giá/khối',
            'Phí nội địa',
            'Phí Chính ngạch',
            'Thuế VAT',
            'Thuế Nhập khẩu',
            'Thuế khác',
            'Tính theo',
            'Tổng giá'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Thêm dữ liệu đơn hàng
        $row = 2;
        $total = 0;
        $totalWeight = 0;
        $totalVolume = 0;
        $totalQuantity = 0;
        $totalDomesticFee = 0;
        $totalOfficialQuotaFee = 0;
        $totalVatTax = 0;
        $totalImportTax = 0;
        $totalOtherTax = 0;

        foreach ($orders as $index => $order) {
            $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
            $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
            $domesticFee = $order['domestic_fee'] * $order['exchange_rate'];
            $officialQuotaFee = $order['official_quota_fee'];
            $vatTax = $order['vat_tax'];
            $importTax = $order['import_tax'];
            $otherTax = $order['other_tax'];

            $finalPrice = max($priceByWeight, $priceByVolume) +
                $domesticFee +
                $officialQuotaFee +
                $vatTax +
                $importTax +
                $otherTax;

            $priceMethod = ($priceByWeight >= $priceByVolume) ? 'Cân nặng' : 'Thể tích';

            $total += $finalPrice;
            $totalWeight += $order['total_weight'];
            $totalVolume += $order['volume'];
            $totalQuantity += $order['quantity'];
            $totalDomesticFee += $domesticFee;
            $totalOfficialQuotaFee += $officialQuotaFee;
            $totalVatTax += $vatTax;
            $totalImportTax += $importTax;
            $totalOtherTax += $otherTax;

            // Ghi các giá trị khác bằng fromArray
            $sheet->fromArray([
                $index + 1,
                $order['sub_customer_code'] ?? '-',
                null, // Để trống cột "Mã vận chuyển", sẽ ghi riêng bằng setValueExplicit
                $order['package_code'],
                $order['product_type_name'],
                $order['quantity'],
                $order['total_weight'],
                $order['length'] . 'x' . $order['width'] . 'x' . $order['height'],
                $order['volume'],
                $order['price_per_kg'],
                $order['price_per_cubic_meter'],
                $order['domestic_fee'],
                $officialQuotaFee,
                $vatTax,
                $importTax,
                $otherTax,
                $priceMethod,
                $finalPrice
            ], null, "A$row");

            // Ghi giá trị tracking_code bằng setValueExplicit để đảm bảo là text
            if (!empty($order['tracking_code'])) {
                $sheet->getCell('C' . $row)
                    ->setValueExplicit(
                        $order['tracking_code'],
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
            }

            // Định dạng cột "Mã vận chuyển" (cột C) là Text
            $sheet->getCell('C' . $row)->getStyle()->getNumberFormat()->setFormatCode('@');

            $row++;
        }

        // Định dạng toàn bộ cột "Mã vận chuyển" (cột C) là Text
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('C2:C' . $lastRow)->getNumberFormat()->setFormatCode('@');

        // Thêm chân bảng (tfoot) giống detail.php
        $sheet->fromArray(['Tổng', '', '', '', $totalQuantity, $totalWeight, '', $totalVolume, '', '', $totalDomesticFee, $totalOfficialQuotaFee, $totalVatTax, $totalImportTax, $totalOtherTax, '', $total], null, "A$row");
        $row++;
        $sheet->fromArray(['Phí giao hàng', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $invoice['shipping_fee']], null, "A$row");
        $row++;
        $sheet->fromArray(['Phí khác', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $invoice['other_fee']], null, "A$row");
        $row++;
        $sheet->fromArray(['Tổng cộng', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $total + $invoice['shipping_fee'] + $invoice['other_fee']], null, "A$row");

        // Định dạng tên file: PXK-customer_code-id_phieuxuat
        $filename = 'PXK-' . $customer['customer_code'] . '-' . $invoiceId . '.xlsx';

        // Xuất file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    public function exportInvoicesByFilter2()
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();
        $customerModel = new \App\Models\CustomerModel();

        // Lấy bộ lọc từ GET
        $filters = [
            'customer_code' => $this->request->getGet('customer_code') ?? 'ALL',
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
            'invoice_id' => $this->request->getGet('invoice_id'),
            'tracking_code' => $this->request->getGet('tracking_code'),
            'page' => $this->request->getGet('page') ?? 1
        ];

        $perPage = 30; // Số phiếu xuất mỗi trang, giống trong index()
        $currentPage = (int)$filters['page'];
        $offset = ($currentPage - 1) * $perPage;

        // Truy vấn danh sách phiếu xuất với phân trang
        $query = $invoiceModel
            ->select('invoices.*, customers.customer_code, customers.fullname, COUNT(orders.id) as order_count')
            ->join('customers', 'invoices.customer_id = customers.id', 'left')
            ->join('orders', 'invoices.id = orders.invoice_id', 'left')
            ->groupBy('invoices.id')
            ->orderBy('invoices.id', 'ASC'); // Sắp xếp theo ID tăng dần

        // Áp dụng các bộ lọc
        if (!empty($filters['invoice_id'])) {
            $query->where('invoices.id', $filters['invoice_id']);
        }
        if (!empty($filters['tracking_code'])) {
            $query->whereIn('invoices.id', function ($builder) use ($filters) {
                $builder->select('invoice_id')
                    ->from('orders')
                    ->where('tracking_code', $filters['tracking_code']);
            });
        }
        if ($filters['customer_code'] !== 'ALL') {
            $query->where('customers.customer_code', $filters['customer_code']);
        }
        if (!empty($filters['from_date'])) {
            $query->where('invoices.created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $query->where('invoices.created_at <=', $filters['to_date'] . ' 23:59:59');
        }

        // Lấy dữ liệu với phân trang
        $invoices = $query->limit($perPage, $offset)->findAll();
        if (empty($invoices)) {
            session()->setFlashdata('error', 'Không có phiếu xuất nào trong trang này.');
            return redirect()->to('/invoices');
        }

        // Tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: Danh sách đơn hàng
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Danh sách đơn hàng');

        $headers = [
            'ID Phiếu xuất',
            '#',
            'Ngày tạo phiếu',
            'Mã khách hàng',
            'Mã vận chuyển',
            'Mã bao',
            'Hàng',
            'Số lượng',
            'KL (kg)',
            'Dài',
            'Rộng',
            'Cao',
            'Khối m³',
            'Giá/kg',
            'Giá/khối',
            'Phí nội địa',
            'Tính theo',
            'Tổng giá'
        ];
        $sheet1->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($invoices as $invoice) {
            $orders = $orderModel
                ->select('orders.*, product_types.name AS product_type_name, sub_customers.sub_customer_code')
                ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
                ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
                ->where('orders.invoice_id', $invoice['id'])
                ->findAll();

            foreach ($orders as $index => $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
                $priceMethod = ($priceByWeight >= $priceByVolume) ? 'Cân nặng' : 'Thể tích';

                $sheet1->fromArray([
                    $invoice['id'],
                    $index + 1,
                    date('d/m/Y H:i', strtotime($invoice['created_at'])),
                    $invoice['customer_code'],
                    $order['sub_customer_code'] ?? '-',
                    $order['tracking_code'],
                    $order['package_code'],
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
                    $priceMethod,
                    $finalPrice
                ], null, "A$row");
                $row++;
            }
        }

        // Sheet 2: Phí phiếu xuất
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Phí phiếu xuất');

        $feeHeaders = [
            'ID Phiếu xuất',
            'Ngày tạo',
            'Mã khách hàng',
            'Số đơn hàng',
            'Phí giao hàng (VNĐ)',
            'Phí khác (VNĐ)',
            'Tổng tiền (VNĐ)',
            'Trạng thái giao',
            'Trạng thái thanh toán'
        ];
        $sheet2->fromArray($feeHeaders, null, 'A1');

        $row = 2;
        foreach ($invoices as $invoice) {
            $orders = $orderModel->where('invoice_id', $invoice['id'])->findAll();
            $total = 0;
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $total += max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
            }
            $grandTotal = $total + $invoice['shipping_fee'] + $invoice['other_fee'];

            $sheet2->fromArray([
                $invoice['id'],
                date('d/m/Y H:i', strtotime($invoice['created_at'])),
                $invoice['customer_code'],
                count($orders),
                $invoice['shipping_fee'],
                $invoice['other_fee'],
                $grandTotal,
                $invoice['shipping_status'] === 'confirmed' ? 'Đã giao' : 'Chờ giao',
                $invoice['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'
            ], null, "A$row");
            $row++;
        }

        // Định dạng tên file: PXK-ngày-tháng-năm-trang
        $filename = 'PXK-' . $filters['customer_code'] . '-' . count($invoices) . '-phieuxuat-trang-' . $currentPage . '.xlsx';

        // Xuất file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    public function exportInvoicesByFilter()
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();
        $customerModel = new \App\Models\CustomerModel();

        // Lấy bộ lọc từ GET
        $filters = [
            'customer_code' => $this->request->getGet('customer_code') ?? 'ALL',
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
            'invoice_id' => $this->request->getGet('invoice_id'),
            'tracking_code' => $this->request->getGet('tracking_code'),
            'page' => $this->request->getGet('page') ?? 1
        ];

        $perPage = 30; // Số phiếu xuất mỗi trang, giống trong index()
        $currentPage = (int)$filters['page'];
        $offset = ($currentPage - 1) * $perPage;

        // Truy vấn danh sách phiếu xuất với phân trang
        $query = $invoiceModel
            ->select('invoices.*, customers.customer_code, customers.fullname, COUNT(orders.id) as order_count')
            ->join('customers', 'invoices.customer_id = customers.id', 'left')
            ->join('orders', 'invoices.id = orders.invoice_id', 'left')
            ->groupBy('invoices.id')
            ->orderBy('invoices.id', 'DESC');

        // Áp dụng các bộ lọc
        if (!empty($filters['invoice_id'])) {
            $query->where('invoices.id', $filters['invoice_id']);
        }
        if (!empty($filters['tracking_code'])) {
            $query->whereIn('invoices.id', function ($builder) use ($filters) {
                $builder->select('invoice_id')
                    ->from('orders')
                    ->where('tracking_code', $filters['tracking_code']);
            });
        }
        if ($filters['customer_code'] !== 'ALL') {
            $query->where('customers.customer_code', $filters['customer_code']);
        }
        if (!empty($filters['from_date'])) {
            $query->where('invoices.created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (!empty($filters['to_date'])) {
            $query->where('invoices.created_at <=', $filters['to_date'] . ' 23:59:59');
        }

        // Lấy dữ liệu với phân trang
        $invoices = $query->limit($perPage, $offset)->findAll();
        if (empty($invoices)) {
            session()->setFlashdata('error', 'Không có phiếu xuất nào trong trang này.');
            return redirect()->to('/invoices');
        }

        // Tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: Danh sách đơn hàng
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Danh sách đơn hàng');

        $headers = [
            'ID Phiếu xuất',
            '#',
            'Ngày tạo phiếu',
            'Mã khách hàng',
            'Mã phụ',
            'Mã vận chuyển',
            'Mã bao',
            'Hàng',
            'Số lượng',
            'KL (kg)',
            'Dài',
            'Rộng',
            'Cao',
            'Khối m³',
            'Giá/kg',
            'Giá/khối',
            'Phí nội địa',
            'Phí Chính ngạch',
            'Thuế VAT',
            'Thuế Nhập khẩu',
            'Thuế khác',
            'Tính theo',
            'Tổng giá'
        ];
        $sheet1->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($invoices as $invoice) {
            $orders = $orderModel
                ->select('orders.*, product_types.name AS product_type_name, sub_customers.sub_customer_code')
                ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
                ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
                ->where('orders.invoice_id', $invoice['id'])
                ->findAll();

            foreach ($orders as $index => $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
                $priceMethod = ($priceByWeight >= $priceByVolume) ? 'Cân nặng' : 'Thể tích';

                // Ghi các giá trị khác bằng fromArray, để trống cột "Mã vận chuyển"
                $sheet1->fromArray([
                    $invoice['id'],
                    $index + 1,
                    date('d/m/Y H:i', strtotime($invoice['created_at'])),
                    $invoice['customer_code'],
                    $order['sub_customer_code'] ?? '-',
                    null, // Để trống cột "Mã vận chuyển", sẽ ghi riêng bằng setValueExplicit
                    $order['package_code'],
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
                    $priceMethod,
                    $finalPrice
                ], null, "A$row");

                // Ghi giá trị tracking_code bằng setValueExplicit để đảm bảo là text
                if (!empty($order['tracking_code'])) {
                    $sheet1->getCell('F' . $row)
                        ->setValueExplicit(
                            $order['tracking_code'],
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                }

                // Định dạng cột "Mã vận chuyển" (cột F) là Text
                $sheet1->getCell('F' . $row)->getStyle()->getNumberFormat()->setFormatCode('@');

                $row++;
            }
        }

        // Định dạng toàn bộ cột "Mã vận chuyển" (cột F) là Text
        $lastRow = $sheet1->getHighestRow();
        $sheet1->getStyle('F2:F' . $lastRow)->getNumberFormat()->setFormatCode('@');

        // Định dạng cột "Khối m³" (cột M) để hiển thị 3 chữ số sau dấu phẩy
        $sheet1->getStyle('M2:M' . ($lastRow))
            ->getNumberFormat()
            ->setFormatCode('0.000');

        // Tự động điều chỉnh độ rộng cột cho sheet 1
        foreach (range('A', 'R') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Sheet 2: Phí phiếu xuất
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Phí phiếu xuất');

        $feeHeaders = [
            'ID Phiếu xuất',
            'Ngày tạo',
            'Mã khách hàng',
            'Số đơn hàng',
            'Phí giao hàng (VNĐ)',
            'Phí khác (VNĐ)',
            'Tổng tiền (VNĐ)',
            'Trạng thái giao',
            'Trạng thái thanh toán'
        ];
        $sheet2->fromArray($feeHeaders, null, 'A1');

        $row = 2;
        foreach ($invoices as $invoice) {
            $orders = $orderModel->where('invoice_id', $invoice['id'])->findAll();
            $total = 0;
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $total += max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
            }
            $grandTotal = $total + $invoice['shipping_fee'] + $invoice['other_fee'];

            $sheet2->fromArray([
                $invoice['id'],
                date('d/m/Y H:i', strtotime($invoice['created_at'])),
                $invoice['customer_code'],
                count($orders),
                $invoice['shipping_fee'],
                $invoice['other_fee'],
                $grandTotal,
                $invoice['shipping_status'] === 'confirmed' ? 'Đã giao' : 'Chờ giao',
                $invoice['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'
            ], null, "A$row");

            $row++;
        }

        // Tự động điều chỉnh độ rộng cột cho sheet 2
        foreach (range('A', 'I') as $columnID) {
            $sheet2->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Định dạng tên file: PXK-ngày-tháng-năm-trang
        $filename = 'PXK-' . $filters['customer_code'] . '-' . count($invoices) . '-phieuxuat-trang-' . $currentPage . '.xlsx';

        // Xuất file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }

    public function exportExcelBySelect()
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $orderModel = new \App\Models\OrderModel();
        $customerModel = new \App\Models\CustomerModel();

        // Lấy danh sách ID của các phiếu đã chọn từ query string
        $ids = $this->request->getVar('ids');
        if (empty($ids)) {
            session()->setFlashdata('error', 'Bạn chưa chọn phiếu nào để xuất Excel.');
            return redirect()->to('/invoices');
        }

        // Chia tách các ID thành mảng
        $idsArray = explode(',', $ids);

        // Truy vấn danh sách phiếu xuất dựa trên ID đã chọn
        $invoices = $invoiceModel
            ->select('invoices.*, customers.customer_code, customers.fullname, COUNT(orders.id) as order_count')
            ->join('customers', 'invoices.customer_id = customers.id', 'left')
            ->join('orders', 'invoices.id = orders.invoice_id', 'left')
            ->whereIn('invoices.id', $idsArray)
            ->groupBy('invoices.id')
            ->orderBy('invoices.id', 'DESC')
            ->findAll();

        if (empty($invoices)) {
            session()->setFlashdata('error', 'Không có phiếu xuất nào được tìm thấy.');
            return redirect()->to('/invoices');
        }

        // Tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: Danh sách đơn hàng
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Danh sách đơn hàng');

        $headers = [
            'ID Phiếu xuất',
            '#',
            'Ngày tạo phiếu',
            'Mã khách hàng',
            'Mã phụ',
            'Mã vận chuyển',
            'Mã bao',
            'Hàng',
            'Số lượng',
            'KL (kg)',
            'Dài',
            'Rộng',
            'Cao',
            'Khối m³',
            'Giá/kg',
            'Giá/khối',
            'Phí nội địa',
            'Tính theo',
            'Tổng giá'
        ];
        $sheet1->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($invoices as $invoice) {
            $orders = $orderModel
                ->select('orders.*, product_types.name AS product_type_name, sub_customers.sub_customer_code')
                ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
                ->join('sub_customers', 'sub_customers.id = orders.sub_customer_id', 'left')
                ->where('orders.invoice_id', $invoice['id'])
                ->findAll();

            foreach ($orders as $index => $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']) + $order['official_quota_fee'] + $order['vat_tax'] + $order['import_tax'] + $order['other_tax'];
                $priceMethod = ($priceByWeight >= $priceByVolume) ? 'Cân nặng' : 'Thể tích';

                // Ghi các giá trị khác bằng fromArray, để trống cột "Mã vận chuyển"
                $sheet1->fromArray([
                    $invoice['id'],
                    $index + 1,
                    date('d/m/Y H:i', strtotime($invoice['created_at'])),
                    $invoice['customer_code'],
                    $order['sub_customer_code'] ?? '-',
                    null, // Để trống cột "Mã vận chuyển", sẽ ghi riêng bằng setValueExplicit
                    $order['package_code'],
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
                    $order['official_quota_fee'],
                    $order['vat_tax'],
                    $order['import_tax'],
                    $order['other_tax'],
                    $priceMethod,
                    $finalPrice
                ], null, "A$row");

                // Ghi giá trị tracking_code bằng setValueExplicit để đảm bảo là text
                if (!empty($order['tracking_code'])) {
                    $sheet1->getCell('F' . $row)
                        ->setValueExplicit(
                            $order['tracking_code'],
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                }

                // Định dạng cột "Mã vận chuyển" (cột F) là Text
                $sheet1->getCell('F' . $row)->getStyle()->getNumberFormat()->setFormatCode('@');

                $row++;
            }
        }

        // Định dạng toàn bộ cột "Mã vận chuyển" (cột F) là Text
        $lastRow = $sheet1->getHighestRow();
        $sheet1->getStyle('F2:F' . $lastRow)->getNumberFormat()->setFormatCode('@');

        // Tự động điều chỉnh độ rộng cột cho sheet 1
        foreach (range('A', 'R') as $columnID) {
            $sheet1->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Sheet 2: Phí phiếu xuất
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Phí phiếu xuất');

        $feeHeaders = [
            'ID Phiếu xuất',
            'Ngày tạo',
            'Mã khách hàng',
            'Số đơn hàng',
            'Phí giao hàng (VNĐ)',
            'Phí khác (VNĐ)',
            'Tổng tiền (VNĐ)',
            'Trạng thái giao',
            'Trạng thái thanh toán'
        ];
        $sheet2->fromArray($feeHeaders, null, 'A1');

        $row = 2;
        foreach ($invoices as $invoice) {
            $orders = $orderModel->where('invoice_id', $invoice['id'])->findAll();
            $total = 0;
            foreach ($orders as $order) {
                $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                $domesticFee = $order['domestic_fee'] * $order['exchange_rate'];
                $officialQuotaFee = $order['official_quota_fee'];
                $vatTax = $order['vat_tax'];
                $importTax = $order['import_tax'];
                $otherTax = $order['other_tax'];

                $total += max($priceByWeight, $priceByVolume) +
                    $domesticFee +
                    $officialQuotaFee +
                    $vatTax +
                    $importTax +
                    $otherTax;
            }
            $grandTotal = $total + $invoice['shipping_fee'] + $invoice['other_fee'];

            $sheet2->fromArray([
                $invoice['id'],
                date('d/m/Y H:i', strtotime($invoice['created_at'])),
                $invoice['customer_code'],
                count($orders),
                $invoice['shipping_fee'],
                $invoice['other_fee'],
                $grandTotal,
                $invoice['shipping_status'] === 'confirmed' ? 'Đã giao' : 'Chờ giao',
                $invoice['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'
            ], null, "A$row");

            $row++;
        }

        // Tự động điều chỉnh độ rộng cột cho sheet 2
        foreach (range('A', 'I') as $columnID) {
            $sheet2->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Định dạng tên file: PXK-ngày-tháng-năm-trang
        $filename = 'PXK-SELECT-' . count($invoices) . '-phieuxuat.xlsx';

        // Xuất file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
}
