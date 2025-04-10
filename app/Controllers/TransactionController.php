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

            // Xây dựng query cơ bản
            $builder = $this->db->table('customer_transactions ct')
                ->select('
                    ct.*,
                    c.customer_code,
                    c.fullname as customer_name,
                    u.fullname as created_by_name,
                    i.id as invoice_id
                ')
                ->join('customers c', 'c.id = ct.customer_id', 'left')
                ->join('users u', 'u.id = ct.created_by', 'left')
                ->join('invoices i', 'i.id = ct.invoice_id', 'left');

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

            // Clone builder để đếm tổng số bản ghi
            $total = $builder->countAllResults(false);

            // Thêm sắp xếp và phân trang
            $transactions = $builder
                ->orderBy('ct.created_at', 'DESC')
                ->limit($this->perPage, ($page - 1) * $this->perPage)
                ->get()
                ->getResultArray();

            // Tính tổng tiền nạp và thanh toán
            $totalDeposit = $this->db->table('customer_transactions')
                ->selectSum('amount')
                ->where('transaction_type', 'deposit')
                ->get()
                ->getRow()
                ->amount ?? 0;

            $totalPayment = $this->db->table('customer_transactions')
                ->selectSum('amount')
                ->where('transaction_type', 'payment')
                ->get()
                ->getRow()
                ->amount ?? 0;

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
                'totalDeposit' => $totalDeposit,
                'totalPayment' => $totalPayment,
                'customerCode' => $customerCode,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'transactionType' => $transactionType
            ];

            return view('transactions/index', $data);
        } catch (\Exception $e) {
            log_message('error', '[TransactionController::index] Error: ' . $e->getMessage());
            return view('transactions/index', [
                'error' => 'Có lỗi xảy ra khi tải danh sách giao dịch: ' . $e->getMessage()
            ]);
        }
    }
}
