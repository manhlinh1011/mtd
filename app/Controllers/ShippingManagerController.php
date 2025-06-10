<?php

namespace App\Controllers;

use App\Models\ShippingManagerModel;
use App\Models\ShippingProviderModel;
use App\Models\InvoiceModel;

class ShippingManagerController extends BaseController
{
    protected $shippingManagerModel;
    protected $shippingProviderModel;
    protected $invoiceModel;

    public function __construct()
    {
        $this->shippingManagerModel = new ShippingManagerModel();
        $this->shippingProviderModel = new ShippingProviderModel();
        $this->invoiceModel = new InvoiceModel();
    }

    public function index()
    {
        $data['title'] = 'Quản lý giao hàng';
        $perPage = 30; // Số lượng bản ghi mỗi trang
        $data['shippings'] = $this->shippingManagerModel->orderBy('id', 'DESC')->paginate($perPage); // Lấy tất cả đơn hàng, sắp xếp theo ID giảm dần và phân trang
        $data['pager'] = $this->shippingManagerModel->pager; // Lấy đối tượng pager
        $data['providers'] = $this->shippingProviderModel->getAllProviders();

        return view('shipping_manager/index', $data);
    }

    public function delivered()
    {
        $data['title'] = 'Danh sách đã giao';
        $data['shippings'] = $this->shippingManagerModel->getDeliveredShippings();
        $data['providers'] = $this->shippingProviderModel->getAllProviders();

        return view('shipping_manager/delivered', $data);
    }

    public function search()
    {
        $keyword = $this->request->getGet('keyword');
        $data['title'] = 'Tìm kiếm giao hàng';

        // Thêm phân trang
        $data['shippings'] = $this->shippingManagerModel->searchShippings($keyword);
        $data['pager'] = $this->shippingManagerModel->pager;
        $data['providers'] = $this->shippingProviderModel->getAllProviders();
        $data['keyword'] = $keyword;

        return view('shipping_manager/index', $data);
    }

