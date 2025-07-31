<?php

namespace App\Controllers;

use App\Models\TransactionActionConfigModel;
use App\Models\TransactionTypeModel;

class TransactionActionConfigController extends BaseController
{
    protected $configModel;
    protected $typeModel;

    public function __construct()
    {
        $this->configModel = new TransactionActionConfigModel();
        $this->typeModel = new TransactionTypeModel();
    }

    /**
     * Hiển thị danh sách mapping action_code
     */
    public function index()
    {
        $data = [
            'title' => 'Quản lý mapping action → loại giao dịch',
            'configs' => $this->configModel->orderBy('id', 'ASC')->findAll()
        ];
        return view('transaction_action_config/index', $data);
    }

    /**
     * Hiển thị form tạo mới
     */
    public function create()
    {
        $data = [
            'title' => 'Thêm mapping action mới',
            'types' => $this->typeModel->getActiveTypes()
        ];
        return view('transaction_action_config/create', $data);
    }

    /**
     * Lưu mapping mới
     */
    public function store()
    {
        $rules = [
            'action_code' => 'required|max_length[50]|is_unique[transaction_action_config.action_code]',
            'transaction_type_id' => 'required|integer',
            'description' => 'permit_empty|max_length[255]'
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = [
            'action_code' => $this->request->getPost('action_code'),
            'transaction_type_id' => $this->request->getPost('transaction_type_id'),
            'description' => $this->request->getPost('description')
        ];
        if ($this->configModel->insert($data)) {
            return redirect()->to('/transaction-action-config')->with('success', 'Thêm mapping thành công');
        } else {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi thêm mapping');
        }
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id = null)
    {
        $config = $this->configModel->find($id);
        if (!$config) {
            return redirect()->to('/transaction-action-config')->with('error', 'Không tìm thấy mapping');
        }
        $data = [
            'title' => 'Chỉnh sửa mapping',
            'config' => $config,
            'types' => $this->typeModel->getActiveTypes()
        ];
        return view('transaction_action_config/edit', $data);
    }

    /**
     * Cập nhật mapping
     */
    public function update($id = null)
    {
        $config = $this->configModel->find($id);
        if (!$config) {
            return redirect()->to('/transaction-action-config')->with('error', 'Không tìm thấy mapping');
        }
        $rules = [
            'action_code' => "required|max_length[50]|is_unique[transaction_action_config.action_code,id,$id]",
            'transaction_type_id' => 'required|integer',
            'description' => 'permit_empty|max_length[255]'
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = [
            'action_code' => $this->request->getPost('action_code'),
            'transaction_type_id' => $this->request->getPost('transaction_type_id'),
            'description' => $this->request->getPost('description')
        ];
        if ($this->configModel->update($id, $data)) {
            return redirect()->to('/transaction-action-config')->with('success', 'Cập nhật mapping thành công');
        } else {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật');
        }
    }

    /**
     * Xóa mapping
     */
    public function delete($id = null)
    {
        $config = $this->configModel->find($id);
        if (!$config) {
            return redirect()->to('/transaction-action-config')->with('error', 'Không tìm thấy mapping');
        }
        if ($this->configModel->delete($id)) {
            return redirect()->to('/transaction-action-config')->with('success', 'Xóa mapping thành công');
        } else {
            return redirect()->to('/transaction-action-config')->with('error', 'Có lỗi xảy ra khi xóa');
        }
    }
}
