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
                               funds.name as fund_name,
                               tt.name as transaction_type_name')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->join('transaction_types tt', 'tt.id = financial_transactions.transaction_type_id', 'left')
            ->orderBy('financial_transactions.created_at', 'DESC');

        // Xử lý bộ lọc
        $fundId = $this->request->getGet('fund_id');
        if ($fundId) {
            $query->where('financial_transactions.fund_id', $fundId);
        }
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

        $fundModel = new FundModel();
        $funds = $fundModel->findAll();

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
            'funds' => $funds,
            'fundFilter' => $fundId,
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
                               funds.name as fund_name,
                               tt.name as transaction_type_name')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->join('transaction_types tt', 'tt.id = financial_transactions.transaction_type_id', 'left')
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
                               funds.name as fund_name,
                               tt.name as transaction_type_name')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->join('transaction_types tt', 'tt.id = financial_transactions.transaction_type_id', 'left')
            ->where('type', 'expense')
            ->orderBy('financial_transactions.created_at', 'DESC');

        $transactions = $query->findAll();
        return view('financial/expense', ['transactions' => $transactions]);
    }

    public function create()
    {
        $fundModel = new FundModel();
        $funds = $fundModel->findAll();

        // Load transaction types
        $transactionTypeModel = new \App\Models\TransactionTypeModel();
        $incomeTypes = $transactionTypeModel->getIncomeTypes();
        $expenseTypes = $transactionTypeModel->getExpenseTypes();

        return view('financial/create', [
            'funds' => $funds,
            'income_types' => $incomeTypes,
            'expense_types' => $expenseTypes
        ]);
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
        $transactionTypeId = $this->request->getPost('transaction_type_id');
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
            'transaction_type_id' => $transactionTypeId,
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
        $isCustomerDeposit = $this->request->getPost('is_customer_deposit');
        $transactionDate = $this->request->getPost('transaction_date');
        if (empty($transactionDate)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ngày giao dịch.');
        }

        if ($isCustomerDeposit) {
            $model = new \App\Models\CustomerTransactionModel();
            $transaction = $model->find($id);
            if (!$transaction) {
                return redirect()->back()->with('error', 'Không tìm thấy giao dịch nạp tiền khách.');
            }
            $model->update($id, ['transaction_date' => $transactionDate]);
        } else {
            $model = new \App\Models\FinancialTransactionModel();
            $transaction = $model->find($id);
            if (!$transaction) {
                return redirect()->back()->with('error', 'Không tìm thấy phiếu thu/chi.');
            }
            $model->update($id, ['transaction_date' => $transactionDate]);
        }
        return redirect()->back()->with('success', 'Cập nhật ngày giao dịch thành công.');
    }

    public function updateTransactionType()
    {
        $id = $this->request->getPost('id');
        $table = $this->request->getPost('table'); // 'financial' hoặc 'customer'
        $transactionTypeId = $this->request->getPost('transaction_type_id');

        if (!$id || !$transactionTypeId || !in_array($table, ['financial', 'customer'])) {
            session()->setFlashdata('error', 'Dữ liệu không hợp lệ');
            return redirect()->back();
        }

        if ($table === 'financial') {
            $model = new FinancialTransactionModel();
        } else {
            $model = new CustomerTransactionModel();
        }

        $updated = $model->update($id, ['transaction_type_id' => $transactionTypeId]);

        if ($updated) {
            session()->setFlashdata('success', 'Cập nhật loại giao dịch thành công!');
        } else {
            session()->setFlashdata('error', 'Cập nhật thất bại');
        }

        return redirect()->back();
    }

    public function fundTransactions()
    {
        $perPage = 20;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $fundId = $this->request->getGet('fund_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $status = $this->request->getGet('status');
        $transactionTypeId = $this->request->getGet('transaction_type_id');

        // Lấy danh sách quỹ
        $fundModel = new \App\Models\FundModel();
        $funds = $fundModel->findAll();

        // Lấy danh sách loại giao dịch
        $transactionTypeModel = new \App\Models\TransactionTypeModel();
        $transactionTypes = $transactionTypeModel->getActiveTypes();

        // Lấy dữ liệu nạp tiền và rút tiền khách hàng
        $customerModel1 = new \App\Models\CustomerTransactionModel();
        $customerModel2 = new \App\Models\CustomerTransactionModel();
        // Deposit
        $depositQuery = $customerModel1->select('customer_transactions.id, customer_transactions.created_at, customer_transactions.transaction_date, "income" as type, customer_transactions.amount, customer_transactions.fund_id, customer_transactions.notes as description, "approved" as status, customer_transactions.created_by, NULL as approved_by, funds.name as fund_name, u1.fullname as creator_name, NULL as approver_name, 1 as is_customer_deposit, customers.customer_code, tt.name as transaction_type_name')
            ->join('funds', 'funds.id = customer_transactions.fund_id', 'left')
            ->join('users u1', 'u1.id = customer_transactions.created_by', 'left')
            ->join('customers', 'customers.id = customer_transactions.customer_id', 'left')
            ->join('transaction_types tt', 'tt.id = customer_transactions.transaction_type_id', 'left')
            ->where('customer_transactions.transaction_type', 'deposit');
        // Withdraw
        $withdrawQuery = $customerModel2->select('customer_transactions.id, customer_transactions.created_at, customer_transactions.transaction_date, "withdraw" as type, customer_transactions.amount, customer_transactions.fund_id, customer_transactions.notes as description, "approved" as status, customer_transactions.created_by, NULL as approved_by, funds.name as fund_name, u1.fullname as creator_name, NULL as approver_name, 1 as is_customer_deposit, customers.customer_code, tt.name as transaction_type_name')
            ->join('funds', 'funds.id = customer_transactions.fund_id', 'left')
            ->join('users u1', 'u1.id = customer_transactions.created_by', 'left')
            ->join('customers', 'customers.id = customer_transactions.customer_id', 'left')
            ->join('transaction_types tt', 'tt.id = customer_transactions.transaction_type_id', 'left')
            ->where('customer_transactions.transaction_type', 'withdraw');
        // Filter
        if ($fundId) {
            $depositQuery->where('customer_transactions.fund_id', $fundId);
            $withdrawQuery->where('customer_transactions.fund_id', $fundId);
        }
        if ($dateFrom) {
            $depositQuery->where('customer_transactions.transaction_date >=', $dateFrom);
            $withdrawQuery->where('customer_transactions.transaction_date >=', $dateFrom);
        }
        if ($dateTo) {
            $depositQuery->where('customer_transactions.transaction_date <=', $dateTo);
            $withdrawQuery->where('customer_transactions.transaction_date <=', $dateTo);
        }
        if ($transactionTypeId) {
            $depositQuery->where('customer_transactions.transaction_type_id', $transactionTypeId);
            $withdrawQuery->where('customer_transactions.transaction_type_id', $transactionTypeId);
        }
        if ($status && $status !== 'approved') {
            $customerTransactions = [];
        } else {
            $depositTransactions = $depositQuery->findAll();
            $withdrawTransactions = $withdrawQuery->findAll();
            // Gắn cờ và mô tả cho withdraw
            foreach ($withdrawTransactions as &$w) {
                $w['is_customer_withdraw'] = true;
                $w['type'] = 'withdraw';
                if (!empty($w['customer_code'])) {
                    $w['description'] = '<span class="badge bg-warning">' . esc($w['customer_code']) . '</span> ' . $w['description'];
                }
            }
            unset($w);
            $customerTransactions = array_merge($depositTransactions, $withdrawTransactions);
        }

        // Lấy dữ liệu thu chi
        $financialModel = new \App\Models\FinancialTransactionModel();
        $financialQuery = $financialModel->select('financial_transactions.id, financial_transactions.created_at, financial_transactions.transaction_date, financial_transactions.type, financial_transactions.amount, financial_transactions.fund_id, financial_transactions.description, financial_transactions.status, financial_transactions.created_by, financial_transactions.approved_by, funds.name as fund_name, u1.fullname as creator_name, u2.fullname as approver_name, 0 as is_customer_deposit, tt.name as transaction_type_name')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('transaction_types tt', 'tt.id = financial_transactions.transaction_type_id', 'left');
        if ($fundId) {
            $financialQuery->where('financial_transactions.fund_id', $fundId);
        }
        if ($dateFrom) {
            $financialQuery->where('financial_transactions.transaction_date >=', $dateFrom);
        }
        if ($dateTo) {
            $financialQuery->where('financial_transactions.transaction_date <=', $dateTo);
        }
        if ($transactionTypeId) {
            $financialQuery->where('financial_transactions.transaction_type_id', $transactionTypeId);
        }
        if ($status) {
            $financialQuery->where('financial_transactions.status', $status);
        }
        $financialTransactions = $financialQuery->findAll();

        // Gộp dữ liệu và sắp xếp
        $allTransactions = array_merge($customerTransactions, $financialTransactions);
        usort($allTransactions, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Tính tổng số liệu trên toàn bộ dữ liệu đã lọc
        $totalTransactions = count($allTransactions);
        $totalIncome = 0;
        $totalExpense = 0;
        $totalWithdraw = 0;
        $fundBalances = [];
        foreach ($funds as $fund) {
            $fundBalances[$fund['id']] = [
                'name' => $fund['name'],
                'income' => 0,
                'expense' => 0,
                'deposit' => 0,
                'withdraw' => 0,
                'balance' => 0,
            ];
        }
        foreach ($allTransactions as $t) {
            if ($t['type'] === 'income') {
                $totalIncome += $t['amount'];
                if (isset($fundBalances[$t['fund_id']])) {
                    $fundBalances[$t['fund_id']]['income'] += $t['amount'];
                }
            } elseif ($t['type'] === 'expense') {
                $totalExpense += $t['amount'];
                if (isset($fundBalances[$t['fund_id']])) {
                    $fundBalances[$t['fund_id']]['expense'] += $t['amount'];
                }
            }
            // Nếu là nạp tiền khách hàng (income từ customer_transactions, không có approver_name)
            if ($t['type'] === 'income' && $t['approver_name'] === null) {
                if (isset($fundBalances[$t['fund_id']])) {
                    $fundBalances[$t['fund_id']]['deposit'] += $t['amount'];
                }
            }
            // Nếu là rút tiền khách hàng (withdraw)
            if ($t['type'] === 'withdraw' && !empty($t['is_customer_withdraw'])) {
                $totalWithdraw += abs($t['amount']);
                if (isset($fundBalances[$t['fund_id']])) {
                    $fundBalances[$t['fund_id']]['withdraw'] += abs($t['amount']);
                }
            }
        }
        foreach ($fundBalances as $id => &$fb) {
            $fb['balance'] = $fb['income'] + $fb['deposit'] - $fb['expense'];
        }
        unset($fb);
        $totalExpense = $totalExpense + $totalWithdraw;
        $soDuQuy = $totalIncome - $totalExpense;

        // Phân trang thủ công
        $total = count($allTransactions);
        $start = ($page - 1) * $perPage;
        $pagedTransactions = array_slice($allTransactions, $start, $perPage);

        // Chuẩn bị biến cho view
        return view('financial/fund_transactions', [
            'transactions' => $pagedTransactions,
            'funds' => $funds,
            'transactionTypes' => $transactionTypes,
            'fundFilter' => $fundId,
            'dateFromFilter' => $dateFrom,
            'dateToFilter' => $dateTo,
            'transactionTypeFilter' => $transactionTypeId,
            'pager' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
            ],
            'totalTransactions' => $totalTransactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalWithdraw' => $totalWithdraw,
            'fundBalances' => $fundBalances,
            'soDuQuy' => $soDuQuy,
            'statusFilter' => $status,
        ]);
    }

    public function exportFundTransactions()
    {
        $fundId = $this->request->getGet('fund_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $status = $this->request->getGet('status');
        $transactionTypeId = $this->request->getGet('transaction_type_id');

        $fundModel = new \App\Models\FundModel();
        $funds = $fundModel->findAll();

        // Lấy dữ liệu nạp tiền và rút tiền khách hàng
        $customerModel1 = new \App\Models\CustomerTransactionModel();
        $customerModel2 = new \App\Models\CustomerTransactionModel();
        // Deposit
        $depositQuery = $customerModel1->select('customer_transactions.id, customer_transactions.created_at, customer_transactions.transaction_date, "income" as type, customer_transactions.amount, customer_transactions.fund_id, customer_transactions.notes as description, "approved" as status, customer_transactions.created_by, NULL as approved_by, funds.name as fund_name, u1.fullname as creator_name, NULL as approver_name, 1 as is_customer_deposit, customers.customer_code, tt.name as transaction_type_name')
            ->join('funds', 'funds.id = customer_transactions.fund_id', 'left')
            ->join('users u1', 'u1.id = customer_transactions.created_by', 'left')
            ->join('customers', 'customers.id = customer_transactions.customer_id', 'left')
            ->join('transaction_types tt', 'tt.id = customer_transactions.transaction_type_id', 'left')
            ->where('customer_transactions.transaction_type', 'deposit');
        // Withdraw
        $withdrawQuery = $customerModel2->select('customer_transactions.id, customer_transactions.created_at, customer_transactions.transaction_date, "withdraw" as type, customer_transactions.amount, customer_transactions.fund_id, customer_transactions.notes as description, "approved" as status, customer_transactions.created_by, NULL as approved_by, funds.name as fund_name, u1.fullname as creator_name, NULL as approver_name, 1 as is_customer_deposit, customers.customer_code, tt.name as transaction_type_name')
            ->join('funds', 'funds.id = customer_transactions.fund_id', 'left')
            ->join('users u1', 'u1.id = customer_transactions.created_by', 'left')
            ->join('customers', 'customers.id = customer_transactions.customer_id', 'left')
            ->join('transaction_types tt', 'tt.id = customer_transactions.transaction_type_id', 'left')
            ->where('customer_transactions.transaction_type', 'withdraw');
        // Filter
        if ($fundId) {
            $depositQuery->where('customer_transactions.fund_id', $fundId);
            $withdrawQuery->where('customer_transactions.fund_id', $fundId);
        }
        if ($dateFrom) {
            $depositQuery->where('customer_transactions.transaction_date >=', $dateFrom);
            $withdrawQuery->where('customer_transactions.transaction_date >=', $dateFrom);
        }
        if ($dateTo) {
            $depositQuery->where('customer_transactions.transaction_date <=', $dateTo);
            $withdrawQuery->where('customer_transactions.transaction_date <=', $dateTo);
        }
        if ($transactionTypeId) {
            $depositQuery->where('customer_transactions.transaction_type_id', $transactionTypeId);
            $withdrawQuery->where('customer_transactions.transaction_type_id', $transactionTypeId);
        }
        if ($status && $status !== 'approved') {
            $customerTransactions = [];
        } else {
            $depositTransactions = $depositQuery->findAll();
            $withdrawTransactions = $withdrawQuery->findAll();
            // Gắn cờ và mô tả cho withdraw
            foreach ($withdrawTransactions as &$w) {
                $w['is_customer_withdraw'] = true;
                $w['type'] = 'withdraw';
                if (!empty($w['customer_code'])) {
                    $w['description'] = '<span class="badge bg-warning">' . esc($w['customer_code']) . '</span> ' . $w['description'];
                }
            }
            unset($w);
            $customerTransactions = array_merge($depositTransactions, $withdrawTransactions);
        }

        $financialModel = new \App\Models\FinancialTransactionModel();
        $financialQuery = $financialModel->select('financial_transactions.id, financial_transactions.created_at, financial_transactions.transaction_date, financial_transactions.type, financial_transactions.amount, financial_transactions.fund_id, financial_transactions.description, financial_transactions.status, financial_transactions.created_by, financial_transactions.approved_by, funds.name as fund_name, u1.fullname as creator_name, u2.fullname as approver_name, 0 as is_customer_deposit, tt.name as transaction_type_name')
            ->join('funds', 'funds.id = financial_transactions.fund_id', 'left')
            ->join('users u1', 'u1.id = financial_transactions.created_by', 'left')
            ->join('users u2', 'u2.id = financial_transactions.approved_by', 'left')
            ->join('transaction_types tt', 'tt.id = financial_transactions.transaction_type_id', 'left');
        if ($fundId) {
            $financialQuery->where('financial_transactions.fund_id', $fundId);
        }
        if ($dateFrom) {
            $financialQuery->where('financial_transactions.transaction_date >=', $dateFrom);
        }
        if ($dateTo) {
            $financialQuery->where('financial_transactions.transaction_date <=', $dateTo);
        }
        if ($transactionTypeId) {
            $financialQuery->where('financial_transactions.transaction_type_id', $transactionTypeId);
        }
        if ($status) {
            $financialQuery->where('financial_transactions.status', $status);
        }
        $financialTransactions = $financialQuery->findAll();

        $allTransactions = array_merge($customerTransactions, $financialTransactions);
        usort($allTransactions, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Xuất excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'STT');
        $sheet->setCellValue('B1', 'Ngày tạo');
        $sheet->setCellValue('C1', 'Ngày giao dịch');
        $sheet->setCellValue('D1', 'Loại');
        $sheet->setCellValue('E1', 'Thu');
        $sheet->setCellValue('F1', 'Chi');
        $sheet->setCellValue('G1', 'Quỹ');
        $sheet->setCellValue('H1', 'Mã khách hàng');
        $sheet->setCellValue('I1', 'Mô tả');
        $sheet->setCellValue('J1', 'Loại giao dịch');
        $sheet->setCellValue('K1', 'Trạng thái');
        $sheet->setCellValue('L1', 'Người tạo');
        $sheet->setCellValue('M1', 'Người duyệt');

        $row = 2;
        $stt = 1;
        foreach ($allTransactions as $t) {
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, $t['created_at'] ? date('d/m/Y H:i', strtotime($t['created_at'])) : '');
            $sheet->setCellValue('C' . $row, $t['transaction_date'] ? date('d/m/Y', strtotime($t['transaction_date'])) : '');

            // Xử lý loại giao dịch
            if ($t['type'] === 'income') {
                $sheet->setCellValue('D' . $row, 'Thu');
                $sheet->setCellValue('E' . $row, $t['amount']);
                $sheet->setCellValue('F' . $row, '');
            } elseif ($t['type'] === 'expense') {
                $sheet->setCellValue('D' . $row, 'Chi');
                $sheet->setCellValue('E' . $row, '');
                $sheet->setCellValue('F' . $row, $t['amount']);
            } elseif ($t['type'] === 'withdraw' && !empty($t['is_customer_withdraw'])) {
                $sheet->setCellValue('D' . $row, 'Rút');
                $sheet->setCellValue('E' . $row, '');
                $sheet->setCellValue('F' . $row, abs($t['amount']));
            } else {
                $sheet->setCellValue('D' . $row, $t['type']);
                $sheet->setCellValue('E' . $row, '');
                $sheet->setCellValue('F' . $row, '');
            }

            $sheet->setCellValue('G' . $row, $t['fund_name'] ?? '');
            $sheet->setCellValue('H' . $row, !empty($t['is_customer_deposit']) || !empty($t['is_customer_withdraw']) ? ($t['customer_code'] ?? '') : '');
            $sheet->setCellValue('I' . $row, strip_tags($t['description'] ?? ''));
            $sheet->setCellValue('J' . $row, $t['transaction_type_name'] ?? '');
            $sheet->setCellValue('K' . $row, $t['status'] === 'approved' ? 'Đã duyệt' : 'Chờ duyệt');
            $sheet->setCellValue('L' . $row, $t['creator_name'] ?? '');
            $sheet->setCellValue('M' . $row, $t['approver_name'] ?? '');
            $row++;
        }

        // Xuất file
        $fileName = 'danh_sach_thu_chi_quy_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
