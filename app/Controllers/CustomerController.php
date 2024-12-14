<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $data['customers'] = $this->customerModel->orderBy('id', 'DESC')->findAll();
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

        return view('customers/create');
    }



    public function edit($id)
    {
        // Lấy thông tin khách hàng
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Khách hàng không tồn tại.");
        }

        // Nếu request là POST thì xử lý cập nhật
        if ($this->request->getMethod() === 'POST') {
            // Quy định các rule kiểm tra dữ liệu
            $rules = [
                'fullname' => 'required|min_length[3]|max_length[100]',
                'phone' => 'required|regex_match[/^[0-9]{10,15}$/]',
                'address' => 'required|min_length[5]|max_length[255]',
                'zalo_link' => 'permit_empty|valid_url',
                'email' => 'permit_empty|valid_email',
                'price_per_kg' => 'required|integer|greater_than_equal_to[0]', // Kiểm tra giá 1kg
                'price_per_cubic_meter' => 'required|integer|greater_than_equal_to[0]' // Kiểm tra giá 1 mét khối
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            // Lấy dữ liệu từ form và chỉ nhận các trường được phép
            $data = [
                'fullname' => $this->request->getPost('fullname'),
                'phone' => $this->request->getPost('phone'),
                'address' => $this->request->getPost('address'),
                'zalo_link' => $this->request->getPost('zalo_link'),
                'email' => $this->request->getPost('email'),
                'price_per_kg' => $this->request->getPost('price_per_kg'),
                'price_per_cubic_meter' => $this->request->getPost('price_per_cubic_meter'),
            ];

            // Cập nhật dữ liệu vào database
            if ($this->customerModel->update($id, $data)) {
                // Nếu cập nhật thành công, chuyển hướng về trang danh sách
                return redirect()->to('/customers')->with('success', 'Cập nhật khách hàng thành công.');
            } else {
                // Nếu cập nhật thất bại, thông báo lỗi
                return redirect()->back()->with('error', 'Cập nhật khách hàng thất bại.');
            }
        }

        // Nếu không phải POST, hiển thị form chỉnh sửa
        return view('customers/edit', ['customer' => $customer]);
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
}