    public function confirm($id = null)
    {
        // Chỉ xử lý yêu cầu POST từ modal
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Phương thức không được hỗ trợ'
            ]);
        }

        // Đảm bảo header là JSON
        $this->response->setHeader('Content-Type', 'application/json');

        $shippingId = $this->request->getPost('shipping_id');

        // Kiểm tra shipping ID
        if (!$shippingId || !is_numeric($shippingId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID giao hàng không hợp lệ'
            ]);
        }

        // Kiểm tra xem giao hàng có tồn tại không
        $shipping = $this->shippingManagerModel->find($shippingId);
        if ($shipping === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy thông tin giao hàng'
            ]);
        }

        // Kiểm tra trạng thái
        if ($shipping['status'] === 'delivered') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Đơn hàng này đã được xác nhận giao'
            ]);
        }

        // Validate dữ liệu từ modal
        $rules = [
            'shipping_id' => 'required|numeric',
            'receiver_name' => 'required|min_length[3]|max_length[255]',
            'receiver_phone' => 'required|min_length[10]|max_length[20]',
            'receiver_address' => 'required|min_length[5]|max_length[500]',
            'shipping_provider_id' => 'required|numeric',
            'tracking_number' => 'permit_empty|max_length[100]',
            'shipping_fee' => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi validation: ' . implode(', ', $this->validator->getErrors())
            ]);
        }

        // Lấy dữ liệu đã validate
        $data = [
            'receiver_name' => $this->request->getPost('receiver_name'),
            'receiver_phone' => $this->request->getPost('receiver_phone'),
            'receiver_address' => $this->request->getPost('receiver_address'),
            'shipping_provider_id' => (int) $this->request->getPost('shipping_provider_id'),
            'tracking_number' => $this->request->getPost('tracking_number') ?: null,
            'shipping_fee' => (int) ($this->request->getPost('shipping_fee') ?: 0),
            'status' => 'delivered',
            'confirmed_by' => (int) session()->get('user_id'),
            'confirmed_at' => date('Y-m-d H:i:s')
        ];

        // Cập nhật vào database
        try {
            if ($this->shippingManagerModel->update($shippingId, $data)) {
                // Lấy thông tin invoice_id từ đơn hàng vừa cập nhật
                $shipping = $this->shippingManagerModel->find($shippingId);
                $invoiceId = $shipping['invoice_id'];

                $this->notifyShipmenttoCustomer($shippingId);

                // Cập nhật thông tin xác nhận giao hàng trong bảng invoices
                $invoiceModel = new \App\Models\InvoiceModel();
                $invoiceModel->update($invoiceId, [
                    'shipping_status' => 'confirmed',
                    'shipping_confirmed_at' => date('Y-m-d H:i:s'),
                    'shipping_confirmed_by' => (int) session()->get('user_id')
                ]);



                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Xác nhận giao hàng thành công'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể cập nhật thông tin giao hàng'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }

    public function create($invoiceId = null)
    {
        if ($invoiceId === null) {
            return redirect()->to('/shipping-manager')->with('error', 'Không tìm thấy thông tin phiếu xuất');
        }

        $db = \Config\Database::connect();
        $invoice = $db->table('invoices')
            ->select('invoices.*, 
                customers.fullname as customer_name, customers.phone as customer_phone, customers.address as customer_address,
                sub_customers.fullname as sub_customer_name, sub_customers.phone as sub_customer_phone, sub_customers.address as sub_customer_address')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->join('sub_customers as sub_customers', 'sub_customers.id = invoices.sub_customer_id', 'left')
            ->where('invoices.id', $invoiceId)
            ->get()
            ->getRowArray();

        if ($invoice === null) {
            return redirect()->to('/shipping-manager')->with('error', 'Không tìm thấy thông tin phiếu xuất');
        }

        // Kiểm tra xem phiếu xuất đã có giao hàng chưa
        $existingShipping = $this->shippingManagerModel->where('invoice_id', $invoiceId)->first();
        if ($existingShipping !== null) {
            return redirect()->to('/shipping-manager')->with('error', 'Phiếu xuất này đã được tạo giao hàng');
        }

        $data['title'] = 'Tạo giao hàng mới';
        $data['invoice'] = $invoice;
        $data['providers'] = $this->shippingProviderModel->getAllProviders();

        return view('shipping_manager/create', $data);
    }

    public function store()
    {
        // Validate dữ liệu
        $rules = [
            'invoice_id' => 'required|numeric',
            'customer_id' => 'required|numeric',
            'sub_customer_id' => 'permit_empty|numeric',
            'receiver_name' => 'required|min_length[3]|max_length[255]',
            'receiver_phone' => 'required|min_length[10]|max_length[20]',
            'receiver_address' => 'required|min_length[5]|max_length[500]',
            'shipping_provider_id' => 'permit_empty|numeric',
            'tracking_number' => 'permit_empty|max_length[100]',
            'shipping_fee' => 'permit_empty|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Lấy thông tin phiếu xuất để lấy sub_customer_id
        $db = \Config\Database::connect();
        $invoice = $db->table('invoices')
            ->select('sub_customer_id')
            ->where('id', $this->request->getPost('invoice_id'))
            ->get()
            ->getRowArray();

        // Lấy dữ liệu từ form
        $data = [
            'invoice_id' => $this->request->getPost('invoice_id'),
            'customer_id' => $this->request->getPost('customer_id'),
            'sub_customer_id' => $invoice['sub_customer_id'] ?? null,
            'receiver_name' => $this->request->getPost('receiver_name'),
            'receiver_phone' => $this->request->getPost('receiver_phone'),
            'receiver_address' => $this->request->getPost('receiver_address'),
            'shipping_provider_id' => $this->request->getPost('shipping_provider_id') ?: null,
            'tracking_number' => $this->request->getPost('tracking_number') ?: null,
            'shipping_fee' => $this->request->getPost('shipping_fee') ?: 0,
            'status' => 'pending',
            'created_by' => session()->get('user_id'),
            'notes' => $this->request->getPost('notes') ?: null
        ];



        //return;
        // Lưu vào database
        if ($this->shippingManagerModel->insert($data)) {
            $this->notifyRequestShipment($this->shippingManagerModel->getInsertID());
            return redirect()->to('/shipping-manager')->with('success', 'Tạo giao hàng thành công');
        }

        return redirect()->back()->withInput()->with('error', 'Không thể tạo giao hàng');
    }

    private function notifyRequestShipment($shippingId)
    {
        $shipping = $this->shippingManagerModel->getShippingDetails($shippingId);
        if ($shipping['sub_customer_id'] != null) {
            $customer_code = $shipping['sub_customer_code'];
        } else {
            $customer_code = $shipping['customer_code'];
        }

        // Gửi thông báo qua webhook
        $client = \Config\Services::curlrequest();
        try {
            $postData = [
                'customer_code' => $customer_code,
                'receiver_name' => $shipping['receiver_name'],
                'receiver_phone' => $shipping['receiver_phone'],
                'receiver_address' => $shipping['receiver_address'],
                'invoice_id' => $shipping['invoice_id'],
                'notes' => $shipping['notes']
            ];

            $response = $client->request('POST', 'https://mqzcil.datadex.vn/webhook/yeucauship', [
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
    }

    private function notifyShipmenttoCustomer($shippingId)
    {
        $shipping = $this->shippingManagerModel->getShippingDetails($shippingId);
        if ($shipping['sub_customer_id']) {
            // Có mã phụ
            $customer_code = $shipping['sub_customer_code']; // Mã khách hàng phụ
            if (!empty($shipping['sub_customer_thread_id_zalo'])) {
                // Lấy từ khách hàng phụ nếu có
                $thread_id_zalo = $shipping['sub_customer_thread_id_zalo'];
                $msg_zalo_type = $shipping['sub_customer_msg_zalo_type'];
            } else {
                // Nếu khách hàng phụ không có, lấy từ khách hàng chính
                $thread_id_zalo = $shipping['customer_thread_id_zalo'];
                $msg_zalo_type = $shipping['customer_msg_zalo_type'];
            }
        } else {
            // Không có mã phụ, lấy từ khách hàng chính
            $customer_code = $shipping['customer_code'];
            $thread_id_zalo = $shipping['customer_thread_id_zalo'];
            $msg_zalo_type = $shipping['customer_msg_zalo_type'];
        }
        $shippingInfo = "";
        if ($shipping['tracking_number']) {
            $shippingInfo = $shipping['shipping_provider_name'] . " - " . $shipping['tracking_number'];
        } else {
            $shippingInfo = $shipping['shipping_provider_name'];
        }

        if ($shipping['shipping_fee'] != 0) {
            $shippingInfo .= "\nPhí giao hàng: " . $shipping['shipping_fee'] . " đ";
        }

        $client = \Config\Services::curlrequest();
        try {
            $postData = [
                'customer_code' => $customer_code,
                'receiver_name' => $shipping['receiver_name'],
                'receiver_phone' => $shipping['receiver_phone'],
                'receiver_address' => $shipping['receiver_address'],
                'invoice_id' => $shipping['invoice_id'],
                'shipping_info' => $shippingInfo,
                'notes' => $shipping['notes'] ?? '',
                'thread_id_zalo' => $thread_id_zalo,
                'msg_zalo_type' => $msg_zalo_type,
                'confirmed_by' => $shipping['confirmed_by_name'] ?? '',
                'shipping_fee' => $shipping['shipping_fee'] ?? 0
            ];

            // Kiểm tra nếu có thread_id_zalo mới gửi thông báo
            if (!empty($thread_id_zalo)) {
                $response = $client->request('POST', 'https://mqzcil.datadex.vn/webhook/thongbaoship', [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'form_params' => $postData
                ]);

                log_message('debug', 'Notification response: ' . $response->getBody());
            } else {
                log_message('info', 'Không có thread_id_zalo để gửi thông báo ship cho đơn hàng #' . $shippingId);
            }
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Lỗi gửi thông báo đơn hàng về kho VN: ' . $e->getMessage());
            return false;
        }
    }
}
