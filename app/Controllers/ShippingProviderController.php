<?php

namespace App\Controllers;

use App\Models\ShippingProviderModel;

class ShippingProviderController extends BaseController
{
    protected $shippingProviderModel;

    public function __construct()
    {
        $this->shippingProviderModel = new ShippingProviderModel();
    }

    public function index()
    {
        $data['title'] = 'Quản lý đơn vị vận chuyển';
        $data['providers'] = $this->shippingProviderModel->getAllProviders();

        // Lấy số phiếu giao hàng cho từng provider
        $db = \Config\Database::connect();
        foreach ($data['providers'] as &$provider) {
            $provider['shipping_count'] = $db->table('shipping_managers')
                ->where('shipping_provider_id', $provider['id'])
                ->countAllResults();
        }

        return view('shipping_provider/index', $data);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name' => 'required|min_length[3]|max_length[100]',
                'description' => 'permit_empty|max_length[1000]'
            ];

            if ($this->validate($rules)) {
                $data = [
                    'name' => $this->request->getPost('name'),
                    'description' => $this->request->getPost('description')
                ];

                if ($this->shippingProviderModel->insert($data)) {
                    return redirect()->to('/shipping-provider')->with('success', 'Thêm đơn vị vận chuyển thành công');
                }
            }

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data['title'] = 'Thêm đơn vị vận chuyển mới';
        return view('shipping_provider/create', $data);
    }

    public function store()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        $this->shippingProviderModel->insert($data);
        return redirect()->to('/shipping-provider')->with('success', 'Thêm đơn vị vận chuyển thành công');
    }

    public function edit($id = null)
    {
        if ($id === null) {
            return redirect()->to('/shipping-provider');
        }

        $provider = $this->shippingProviderModel->find($id);
        if ($provider === null) {
            return redirect()->to('/shipping-provider')->with('error', 'Không tìm thấy đơn vị vận chuyển');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name' => 'required|min_length[3]|max_length[100]',
                'description' => 'permit_empty|max_length[1000]'
            ];

            if ($this->validate($rules)) {
                $data = [
                    'name' => $this->request->getPost('name'),
                    'description' => $this->request->getPost('description')
                ];

                if ($this->shippingProviderModel->update($id, $data)) {
                    return redirect()->to('/shipping-provider')->with('success', 'Cập nhật đơn vị vận chuyển thành công');
                }
            }

            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data['title'] = 'Chỉnh sửa đơn vị vận chuyển';
        $data['provider'] = $provider;
        return view('shipping_provider/edit', $data);
    }

    public function update($id = null)
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        $this->shippingProviderModel->update($id, $data);
        return redirect()->to('/shipping-provider')->with('success', 'Cập nhật đơn vị vận chuyển thành công');
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('/shipping-provider');
        }

        if ($this->shippingProviderModel->delete($id)) {
            return redirect()->to('/shipping-provider')->with('success', 'Xóa đơn vị vận chuyển thành công');
        }

        return redirect()->to('/shipping-provider')->with('error', 'Không thể xóa đơn vị vận chuyển');
    }

    public function search()
    {
        $keyword = $this->request->getGet('keyword');
        $data['title'] = 'Tìm kiếm đơn vị vận chuyển';
        $data['providers'] = $this->shippingProviderModel->searchProviders($keyword);
        $data['keyword'] = $keyword;

        return view('shipping_provider/index', $data);
    }
}
