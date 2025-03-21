<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\CustomerTransactionModel;
use App\Models\InvoiceModel; // Thêm model cho invoices nếu cần
use App\Models\OrderModel;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $customerTransactionModel;
    protected $invoiceModel;
    protected $orderModel;
    protected $db;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->customerTransactionModel = new CustomerTransactionModel();
        $this->invoiceModel = new InvoiceModel(); // Khởi tạo nếu cần
        $this->orderModel = new OrderModel();     // Khởi tạo nếu cần
        $this->db = \Config\Database::connect();
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
                'price_per_cubic_meter' => 'required|integer|greater_than_equal_to[0]'
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
        $data = $this->customerModel->getCustomerDetail($id);
        if (!$data) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Khách hàng không tồn tại.");
        }

        return view('customers/detail', $data);
    }

    public function deposit($id)
    {
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'amount' => 'required|numeric|greater_than[0]',
                'notes' => 'permit_empty|max_length[255]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'customer_id' => $id,
                'transaction_type' => 'deposit',
                'amount' => $this->request->getPost('amount'),
                'created_by' => session()->get('user_id') ?? 1, // Điều chỉnh nếu cần, mặc định là 1 nếu không có session
                'notes' => $this->request->getPost('notes'),
            ];

            if ($this->customerTransactionModel->addTransaction($data)) {
                cache()->delete("customer_balance_{$id}");
                cache()->delete("invoice_stats_$id");
                cache()->delete("order_stats_$id");
                return redirect()->to("/customers/detail/{$id}")->with('success', 'Nạp tiền thành công.');
            } else {
                return redirect()->back()->with('error', 'Nạp tiền thất bại.');
            }
        }
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
}
