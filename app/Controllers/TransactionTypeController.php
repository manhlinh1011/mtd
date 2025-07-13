<?php

namespace App\Controllers;

use App\Models\TransactionTypeModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TransactionTypeController extends BaseController
{
    protected $transactionTypeModel;

    public function __construct()
    {
        $this->transactionTypeModel = new TransactionTypeModel();
    }

    /**
     * Hiển thị danh sách loại giao dịch
     */
    public function index()
    {
        $data = [
            'title' => 'Quản lý loại giao dịch',
            'income_types' => $this->transactionTypeModel->getIncomeTypes(false),
            'expense_types' => $this->transactionTypeModel->getExpenseTypes(false),
            'transaction_counts' => $this->transactionTypeModel->getTransactionCounts()
        ];

        return view('transaction_types/index', $data);
    }

    /**
     * Hiển thị form tạo loại giao dịch mới
     */
    public function create()
    {
        $data = [
            'title' => 'Thêm loại giao dịch mới',
            'categories' => [
                'income' => 'Thu',
                'expense' => 'Chi'
            ]
        ];

        return view('transaction_types/create', $data);
    }

    /**
     * Lưu loại giao dịch mới
     */
    public function store()
    {
        $rules = [
            'name' => 'required|max_length[100]|is_unique[transaction_types.name]',
            'category' => 'required|in_list[income,expense]',
            'description' => 'permit_empty|max_length[500]',
            'sort_order' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_active' => 1
        ];

        if ($this->transactionTypeModel->createType($data)) {
            return redirect()->to('/transaction-types')->with('success', 'Thêm loại giao dịch thành công');
        } else {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi thêm loại giao dịch');
        }
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id = null)
    {
        $transactionType = $this->transactionTypeModel->find($id);

        if (!$transactionType) {
            return redirect()->to('/transaction-types')->with('error', 'Không tìm thấy loại giao dịch');
        }

        $data = [
            'title' => 'Chỉnh sửa loại giao dịch',
            'transaction_type' => $transactionType,
            'categories' => [
                'income' => 'Thu',
                'expense' => 'Chi'
            ]
        ];

        return view('transaction_types/edit', $data);
    }

    /**
     * Cập nhật loại giao dịch
     */
    public function update($id = null)
    {
        $transactionType = $this->transactionTypeModel->find($id);

        if (!$transactionType) {
            return redirect()->to('/transaction-types')->with('error', 'Không tìm thấy loại giao dịch');
        }

        $rules = [
            'name' => "required|max_length[100]|is_unique[transaction_types.name,id,$id]",
            'category' => 'required|in_list[income,expense]',
            'description' => 'permit_empty|max_length[500]',
            'sort_order' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0
        ];

        if ($this->transactionTypeModel->update($id, $data)) {
            return redirect()->to('/transaction-types')->with('success', 'Cập nhật loại giao dịch thành công');
        } else {
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật');
        }
    }

    /**
     * Xóa loại giao dịch
     */
    public function delete($id = null)
    {
        $transactionType = $this->transactionTypeModel->find($id);

        if (!$transactionType) {
            return redirect()->to('/transaction-types')->with('error', 'Không tìm thấy loại giao dịch');
        }

        // Kiểm tra xem có giao dịch nào đang sử dụng loại này không
        $counts = $this->transactionTypeModel->getTransactionCounts();
        if (isset($counts[$id]) && $counts[$id]['total_count'] > 0) {
            return redirect()->to('/transaction-types')->with('error', 'Không thể xóa loại giao dịch đang được sử dụng');
        }

        if ($this->transactionTypeModel->delete($id)) {
            return redirect()->to('/transaction-types')->with('success', 'Xóa loại giao dịch thành công');
        } else {
            return redirect()->to('/transaction-types')->with('error', 'Có lỗi xảy ra khi xóa');
        }
    }

    /**
     * Toggle trạng thái hoạt động
     */
    public function toggleActive($id = null)
    {
        if ($this->transactionTypeModel->toggleActive($id)) {
            return redirect()->to('/transaction-types')->with('success', 'Cập nhật trạng thái thành công');
        } else {
            return redirect()->to('/transaction-types')->with('error', 'Có lỗi xảy ra');
        }
    }

    /**
     * API: Lấy danh sách loại giao dịch theo category
     */
    public function getByCategory()
    {
        $category = $this->request->getGet('category');
        $activeOnly = $this->request->getGet('active_only') !== 'false';

        if (!in_array($category, ['income', 'expense'])) {
            return $this->response->setJSON(['error' => 'Category không hợp lệ'])->setStatusCode(400);
        }

        $types = $this->transactionTypeModel->getByCategory($category, $activeOnly);
        return $this->response->setJSON(['data' => $types]);
    }

    /**
     * API: Lấy tất cả loại giao dịch đang hoạt động
     */
    public function getActive()
    {
        $types = $this->transactionTypeModel->getActiveTypes();
        return $this->response->setJSON(['data' => $types]);
    }

    /**
     * Hiển thị trang thống kê
     */
    public function statistics()
    {
        $data = [
            'title' => 'Thống kê theo loại giao dịch'
        ];
        return view('transaction_types/statistics', $data);
    }

    /**
     * API: Lấy dữ liệu thống kê
     */
    public function getStatistics()
    {
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $category = $this->request->getGet('category');

        if (!$dateFrom || !$dateTo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng chọn khoảng thời gian'
            ])->setStatusCode(400);
        }

        try {
            $statistics = $this->transactionTypeModel->getStatistics($dateFrom, $dateTo, $category);
            return $this->response->setJSON([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
