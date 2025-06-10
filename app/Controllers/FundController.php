<?php

namespace App\Controllers;

use App\Models\FundModel;
use App\Models\FinancialTransactionModel;
use App\Models\CustomerTransactionModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FundController extends Controller
{
    protected $fundModel;
    protected $transactionModel;
    protected $customerTransactionModel;

    public function __construct()
    {
        $this->fundModel = new FundModel();
        $this->transactionModel = new FinancialTransactionModel();
        $this->customerTransactionModel = new CustomerTransactionModel();
    }

    // Hiển thị danh sách quỹ
    public function index()
    {
        $funds = $this->fundModel->findAll();
        return view('funds/index', ['funds' => $funds]);
    }

    // Hiển thị form tạo quỹ mới
    public function create()
    {
        return view('funds/create');
    }

    // Lưu quỹ mới
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'account_number' => 'permit_empty|max_length[50]',
            'bank_name' => 'permit_empty|max_length[100]',
            'account_holder' => 'permit_empty|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'account_number' => $this->request->getPost('account_number'),
            'bank_name' => $this->request->getPost('bank_name'),
            'account_holder' => $this->request->getPost('account_holder')
        ];

        $this->fundModel->insert($data);
        return redirect()->to('/funds')->with('message', 'Quỹ đã được tạo thành công');
    }

    // Hiển thị form chỉnh sửa quỹ
    public function edit($id)
    {
        $fund = $this->fundModel->find($id);
        if (!$fund) {
            return redirect()->to('/funds')->with('error', 'Không tìm thấy quỹ');
        }
        return view('funds/edit', ['fund' => $fund]);
    }

    // Cập nhật thông tin quỹ
    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'account_number' => 'permit_empty|max_length[50]',
            'bank_name' => 'permit_empty|max_length[100]',
            'account_holder' => 'permit_empty|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'account_number' => $this->request->getPost('account_number'),
            'bank_name' => $this->request->getPost('bank_name'),
            'account_holder' => $this->request->getPost('account_holder')
        ];

        $this->fundModel->update($id, $data);
        return redirect()->to('/funds')->with('message', 'Quỹ đã được cập nhật thành công');
    }

    // Xóa quỹ
    public function delete($id)
    {
        // Kiểm tra xem quỹ có giao dịch nào không
        $transactions = $this->transactionModel->where('fund_id', $id)->countAllResults();
        if ($transactions > 0) {
            return redirect()->to('/funds')->with('error', 'Không thể xóa quỹ vì đã có giao dịch liên quan');
        }

        $this->fundModel->delete($id);
        return redirect()->to('/funds')->with('message', 'Quỹ đã được xóa thành công');
    }

    // Xem chi tiết quỹ và lịch sử giao dịch
    public function detail($id)
    {
        $fund = $this->fundModel->find($id);
        if (!$fund) {
            return redirect()->to('/funds')->with('error', 'Không tìm thấy quỹ');
        }

        $financialTransactions = $this->transactionModel
            ->where('fund_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll(20);


        $customerTransactions = $this->customerTransactionModel
            ->select('customer_transactions.*, customers.fullname as customer_name, users.fullname as created_by_name')
            ->join('customers', 'customers.id = customer_transactions.customer_id')
            ->join('users', 'users.id = customer_transactions.created_by')
            ->where('customer_transactions.fund_id', $id)
            ->where('customer_transactions.transaction_type', 'deposit')
            ->orderBy('customer_transactions.created_at', 'DESC')
            ->findAll(20);

        // Tính toán số dư
        $totalIncome = $this->fundModel->getTotalIncome($id);
        $totalExpense = $this->fundModel->getTotalExpense($id);
        $balance = $totalIncome - $totalExpense;



        return view('funds/detail', [
            'fund' => $fund,
            'financialTransactions' => $financialTransactions,
            'customerTransactions' => $customerTransactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance
        ]);
    }
}
