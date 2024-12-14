<?php

namespace App\Controllers;

use App\Models\ProductTypeModel;

class ProductTypeController extends BaseController
{
    protected $productTypeModel;

    public function __construct()
    {
        $this->productTypeModel = new ProductTypeModel();
    }

    public function index()
    {
        $data['product_types'] = $this->productTypeModel->findAll();
        return view('product_types/index', $data);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getPost();
            if ($this->productTypeModel->insert($data)) {
                return redirect()->to('/product-types')->with('success', 'Thêm loại hàng thành công.');
            } else {
                return redirect()->back()->with('error', 'Thêm loại hàng thất bại.');
            }
        }
        return view('product_types/create');
    }

    public function edit($id)
    {
        $data['product_type'] = $this->productTypeModel->find($id);
        if ($this->request->getMethod() === 'POST') {
            if ($this->productTypeModel->update($id, $this->request->getPost())) {
                return redirect()->to('/product-types')->with('success', 'Cập nhật loại hàng thành công.');
            } else {
                return redirect()->back()->with('error', 'Cập nhật loại hàng thất bại.');
            }
        }
        return view('product_types/edit', $data);
    }

    public function delete($id)
    {
        $this->productTypeModel->delete($id);
        return redirect()->to('/product-types');
    }
}
