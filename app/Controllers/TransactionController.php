<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TransactionController extends BaseController
{
    protected $db;
    protected $perPage = 20;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        try {
            $page = $this->request->getVar('page') ?? 1;
            $customerCode = $this->request->getVar('customer_code');
            $startDate = $this->request->getVar('start_date');
            $endDate = $this->request->getVar('end_date');
            $transactionType = $this->request->getVar('transaction_type');
            $fundId = $this->request->getVar('fund_id');

            // Xây dựng query cơ bản
            $builder = $this->db->table('customer_transactions ct')
                ->select('
                    ct.*,
                    c.customer_code,
                    c.fullname as customer_name,
                    u.fullname as created_by_name,
                    i.id as invoice_id,
                    f.name as fund_name
                ')
                ->join('customers c', 'c.id = ct.customer_id', 'left')
                ->join('users u', 'u.id = ct.created_by', 'left')
                ->join('invoices i', 'i.id = ct.invoice_id', 'left')
                ->join('funds f', 'f.id = ct.fund_id', 'left');

            // Thêm điều kiện tìm kiếm
            if ($customerCode) {
                $builder->where('c.customer_code', $customerCode);
            }
            if ($startDate) {
                $builder->where('DATE(ct.created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(ct.created_at) <=', $endDate);
            }
            if ($transactionType) {
                $builder->where('ct.transaction_type', $transactionType);
            }
            if ($fundId) {
                $builder->where('ct.fund_id', $fundId);
            }

            // Clone builder để tính toán tổng theo bộ lọc
            $filteredQueryBuilder = clone $builder;
            $filteredDeposit = $filteredQueryBuilder->where('ct.transaction_type', 'deposit')->selectSum('ct.amount', 'total')->get()->getRow()->total ?? 0;

            // Clone lại builder gốc để tính tổng thanh toán
            $filteredQueryBuilder = clone $builder;
            $filteredPayment = $filteredQueryBuilder->where('ct.transaction_type', 'payment')->selectSum('ct.amount', 'total')->get()->getRow()->total ?? 0;

            // Clone lại builder gốc để tính tổng rút tiền
            $filteredQueryBuilder = clone $builder;
            $filteredWithdraw = $filteredQueryBuilder->where('ct.transaction_type', 'withdraw')->selectSum('ct.amount', 'total')->get()->getRow()->total ?? 0;

            $filteredBalance = $filteredDeposit + $filteredPayment + $filteredWithdraw; // Thanh toán và rút tiền được lưu dưới dạng số âm

            // Clone builder để đếm tổng số bản ghi
            $total = $builder->countAllResults(false);

            // Thêm sắp xếp và phân trang
            $transactions = $builder
                ->orderBy('ct.created_at', 'DESC')
                ->limit($this->perPage, ($page - 1) * $this->perPage)
                ->get()
                ->getResultArray();

            $fundModel = new \App\Models\FundModel();
            $funds = $fundModel->findAll();

            // Tạo đối tượng phân trang
            $pager = service('pager');
            $pager->setPath('transactions');
            $pager->makeLinks($page, $this->perPage, $total);

            // Chuẩn bị dữ liệu cho view
            $data = [
                'transactions' => $transactions,
                'pager' => $pager,
                'total' => $total,
                'perPage' => $this->perPage,
                'page' => $page,
                'totalDeposit' => $filteredDeposit,
                'totalPayment' => $filteredPayment,
                'totalWithdraw' => $filteredWithdraw,
                'customerCode' => $customerCode,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'transactionType' => $transactionType,
                'funds' => $funds,
                'fundId' => $fundId,
                'filteredDeposit' => $filteredDeposit,
                'filteredPayment' => $filteredPayment,
                'filteredWithdraw' => $filteredWithdraw,
                'filteredBalance' => $filteredBalance
            ];


            return view('transactions/index', $data);
        } catch (\Exception $e) {
            log_message('error', '[TransactionController::index] Error: ' . $e->getMessage());
            return view('transactions/index', [
                'error' => 'Có lỗi xảy ra khi tải danh sách giao dịch: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        if (!in_array(session('role'), ['Quản lý', 'admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Bạn không có quyền xóa giao dịch!']);
        }

        $transaction = $this->db->table('customer_transactions')->where('id', $id)->get()->getRowArray();
        if (!$transaction) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Không tìm thấy giao dịch!']);
        }

        $customerModel = new \App\Models\CustomerModel();
        $balance = $customerModel->getCustomerBalance($transaction['customer_id']);
        if ($transaction['amount'] > $balance) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Số dư khách hàng không đủ để xóa giao dịch này!']);
        }

        $this->db->table('customer_transactions')->where('id', $id)->delete();

        // Ghi log xóa giao dịch
        $logModel = new \App\Models\SystemLogModel();
        $logModel->addLog([
            'entity_type' => 'transaction',
            'entity_id' => $id,
            'action_type' => 'delete',
            'created_by' => session('user_id'),
            'details' => json_encode($transaction),
            'notes' => 'Xóa giao dịch tài chính'
        ]);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Đã xóa giao dịch thành công!']);
    }

    public function getCustomerBalanceApi($customerId)
    {
        $customerModel = new \App\Models\CustomerModel();
        $balance = $customerModel->getCustomerBalanceDirect($customerId);
        return $this->response->setJSON(['balance' => $balance]);
    }

    /**
     * Xử lý rút tiền khách hàng
     */
    public function withdraw()
    {
        if (!in_array(session('role'), ['Kế toán', 'Quản lý'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bạn không có quyền rút tiền.'
            ]);
        }

        try {
            $customerId = $this->request->getPost('customer_id');
            $amount = (float)str_replace('.', '', $this->request->getPost('amount'));
            $fundId = $this->request->getPost('fund_id');
            $notes = $this->request->getPost('notes');
            $transactionDate = $this->request->getPost('transaction_date') ?: date('Y-m-d');

            // Validate
            if (!$customerId || !$fundId || !$amount || $amount <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Vui lòng nhập đầy đủ thông tin và số tiền hợp lệ.'
                ]);
            }

            $customerModel = new \App\Models\CustomerModel();
            $transactionModel = new \App\Models\CustomerTransactionModel();

            $customer = $customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Khách hàng không tồn tại.'
                ]);
            }

            // Kiểm tra số dư hiện tại
            $currentBalance = $customerModel->getCustomerBalanceDirect($customerId);
            if ($currentBalance < $amount) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Số dư không đủ để rút tiền. Số dư hiện tại: ' . number_format($currentBalance, 0, ',', '.') . ' VNĐ'
                ]);
            }

            // Tạo giao dịch rút tiền
            $transactionData = [
                'customer_id' => $customerId,
                'fund_id' => $fundId,
                'transaction_type' => 'withdraw',
                'amount' => -$amount, // Ghi âm vì là rút tiền
                'notes' => $notes,
                'created_by' => session()->get('user_id'),
                'transaction_date' => $transactionDate,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Kiểm tra kết quả insert
            $insertResult = $transactionModel->insert($transactionData);
            if (!$insertResult) {
                log_message('error', 'withdraw - Insert transaction failed: ' . json_encode($transactionModel->errors()));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể tạo giao dịch. Vui lòng thử lại.'
                ]);
            }

            // Cập nhật số dư khách hàng
            $newBalance = $currentBalance - $amount;
            $updateResult = $customerModel->update($customerId, ['balance' => $newBalance]);
            if (!$updateResult) {
                log_message('error', 'withdraw - Update customer balance failed');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không thể cập nhật số dư. Vui lòng thử lại.'
                ]);
            }

            // Ghi log
            $logModel = new \App\Models\SystemLogModel();
            $logModel->addLog([
                'entity_type' => 'transaction',
                'entity_id' => $insertResult,
                'action_type' => 'create',
                'created_by' => session('user_id'),
                'details' => json_encode($transactionData),
                'notes' => 'Rút tiền khách hàng'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Rút tiền thành công! Số dư còn lại: ' . number_format($newBalance, 0, ',', '.') . ' VNĐ',
                'new_balance' => number_format($newBalance, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'withdraw - Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
