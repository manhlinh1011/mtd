<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\CustomerTransactionModel;
use App\Models\InvoiceModel; // Thêm model cho invoices nếu cần
use App\Models\OrderModel;
use App\Models\SubCustomerModel;
use App\Models\FundModel;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $customerTransactionModel;
    protected $invoiceModel;
    protected $orderModel;
    protected $subCustomerModel;
    protected $db;
    protected $fundModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->customerTransactionModel = new CustomerTransactionModel();
        $this->invoiceModel = new InvoiceModel(); // Khởi tạo nếu cần
        $this->orderModel = new OrderModel();     // Khởi tạo nếu cần
        $this->subCustomerModel = new SubCustomerModel();
        $this->db = \Config\Database::connect();
        $this->fundModel = new FundModel();
    }

    public function index()
    {
        // Sử dụng phương thức tùy chỉnh để lấy danh sách khách hàng kèm số đơn hàng
        $data['customers'] = $this->customerModel->getCustomersWithOrderCount();

        // Truyền dữ liệu sang view
        return view('customers/index', $data);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            // Quy định các rule kiểm tra dữ liệu
            $rules = [
                'fullname' => 'required|min_length[3]|max_length[100]',
                'phone' => 'required|regex_match[/^[0-9]{10,15}$/]',
                'address' => 'required|min_length[5]|max_length[255]',
                'zalo_link' => 'permit_empty|valid_url',
                'email' => 'permit_empty|valid_email',
                'customer_code' => 'required|regex_match[/^[\p{L}0-9\- ]+$/u]|max_length[50]|is_unique[customers.customer_code]',
                'price_per_kg' => 'required|integer|greater_than_equal_to[0]', // Kiểm tra giá 1kg
                'price_per_cubic_meter' => 'required|integer|greater_than_equal_to[0]' // Kiểm tra giá 1 mét khối
            ];

            // Quy định các thông báo lỗi
            $errors = [
                'fullname' => [
                    'required' => 'Họ và Tên là bắt buộc.',
                    'min_length' => 'Họ và Tên phải có ít nhất 3 ký tự.',
                    'max_length' => 'Họ và Tên không được vượt quá 100 ký tự.'
                ],
                'phone' => [
                    'required' => 'Số Điện Thoại là bắt buộc.',
                    'regex_match' => 'Số Điện Thoại phải là số và có độ dài từ 10 đến 15 ký tự.'
                ],
                'address' => [
                    'required' => 'Địa chỉ là bắt buộc.',
                    'min_length' => 'Địa chỉ phải có ít nhất 5 ký tự.',
                    'max_length' => 'Địa chỉ không được vượt quá 255 ký tự.'
                ],
                'zalo_link' => [
                    'valid_url' => 'Link Zalo phải là một URL hợp lệ.'
                ],
                'email' => [
                    'valid_email' => 'Email phải là một địa chỉ email hợp lệ.'
                ],
                'customer_code' => [
                    'required' => 'Mã Khách Hàng là bắt buộc.',
                    'regex_match' => 'Mã Khách Hàng chỉ được chứa chữ cái, số và dấu gạch ngang.',
                    'is_unique' => 'Mã Khách Hàng đã tồn tại trong hệ thống.'
                ],
                'price_per_kg' => [
                    'required' => 'Giá cho 1kg là bắt buộc.',
                    'integer' => 'Giá cho 1kg phải là số nguyên.',
                    'greater_than_equal_to' => 'Giá cho 1kg không được nhỏ hơn 0.'
                ],
                'price_per_cubic_meter' => [
                    'required' => 'Giá cho 1 mét khối là bắt buộc.',
                    'integer' => 'Giá cho 1 mét khối phải là số nguyên.',
                    'greater_than_equal_to' => 'Giá cho 1 mét khối không được nhỏ hơn 0.'
                ]
            ];

            // Kiểm tra dữ liệu
            if (!$this->validate($rules, $errors)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Nếu dữ liệu hợp lệ, lưu vào cơ sở dữ liệu
            $data = [
                'fullname' => $this->request->getPost('fullname'),
                'phone' => $this->request->getPost('phone'),
                'address' => $this->request->getPost('address'),
                'zalo_link' => $this->request->getPost('zalo_link'),
                'email' => $this->request->getPost('email'),
                'customer_code' => $this->request->getPost('customer_code'),
                'price_per_kg' => $this->request->getPost('price_per_kg'),
                'price_per_cubic_meter' => $this->request->getPost('price_per_cubic_meter'),
                'payment_limit_days' => $this->request->getPost('payment_limit_days') ?? 15 // Mặc định là 15 ngày
            ];

            if ($this->customerModel->insert($data)) {
                return redirect()->to('/customers')->with('success', 'Thêm khách hàng thành công.');
            } else {
                return redirect()->back()->with('error', 'Thêm khách hàng thất bại. Vui lòng thử lại.');
            }
        }

        //echo "Lỗi";
        return view('customers/create');
    }


    public function edit($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Khách hàng không tồn tại.");
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'fullname' => 'required|min_length[3]|max_length[100]',
                'phone' => 'required|regex_match[/^[0-9]{10,15}$/]',
                'address' => 'required|min_length[5]|max_length[255]',
                'zalo_link' => 'permit_empty|valid_url',
                'email' => 'permit_empty|valid_email',
                'price_per_kg' => 'required|integer|greater_than_equal_to[0]',
                'price_per_cubic_meter' => 'required|integer|greater_than_equal_to[0]',
                'payment_limit_days' => 'required|integer|greater_than_equal_to[1]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            $data = [
                'fullname' => $this->request->getPost('fullname'),
                'phone' => $this->request->getPost('phone'),
                'address' => $this->request->getPost('address'),
                'zalo_link' => $this->request->getPost('zalo_link'),
                'email' => $this->request->getPost('email'),
                'price_per_kg' => $this->request->getPost('price_per_kg'),
                'price_per_cubic_meter' => $this->request->getPost('price_per_cubic_meter'),
                'payment_limit_days' => $this->request->getPost('payment_limit_days') ?? 15
            ];

            if ($this->customerModel->update($id, $data)) {
                cache()->delete("order_stats_$id");
                cache()->delete("invoice_stats_$id");
                return redirect()->to('/customers')->with('success', 'Cập nhật khách hàng thành công.');
            } else {
                return redirect()->back()->with('error', 'Cập nhật khách hàng thất bại.');
            }
        }

        // Lấy thống kê đơn hàng từ cache hoặc database
        $orderCacheKey = "order_stats_$id";
        if (!($orderStats = cache($orderCacheKey))) {
            $orderStats = $this->orderModel->getOrderStatsByCustomer($id);
            cache()->save($orderCacheKey, $orderStats, 3600); // Cache trong 1 giờ
        }
        $totalOrders = $orderStats['total_orders'];
        $chinaStock = $orderStats['china_stock'];
        $stockOrders = $orderStats['in_stock'];
        $pendingShipping = $orderStats['pending_shipping'];
        $shippedOrders = $orderStats['shipped'];

        // Lấy thống kê phiếu xuất từ cache hoặc database
        $invoiceCacheKey = "invoice_stats_$id";
        if (!($invoiceStats = cache($invoiceCacheKey))) {
            $invoiceStats = $this->invoiceModel->getInvoiceStatsByCustomer($id);
            cache()->save($invoiceCacheKey, $invoiceStats, 3600); // Cache trong 1 giờ
        }
        $totalInvoices = $invoiceStats['total_invoices'];
        $paidInvoices = $invoiceStats['paid_invoices'];
        $unpaidInvoices = $invoiceStats['unpaid_invoices'];
        $deliveredInvoices = $invoiceStats['delivered_invoices'];
        $pendingInvoices = $invoiceStats['pending_invoices'];

        // Truyền dữ liệu sang view
        $data = [
            'customer' => $customer,
            'totalOrders' => $totalOrders,
            'chinaStock' => $chinaStock,
            'stockOrders' => $stockOrders,
            'pendingShipping' => $pendingShipping,
            'shippedOrders' => $shippedOrders,
            'totalInvoices' => $totalInvoices,
            'paidInvoices' => $paidInvoices,
            'unpaidInvoices' => $unpaidInvoices,
            'deliveredInvoices' => $deliveredInvoices,
            'pendingInvoices' => $pendingInvoices
        ];

        return view('customers/edit', $data);
    }


    public function delete($id)
    {
        $this->customerModel->delete($id);
        return redirect()->to('/customers');
    }

    public function updateBulk()
    {
        // Lấy dữ liệu từ form
        $customers = $this->request->getPost('customers');
        $updatedCount = 0; // Biến đếm số lượng khách hàng được cập nhật

        if ($customers && is_array($customers)) {
            foreach ($customers as $id => $data) {
                // Kiểm tra dữ liệu hợp lệ trước khi cập nhật
                if (isset($data['price_per_kg'], $data['price_per_cubic_meter'])) {
                    $updateData = [
                        'price_per_kg' => (int)$data['price_per_kg'],
                        'price_per_cubic_meter' => (int)$data['price_per_cubic_meter'],
                    ];

                    // Kiểm tra nếu có thay đổi thực tế trong dữ liệu
                    $currentCustomer = $this->customerModel->find($id);
                    if (
                        $currentCustomer['price_per_kg'] != $updateData['price_per_kg'] ||
                        $currentCustomer['price_per_cubic_meter'] != $updateData['price_per_cubic_meter']
                    ) {
                        $this->customerModel->update($id, $updateData);
                        $updatedCount++;
                    }
                }
            }
            return redirect()->to('/customers')->with('success', "Cập nhật giá hàng loạt thành công $updatedCount khách hàng.");
        }

        return redirect()->to('/customers')->with('error', 'Không có dữ liệu để cập nhật.');
    }

    public function detail($id)
    {
        try {
            // Lấy thông tin khách hàng
            $customer = $this->customerModel->find($id);
            if (!$customer) {
                return redirect()->to('/customers')->with('error', 'Không tìm thấy khách hàng');
            }

            // Lấy số dư từ model
            $customer['balance'] = $this->customerModel->getCustomerBalance($id);

            // Lấy thống kê đơn hàng và phiếu xuất từ cache hoặc database
            $orderCacheKey = "order_stats_$id";
            $invoiceCacheKey = "invoice_stats_$id";

            $orderStats = cache($orderCacheKey) ?: $this->orderModel->getOrderStatsByCustomer($id);
            $invoiceStats = cache($invoiceCacheKey) ?: $this->invoiceModel->getInvoiceStatsByCustomer($id);

            if (!cache($orderCacheKey)) cache()->save($orderCacheKey, $orderStats, 3600);
            if (!cache($invoiceCacheKey)) cache()->save($invoiceCacheKey, $invoiceStats, 3600);

            // Lấy tổng tiền nạp và thanh toán trong một query
            $transactionSums = $this->db->table('customer_transactions')
                ->select('
                    SUM(CASE WHEN transaction_type = "deposit" THEN amount ELSE 0 END) as total_deposit,
                    SUM(CASE WHEN transaction_type = "payment" THEN amount ELSE 0 END) as total_payment
                ')
                ->where('customer_id', $id)
                ->get()
                ->getRow();

            $totalDeposit = $transactionSums->total_deposit ?? 0;
            $totalPayment = $transactionSums->total_payment ?? 0;

            // Lấy danh sách giao dịch
            $transactions = $this->db->table('customer_transactions ct')
                ->select('
                    ct.*,
                    u.fullname as created_by_name,
                    i.id as invoice_id
                ')
                ->join('users u', 'u.id = ct.created_by', 'left')
                ->join('invoices i', 'i.id = ct.invoice_id', 'left')
                ->where('ct.customer_id', $id)
                ->orderBy('ct.created_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            // Lấy danh sách đơn hàng
            $orders = $this->orderModel
                ->select('orders.*, i.id as invoice_id, i.created_at as invoice_date')
                ->join('invoices i', 'i.id = orders.invoice_id', 'left')
                ->where('orders.customer_id', $id)
                ->orderBy('orders.created_at', 'DESC')
                ->findAll();

            // Lấy danh sách khách hàng phụ
            $subCustomers = $this->subCustomerModel->where('customer_id', $id)->findAll();

            // Tính số đơn hàng và số phiếu xuất cho mỗi mã phụ
            foreach ($subCustomers as &$subCustomer) {
                // Đếm số đơn hàng cho mỗi mã phụ
                $subCustomer['order_count'] = $this->orderModel
                    ->where('orders.sub_customer_id', $subCustomer['id'])
                    ->countAllResults();

                // Đếm số phiếu xuất liên quan đến mã phụ này
                $invoiceIds = $this->orderModel
                    ->distinct()
                    ->select('invoice_id')
                    ->where('sub_customer_id', $subCustomer['id'])
                    ->where('invoice_id IS NOT NULL')
                    ->findAll();

                $uniqueInvoiceIds = array_unique(array_column($invoiceIds, 'invoice_id'));
                $subCustomer['invoice_count'] = count($uniqueInvoiceIds);

                // Đếm số phiếu xuất đã thanh toán
                $paidInvoiceCount = 0;
                if (!empty($uniqueInvoiceIds)) {
                    $paidInvoiceCount = $this->invoiceModel
                        ->whereIn('id', $uniqueInvoiceIds)
                        ->where('payment_status', 'paid')
                        ->countAllResults();
                }

                $subCustomer['paid_invoice_count'] = $paidInvoiceCount;
            }

            // Lấy tất cả đơn hàng của các phiếu xuất trong một query
            $allInvoiceIds = array_column($subCustomers, 'id');
            if (!empty($allInvoiceIds)) {
                $allOrders = $this->orderModel
                    ->whereIn('sub_customer_id', $allInvoiceIds)
                    ->findAll();
            }

            // Lấy tất cả phiếu xuất với thông tin cần thiết
            $baseInvoiceQuery = $this->db->table('invoices i')
                ->select('
                    i.*,
                    u.fullname as created_by_name,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(o.total_weight) as total_weight,
                    SUM(o.volume) as total_volume
                ')
                ->join('users u', 'u.id = i.created_by', 'left')
                ->join('orders o', 'o.invoice_id = i.id', 'left')
                ->where('i.customer_id', $id)
                ->groupBy('i.id')
                ->orderBy('i.created_at', 'DESC');

            // Lấy tất cả phiếu xuất
            $invoices = (clone $baseInvoiceQuery)->get()->getResultArray();

            // Khởi tạo các biến mặc định
            $recentInvoices = [];
            $allOrders = [];
            $groupedOrders = [];

            // Lấy tất cả đơn hàng của các phiếu xuất trong một query
            $allInvoiceIds = array_column($invoices, 'id');
            if (!empty($allInvoiceIds)) {
                $allOrders = $this->orderModel
                    ->whereIn('invoice_id', $allInvoiceIds)
                    ->findAll();

                // Nhóm đơn hàng theo invoice_id để dễ truy cập
                foreach ($allOrders as $order) {
                    $groupedOrders[$order['invoice_id']][] = $order;
                }

                // Hàm tính tổng tiền cho một phiếu xuất
                $calculateInvoiceTotal = function ($invoice, $orders) {
                    $total = 0;
                    foreach ($orders as $order) {
                        $totalWeight = floatval($order['total_weight'] ?? 0);
                        $pricePerKg = floatval($order['price_per_kg'] ?? 0);
                        $volume = floatval($order['volume'] ?? 0);
                        $pricePerCubicMeter = floatval($order['price_per_cubic_meter'] ?? 0);
                        $domesticFee = floatval($order['domestic_fee'] ?? 0);
                        $exchangeRate = floatval($order['exchange_rate'] ?? 0);

                        $priceByWeight = $totalWeight * $pricePerKg;
                        $priceByVolume = $volume * $pricePerCubicMeter;
                        $finalPrice = max($priceByWeight, $priceByVolume) + ($domesticFee * $exchangeRate);
                        $total += $finalPrice;
                    }
                    return $total + floatval($invoice['shipping_fee']) + floatval($invoice['other_fee']);
                };

                // Tính tổng tiền cho tất cả phiếu xuất
                foreach ($invoices as &$invoice) {
                    $invoiceOrders = $groupedOrders[$invoice['id']] ?? [];
                    $invoice['total_amount'] = $calculateInvoiceTotal($invoice, $invoiceOrders);
                }

                // Lấy 10 phiếu xuất gần nhất từ danh sách đã có
                $recentInvoices = array_slice($invoices, 0, 10);
            }

            $funds = $this->fundModel->findAll();

            return view('customers/detail', [
                'customer' => $customer,
                'transactions' => $transactions,
                'orders' => $orders,
                'invoices' => $invoices,
                'recentInvoices' => $recentInvoices,
                'totalDeposit' => $totalDeposit,
                'totalPayment' => $totalPayment,
                'orderStats' => $orderStats,
                'invoiceStats' => $invoiceStats,
                'subCustomers' => $subCustomers,
                'funds' => $funds
            ]);
        } catch (\Exception $e) {
            log_message('error', '[CustomerController::detail] Error: ' . $e->getMessage());
            return redirect()->to('/customers')->with('error', 'Có lỗi xảy ra khi tải thông tin khách hàng: ' . $e->getMessage());
        }
    }

    public function deposit($id)
    {
        $rules = [
            'amount' => 'required|numeric|greater_than[0]',
            'fund_id' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $amount = $this->request->getPost('amount');
        $fundId = $this->request->getPost('fund_id');
        $notes = $this->request->getPost('notes');

        // Tạo giao dịch nạp tiền
        $transactionData = [
            'customer_id' => $id,
            'fund_id' => $fundId,
            'transaction_type' => 'deposit',
            'amount' => $amount,
            'notes' => $notes,
            'created_by' => session()->get('user_id')
        ];

        $transactionModel = new CustomerTransactionModel();
        $transactionModel->insert($transactionData);

        // Cập nhật số dư khách hàng
        $customerModel = new CustomerModel();
        $customer = $customerModel->find($id);
        $newBalance = $customer['balance'] + $amount;
        $customerModel->update($id, ['balance' => $newBalance]);

        return redirect()->back()->with('success', 'Nạp tiền thành công');
    }

    public function invoices($id)
    {
        // Lấy thông tin khách hàng
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Khách hàng không tồn tại.');
        }

        // Lấy các tham số lọc
        $filters = [
            'shipping_status' => $this->request->getGet('shipping_status'),
            'payment_status' => $this->request->getGet('payment_status')
        ];
        $page = $this->request->getGet('page') ?? 1;

        // Lấy danh sách phiếu xuất từ Model
        $result = $this->customerModel->getCustomerInvoices($id, $filters, $page);

        // Truyền dữ liệu sang view
        $data = [
            'customer' => $customer,
            'invoices' => $result['invoices'],
            'pager' => $result['pager'],
            'shipping_status' => $filters['shipping_status'],
            'payment_status' => $filters['payment_status']
        ];

        return view('customers/invoices', $data);
    }

    public function search()
    {
        $code = $this->request->getGet('name');
        $customerModel = new CustomerModel();

        // Tìm kiếm khách hàng theo customer_code
        $customers = $customerModel->like('customer_code', $code)->findAll();

        // Trả về dữ liệu dưới dạng JSON
        return $this->response->setJSON($customers);
    }

    public function subCustomerIndex()
    {
        $customerModel = new \App\Models\CustomerModel();
        $subCustomerModel = new \App\Models\SubCustomerModel();

        // Lấy danh sách khách hàng để hiển thị trong bộ lọc
        $customers = $customerModel->findAll();

        // Xử lý bộ lọc
        $customerIdFilter = $this->request->getGet('customer_id');
        $query = $subCustomerModel
            ->select('sub_customers.*, customers.fullname as customer_fullname, customers.customer_code as customer_code')
            ->join('customers', 'customers.id = sub_customers.customer_id', 'left');

        if ($customerIdFilter) {
            $query->where('sub_customers.customer_id', $customerIdFilter);
        }

        $subCustomers = $query->findAll();

        // Lấy số đơn hàng và phiếu xuất cho mỗi mã phụ
        foreach ($subCustomers as &$subCustomer) {
            // Đếm số đơn hàng
            $subCustomer['order_count'] = $this->orderModel
                ->where('orders.sub_customer_id', $subCustomer['id'])
                ->countAllResults();

            // Đếm số phiếu xuất và phiếu đã thanh toán
            $invoiceStats = $this->invoiceModel
                ->select('COUNT(*) as invoice_count, SUM(CASE WHEN invoices.payment_status = "paid" THEN 1 ELSE 0 END) as paid_invoice_count')
                ->join('orders', 'orders.invoice_id = invoices.id', 'left')
                ->where('invoices.customer_id', $subCustomer['customer_id']) // Chỉ định rõ invoices.customer_id
                ->where('orders.sub_customer_id', $subCustomer['id'])
                ->groupBy('invoices.id')
                ->findAll();

            $subCustomer['invoice_count'] = count($invoiceStats);
            $subCustomer['paid_invoice_count'] = array_sum(array_column($invoiceStats, 'paid_invoice_count'));
        }

        return view('customers/sub_index', [
            'subCustomers' => $subCustomers,
            'customers' => $customers,
            'selectedCustomerId' => $customerIdFilter
        ]);
    }

    public function subCustomerCreate()
    {
        $customerModel = new \App\Models\CustomerModel();
        $customers = $customerModel->findAll(); // Lấy tất cả khách hàng để hiển thị trong dropdown

        return view('customers/sub_create', ['customers' => $customers]);
    }

    public function subCustomerStore()
    {
        $customerModel = new \App\Models\CustomerModel();
        $subCustomerModel = new \App\Models\SubCustomerModel();

        if ($this->request->getMethod() === 'POST') {
            // Quy định các rule kiểm tra dữ liệu
            $rules = [
                'customer_id' => 'required|integer|is_not_unique[customers.id]', // Chỉ định rõ bảng customers
                'sub_customer_code' => 'required|regex_match[/^[\p{L}0-9\- ]+$/u]|max_length[50]|is_unique[sub_customers.sub_customer_code]',
                'fullname' => 'required|min_length[3]|max_length[100]',
                'phone' => 'required|regex_match[/^[0-9]{10,15}$/]',
                'address' => 'required|min_length[5]|max_length[255]',
                'zalo_link' => 'permit_empty|valid_url',
                'email' => 'permit_empty|valid_email'
            ];

            // Quy định các thông báo lỗi
            $errors = [
                'customer_id' => [
                    'required' => 'Vui lòng chọn khách hàng chính.',
                    'is_not_unique' => 'Khách hàng được chọn không tồn tại.'
                ],
                'sub_customer_code' => [
                    'required' => 'Mã phụ là bắt buộc.',
                    'regex_match' => 'Mã phụ chỉ được chứa chữ cái, số và dấu gạch ngang.',
                    'max_length' => 'Mã phụ không được vượt quá 50 ký tự.',
                    'is_unique' => 'Mã phụ đã tồn tại trong hệ thống.'
                ],
                'fullname' => [
                    'required' => 'Họ và Tên là bắt buộc.',
                    'min_length' => 'Họ và Tên phải có ít nhất 3 ký tự.',
                    'max_length' => 'Họ và Tên không được vượt quá 100 ký tự.'
                ],
                'phone' => [
                    'required' => 'Số Điện Thoại là bắt buộc.',
                    'regex_match' => 'Số Điện Thoại phải là số và có độ dài từ 10 đến 15 ký tự.'
                ],
                'address' => [
                    'required' => 'Địa chỉ là bắt buộc.',
                    'min_length' => 'Địa chỉ phải có ít nhất 5 ký tự.',
                    'max_length' => 'Địa chỉ không được vượt quá 255 ký tự.'
                ],
                'zalo_link' => [
                    'valid_url' => 'Link Zalo phải là một URL hợp lệ.'
                ],
                'email' => [
                    'valid_email' => 'Email phải là một địa chỉ email hợp lệ.'
                ]
            ];

            // Kiểm tra dữ liệu
            if (!$this->validate($rules, $errors)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Nếu dữ liệu hợp lệ, lưu vào cơ sở dữ liệu
            $data = [
                'customer_id' => $this->request->getPost('customer_id'),
                'sub_customer_code' => $this->request->getPost('sub_customer_code'),
                'fullname' => $this->request->getPost('fullname'),
                'phone' => $this->request->getPost('phone'),
                'address' => $this->request->getPost('address'),
                'zalo_link' => $this->request->getPost('zalo_link'),
                'email' => $this->request->getPost('email')
            ];

            // Kiểm tra khách hàng chính tồn tại
            $customer = $customerModel->find($data['customer_id']);
            if (!$customer) {
                return redirect()->back()->with('error', 'Khách hàng chính không tồn tại.');
            }

            // Thêm mã phụ
            if ($subCustomerModel->insert($data)) {
                return redirect()->to('/customers/sub-customers')->with('success', 'Thêm mã phụ thành công.');
            } else {
                return redirect()->back()->with('error', 'Thêm mã phụ thất bại. Vui lòng thử lại.');
            }
        }
    }

    public function subCustomerEdit($subCustomerId)
    {
        $subCustomerModel = new \App\Models\SubCustomerModel();
        $customerModel = new \App\Models\CustomerModel();

        $subCustomer = $subCustomerModel->find($subCustomerId);
        if (!$subCustomer) {
            return redirect()->to('/customers/sub-customers')->with('error', 'Mã phụ không tồn tại.');
        }

        $customer = $customerModel->find($subCustomer['customer_id']);
        if (!$customer) {
            return redirect()->to('/customers/sub-customers')->with('error', 'Khách hàng chính không tồn tại.');
        }

        if ($this->request->getMethod() === 'POST') {
            // Quy định các rule kiểm tra dữ liệu
            $rules = [
                'sub_customer_code' => 'required|regex_match[/^[\p{L}0-9\- ]+$/u]|max_length[50]|is_unique[sub_customers.sub_customer_code,id,' . $subCustomerId . ']',
                'fullname' => 'required|min_length[3]|max_length[100]',
                'phone' => 'required|regex_match[/^[0-9]{10,15}$/]',
                'address' => 'required|min_length[5]|max_length[255]',
                'zalo_link' => 'permit_empty|valid_url',
                'email' => 'permit_empty|valid_email'
            ];

            // Quy định các thông báo lỗi
            $errors = [
                'sub_customer_code' => [
                    'required' => 'Mã phụ là bắt buộc.',
                    'regex_match' => 'Mã phụ chỉ được chứa chữ cái, số và dấu gạch ngang.',
                    'max_length' => 'Mã phụ không được vượt quá 50 ký tự.',
                    'is_unique' => 'Mã phụ đã tồn tại trong hệ thống.'
                ],
                'fullname' => [
                    'required' => 'Họ và Tên là bắt buộc.',
                    'min_length' => 'Họ và Tên phải có ít nhất 3 ký tự.',
                    'max_length' => 'Họ và Tên không được vượt quá 100 ký tự.'
                ],
                'phone' => [
                    'required' => 'Số Điện Thoại là bắt buộc.',
                    'regex_match' => 'Số Điện Thoại phải là số và có độ dài từ 10 đến 15 ký tự.'
                ],
                'address' => [
                    'required' => 'Địa chỉ là bắt buộc.',
                    'min_length' => 'Địa chỉ phải có ít nhất 5 ký tự.',
                    'max_length' => 'Địa chỉ không được vượt quá 255 ký tự.'
                ],
                'zalo_link' => [
                    'valid_url' => 'Link Zalo phải là một URL hợp lệ.'
                ],
                'email' => [
                    'valid_email' => 'Email phải là một địa chỉ email hợp lệ.'
                ]
            ];

            // Kiểm tra dữ liệu
            if (!$this->validate($rules, $errors)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Dữ liệu cập nhật
            $data = [
                'sub_customer_code' => $this->request->getPost('sub_customer_code'),
                'fullname' => $this->request->getPost('fullname'),
                'phone' => $this->request->getPost('phone'),
                'address' => $this->request->getPost('address'),
                'zalo_link' => $this->request->getPost('zalo_link'),
                'email' => $this->request->getPost('email')
            ];

            if ($subCustomerModel->update($subCustomerId, $data)) {
                return redirect()->to('/customers/sub-customers')->with('success', 'Cập nhật mã phụ thành công.');
            } else {
                return redirect()->back()->with('error', 'Cập nhật mã phụ thất bại.');
            }
        }

        // Lấy thông tin bổ sung (nếu cần)
        $orderModel = new \App\Models\OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();

        $totalOrders = $orderModel->where('sub_customer_id', $subCustomerId)->countAllResults();
        $invoiceStats = $invoiceModel
            ->select('COUNT(*) as total_invoices, SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_invoices')
            ->join('orders', 'orders.invoice_id = invoices.id', 'left')
            ->where('orders.sub_customer_id', $subCustomerId)
            ->groupBy('invoices.id')
            ->findAll();

        $totalInvoices = count($invoiceStats);
        $paidInvoices = array_sum(array_column($invoiceStats, 'paid_invoices'));

        return view('customers/sub_edit', [
            'subCustomer' => $subCustomer,
            'customer' => $customer,
            'totalOrders' => $totalOrders,
            'totalInvoices' => $totalInvoices,
            'paidInvoices' => $paidInvoices
        ]);
    }

    public function subCustomerDelete($subCustomerId)
    {
        $subCustomerModel = new \App\Models\SubCustomerModel();
        $orderModel = new \App\Models\OrderModel();

        $subCustomer = $subCustomerModel->find($subCustomerId);
        if (!$subCustomer) {
            return redirect()->to('/customers/sub-customers')->with('error', 'Mã phụ không tồn tại.');
        }

        $orderCount = $orderModel->where('sub_customer_id', $subCustomerId)->countAllResults();
        if ($orderCount > 0) {
            return redirect()->to('/customers/sub-customers')->with('error', 'Không thể xóa mã phụ này vì có ' . $orderCount . ' đơn hàng liên quan.');
        }

        if ($subCustomerModel->delete($subCustomerId)) {
            return redirect()->to('/customers/sub-customers')->with('success', 'Xóa mã phụ thành công.');
        } else {
            return redirect()->to('/customers/sub-customers')->with('error', 'Xóa mã phụ thất bại.');
        }
    }

    public function subCustomerDetail($subCustomerId)
    {
        $subCustomerModel = new \App\Models\SubCustomerModel();
        $customerModel = new \App\Models\CustomerModel();
        $orderModel = new \App\Models\OrderModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $productTypeModel = new \App\Models\ProductTypeModel();
        $db = \Config\Database::connect();

        $subCustomer = $subCustomerModel->find($subCustomerId);
        if (!$subCustomer) {
            return redirect()->to('/customers/sub-customers')->with('error', 'Mã phụ không tồn tại.');
        }

        $customer = $customerModel->find($subCustomer['customer_id']);
        if (!$customer) {
            return redirect()->to('/customers/sub-customers')->with('error', 'Khách hàng chính không tồn tại.');
        }

        // Lấy thông tin thống kê tổng quát
        $totalOrders = $orderModel->where('sub_customer_id', $subCustomerId)->countAllResults();

        // Lấy thống kê phiếu xuất liên quan 
        $invoiceIds = $orderModel
            ->distinct()
            ->select('invoice_id')
            ->where('sub_customer_id', $subCustomerId)
            ->where('invoice_id IS NOT NULL')
            ->findAll();

        $uniqueInvoiceIds = array_column($invoiceIds, 'invoice_id');
        $uniqueInvoiceIds = array_filter($uniqueInvoiceIds); // Loại bỏ giá trị NULL nếu có

        $totalInvoices = count($uniqueInvoiceIds);
        $paidInvoiceCount = 0;

        if (!empty($uniqueInvoiceIds)) {
            $paidInvoiceCount = $invoiceModel
                ->whereIn('id', $uniqueInvoiceIds)
                ->where('payment_status', 'paid')
                ->countAllResults();
        }

        // Lấy danh sách 10 đơn hàng gần nhất
        $recentOrders = $orderModel
            ->select('orders.*, product_types.name as product_type_name, invoices.id as invoice_id, invoices.created_at as invoice_date, invoices.shipping_confirmed_at')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices', 'invoices.id = orders.invoice_id', 'left')
            ->where('orders.sub_customer_id', $subCustomerId)
            ->orderBy('orders.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // Tính tổng tiền cho mỗi đơn hàng
        $totalOrderAmount = 0;
        foreach ($recentOrders as &$order) {
            $order['total_weight'] = $order['total_weight'] ?? 0;
            $order['price_per_kg'] = $order['price_per_kg'] ?? 0;
            $order['volume'] = $order['volume'] ?? 0;
            $order['price_per_cubic_meter'] = $order['price_per_cubic_meter'] ?? 0;
            $order['domestic_fee'] = $order['domestic_fee'] ?? 0;

            $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
            $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
            $finalPrice = max($priceByWeight, $priceByVolume);
            $exchangeRate = $order['exchange_rate'] ?: 1;
            $domesticFee = $order['domestic_fee'] * $exchangeRate;
            $order['total_amount'] = $finalPrice + $domesticFee;
            $totalOrderAmount += $order['total_amount'];
        }

        // Lấy 10 phiếu xuất gần nhất liên quan đến mã phụ này
        $recentInvoices = [];
        if (!empty($uniqueInvoiceIds)) {
            $recentInvoices = $invoiceModel
                ->select('invoices.*, users.fullname as created_by_name, COUNT(DISTINCT orders.id) as total_orders, SUM(orders.total_weight) as total_weight, SUM(orders.volume) as total_volume')
                ->join('users', 'users.id = invoices.created_by', 'left')
                ->join('orders', 'orders.invoice_id = invoices.id AND orders.sub_customer_id = ' . $subCustomerId, 'left')
                ->whereIn('invoices.id', $uniqueInvoiceIds)
                ->groupBy('invoices.id')
                ->orderBy('invoices.created_at', 'DESC')
                ->limit(10)
                ->findAll();

            // Tính tổng tiền cho mỗi phiếu xuất (chỉ tính những đơn hàng của mã phụ này)
            foreach ($recentInvoices as &$invoice) {
                $invoiceOrders = $orderModel
                    ->where('invoice_id', $invoice['id'])
                    ->where('sub_customer_id', $subCustomerId)
                    ->findAll();

                $total = 0;
                foreach ($invoiceOrders as $order) {
                    $totalWeight = floatval($order['total_weight'] ?? 0);
                    $pricePerKg = floatval($order['price_per_kg'] ?? 0);
                    $volume = floatval($order['volume'] ?? 0);
                    $pricePerCubicMeter = floatval($order['price_per_cubic_meter'] ?? 0);
                    $domesticFee = floatval($order['domestic_fee'] ?? 0);
                    $exchangeRate = floatval($order['exchange_rate'] ?? 1);

                    $priceByWeight = $totalWeight * $pricePerKg;
                    $priceByVolume = $volume * $pricePerCubicMeter;
                    $finalPrice = max($priceByWeight, $priceByVolume);
                    $total += $finalPrice + ($domesticFee * $exchangeRate);
                }

                $invoice['total_amount'] = $total + floatval($invoice['shipping_fee']) + floatval($invoice['other_fee']);
            }
        }

        return view('customers/sub_detail', [
            'subCustomer' => $subCustomer,
            'customer' => $customer,
            'totalOrders' => $totalOrders,
            'totalInvoices' => $totalInvoices,
            'paidInvoices' => $paidInvoiceCount,
            'recentOrders' => $recentOrders,
            'recentInvoices' => $recentInvoices,
            'totalOrderAmount' => $totalOrderAmount
        ]);
    }

    public function updateBalance($id)
    {
        $customerModel = new CustomerModel();
        $customer = $customerModel->find($id);

        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy khách hàng'
            ]);
        }

        // Xóa cache số dư
        $cacheKey = "customer_balance_{$id}";
        cache()->delete($cacheKey);

        // Tính toán lại số dư
        return redirect()->to('/customers/detail/' . $id)->with('success', 'Số dư đã được cập nhật mới nhất.');
    }
}
