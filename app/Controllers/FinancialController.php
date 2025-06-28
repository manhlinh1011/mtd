<?php

namespace App\Controllers;

use App\Models\FinancialTransactionModel;
use App\Models\CustomerTransactionModel;
use App\Models\SystemLogModel;
use App\Models\FundModel;

class FinancialController extends BaseController
{
    public function index()
    {
        $model = new FinancialTransactionModel();
        $perPage = 20;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $query = $model->select('financial_transactions.*, 
                               u1.fullname as creator_name, 
                               u2.fullname as approver_name,
                               funds.name as fund_name')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->orderBy('financial_transactions.created_at', 'DESC');

        // Xử lý bộ lọc
        if ($type = $this->request->getGet('type')) {
            $query->where('type', $type);
        }
        if ($status = $this->request->getGet('status')) {
            $query->where('status', $status);
        }
        if ($dateFrom = $this->request->getGet('date_from')) {
            $query->where('created_at >=', $dateFrom);
        }
        if ($dateTo = $this->request->getGet('date_to')) {
            $query->where('created_at <=', $dateTo . ' 23:59:59');
        }

        $transactions = $query->paginate($perPage, 'default', $page);
        $pager = $model->pager;

        $customerTransactionModel = new CustomerTransactionModel();
        $totalCustomerDeposit = $customerTransactionModel->where('transaction_type', 'deposit')->selectSum('amount')->first()['amount'] ?? 0;

        $totalIncome = $model->where('type', 'income')->selectSum('amount')->first()['amount'] ?? 0;
        $totalExpense = $model->where('type', 'expense')->where('status', 'approved')->selectSum('amount')->first()['amount'] ?? 0;
        $balance = $totalCustomerDeposit + $totalIncome - $totalExpense;

        return view('financial/index', [
            'transactions' => $transactions,
            'typeFilter' => $type,
            'statusFilter' => $status,
            'dateFromFilter' => $dateFrom,
            'dateToFilter' => $dateTo,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'totalCustomerDeposit' => $totalCustomerDeposit,
            'pager' => $pager,
        ]);
    }

    public function dashboard()
    {
        $model = new FinancialTransactionModel();

        $totalIncome = $model->where('type', 'income')->selectSum('amount')->first()['amount'] ?? 0;
        $totalExpense = $model->where('type', 'expense')->where('status', 'approved')->selectSum('amount')->first()['amount'] ?? 0;
        $balance = $totalIncome - $totalExpense;

        $data = [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance
        ];

        return view('financial/dashboard', $data);
    }

    public function income()
    {
        $model = new FinancialTransactionModel();
        $query = $model->select('financial_transactions.*, 
                               u1.fullname as creator_name, 
                               u2.fullname as approver_name,
                               funds.name as fund_name')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->where('type', 'income')
            ->orderBy('financial_transactions.created_at', 'DESC');

        $transactions = $query->findAll();
        return view('financial/income', ['transactions' => $transactions]);
    }

    public function expense()
    {
        $model = new FinancialTransactionModel();
        $query = $model->select('financial_transactions.*, 
                               u1.fullname as creator_name, 
                               u2.fullname as approver_name,
                               funds.name as fund_name')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->where('type', 'expense')
            ->orderBy('financial_transactions.created_at', 'DESC');

        $transactions = $query->findAll();
        return view('financial/expense', ['transactions' => $transactions]);
    }

    public function create()
    {
        $fundModel = new FundModel();
        $funds = $fundModel->findAll();
        return view('financial/create', ['funds' => $funds]);
    }

    public function store()
    {
        $session = session();
        $model = new FinancialTransactionModel();
        $systemLogModel = new SystemLogModel();

        $type = $this->request->getPost('type');
        $amount = $this->request->getPost('amount');
        $description = $this->request->getPost('description');
        $fundId = $this->request->getPost('fund_id');
        $userId = $session->get('user_id');
        $role = $session->get('role');
        $transactionDate = $this->request->getPost('transaction_date');
        if (empty($transactionDate)) {
            $transactionDate = date('Y-m-d');
        }

        // Kiểm tra dữ liệu
        if (empty($type) || empty($amount) || empty($description)) {
            return redirect()->back()->withInput()->with('error', 'Vui lòng điền đầy đủ thông tin.');
        }

        $data = [
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'created_by' => $userId,
            'fund_id' => $fundId,
            'transaction_date' => $transactionDate,
        ];

        // Logic xử lý trạng thái
        if ($type === 'income') {
            $data['status'] = 'approved'; // Phiếu thu không cần duyệt
        } elseif ($type === 'expense') {
            if ($role === 'Quản lý') {
                $data['status'] = 'approved';
                $data['approved_by'] = $userId;
                $data['approved_at'] = date('Y-m-d H:i:s');
            } else {
                $data['status'] = 'pending'; // Chờ duyệt
            }
        }

        $model->save($data);
        $transactionId = $model->getInsertID();

        return redirect()->to('/financial')->with('success', 'Tạo phiếu thành công.');
    }

    public function approve($id)
    {
        $session = session();
        $model = new FinancialTransactionModel();
        $systemLogModel = new SystemLogModel();

        if ($session->get('role') !== 'Quản lý') {
            return redirect()->to('/financial')->with('error', 'Bạn không có quyền duyệt phiếu.');
        }

        $transaction = $model->find($id);
        if (!$transaction || $transaction['type'] !== 'expense' || $transaction['status'] !== 'pending') {
            return redirect()->to('/financial')->with('error', 'Phiếu không hợp lệ hoặc đã được duyệt.');
        }

        $data = [
            'status' => 'approved',
            'approved_by' => $session->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s')
        ];

        $model->update($id, $data);

        // Ghi log
        $systemLogModel->addLog([
            'entity_type' => 'financial_transaction',
            'entity_id' => $id,
            'action_type' => 'approve',
            'created_by' => $session->get('user_id'),
            'details' => json_encode($data),
            'notes' => "Duyệt phiếu chi #{$id}"
        ]);

        return redirect()->to('/financial')->with('success', 'Duyệt phiếu thành công.');
    }

    public function reject($id)
    {
        $session = session();
        $model = new FinancialTransactionModel();
        $systemLogModel = new SystemLogModel();

        if ($session->get('role') !== 'Quản lý') {
            return redirect()->to('/financial')->with('error', 'Bạn không có quyền từ chối phiếu.');
        }

        $transaction = $model->find($id);
        if (!$transaction || $transaction['type'] !== 'expense' || $transaction['status'] !== 'pending') {
            return redirect()->to('/financial')->with('error', 'Phiếu không hợp lệ hoặc đã được xử lý.');
        }

        $data = [
            'status' => 'rejected',
            'approved_by' => $session->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s')
        ];

        $model->update($id, $data);

        // Ghi log
        $systemLogModel->addLog([
            'entity_type' => 'financial_transaction',
            'entity_id' => $id,
            'action_type' => 'reject',
            'created_by' => $session->get('user_id'),
            'details' => json_encode($data),
            'notes' => "Từ chối phiếu chi #{$id}"
        ]);

        return redirect()->to('/financial')->with('success', 'Đã từ chối phiếu chi thành công.');
    }

    public function updateTransactionDate($id)
    {
        $model = new FinancialTransactionModel();
        $transaction = $model->find($id);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Không tìm thấy phiếu thu/chi.');
        }
        $transactionDate = $this->request->getPost('transaction_date');
        if (empty($transactionDate)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ngày giao dịch.');
        }
        $model->update($id, ['transaction_date' => $transactionDate]);
        return redirect()->back()->with('success', 'Cập nhật ngày giao dịch thành công.');
    }
}
