<?php

namespace App\Controllers;

use App\Models\OrderModel;

class PackageController extends BaseController
{
    protected $orderModel;
    protected $db;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 30;

        // Lấy các tham số tìm kiếm
        $search = $this->request->getGet('search') ?? '';
        $startDate = $this->request->getGet('start_date') ?? '';
        $endDate = $this->request->getGet('end_date') ?? '';

        if ($search) {
            // Nếu có search, phân trang theo số lượng bao (không theo ngày)
            $builder = $this->db->table('orders')
                ->select('DATE(created_at) as package_date, package_code, COUNT(*) as order_count, SUM(total_weight) as total_weight')
                ->groupBy('DATE(created_at), package_code')
                ->orderBy('package_date', 'DESC')
                ->orderBy('package_code', 'ASC');
            if ($search === 'no-code') {
                $builder->groupStart()
                    ->where('package_code IS NULL', null, false)
                    ->orWhere('package_code', '')
                    ->groupEnd();
            } else {
                $builder->where('package_code', $search);
            }
            if ($startDate) {
                $builder->where('DATE(created_at) >=', $startDate);
            }
            if ($endDate) {
                $builder->where('DATE(created_at) <=', $endDate);
            }
            $total = $builder->countAllResults(false);
            $builder->limit($perPage, ($page - 1) * $perPage);
            $packages = $builder->get()->getResultArray();
            $pager = service('pager');
            $pager->setPath('packages');
            $pager->makeLinks($page, $perPage, $total, 'bootstrap_pagination');
            $data = [
                'packages' => $packages,
                'pager' => $pager,
                'search' => $search,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'current_package_date' => null,
                'total_package_days' => null
            ];
            return view('packages/index', $data);
        }

        // Nếu không search, vẫn phân trang theo ngày như hiện tại
        $perPage = 1; // Mỗi trang là 1 ngày
        // Lấy danh sách các ngày có bao
        $dateBuilder = $this->db->table('orders')
            ->select('DATE(created_at) as package_date')
            ->groupBy('DATE(created_at)')
            ->orderBy('package_date', 'DESC');
        if ($startDate) {
            $dateBuilder->where('DATE(created_at) >=', $startDate);
        }
        if ($endDate) {
            $dateBuilder->where('DATE(created_at) <=', $endDate);
        }
        $dates = $dateBuilder->get()->getResultArray();
        $dateList = array_column($dates, 'package_date');
        $totalDays = count($dateList);
        $currentDate = $dateList[$page - 1] ?? null;
        $packages = [];
        if ($currentDate) {
            $builder = $this->db->table('orders')
                ->select('DATE(created_at) as package_date, package_code, COUNT(*) as order_count, SUM(total_weight) as total_weight')
                ->where('DATE(created_at)', $currentDate)
                ->groupBy('DATE(created_at), package_code')
                ->orderBy('package_code', 'ASC');
            $packages = $builder->get()->getResultArray();
        }
        $pager = service('pager');
        $pager->setPath('packages');
        $pager->makeLinks($page, 1, $totalDays, 'bootstrap_pagination');
        $data = [
            'packages' => $packages,
            'pager' => $pager,
            'search' => $search,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'current_package_date' => $currentDate,
            'total_package_days' => $totalDays
        ];
        return view('packages/index', $data);
    }

    public function detail($packageCode, $date)
    {
        // Lấy thông tin các đơn hàng trong bao
        $builder = $this->db->table('orders')
            ->select('
                orders.*,
                customers.fullname as customer_name,
                customers.customer_code,
                product_types.name as product_type_name
            ')
            ->join('customers', 'customers.id = orders.customer_id')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->where('DATE(orders.created_at)', $date);

        // Xử lý điều kiện package_code
        if ($packageCode === 'no-code') {
            $builder->groupStart()
                ->where('orders.package_code IS NULL', null, false)
                ->orWhere('orders.package_code', '')
                ->groupEnd();
        } else {
            $builder->where('orders.package_code', $packageCode);
        }

        $orders = $builder->orderBy('orders.created_at', 'DESC')
            ->get()
            ->getResultArray();

        if (empty($orders)) {
            return redirect()->to('/packages')->with('error', 'Không tìm thấy đơn hàng nào.');
        }

        $data = [
            'package_code' => $packageCode === 'no-code' ? null : $packageCode,
            'package_date' => $date,
            'orders' => $orders,
            'total_orders' => count($orders)
        ];

        return view('packages/detail', $data);
    }
}
