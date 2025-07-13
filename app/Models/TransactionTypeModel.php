<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionTypeModel extends Model
{
    protected $table = 'transaction_types';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'description',
        'category',
        'is_active',
        'sort_order',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[transaction_types.name,id,{id}]',
        'category' => 'required|in_list[income,expense]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Tên loại giao dịch là bắt buộc',
            'max_length' => 'Tên loại giao dịch không được vượt quá 100 ký tự',
            'is_unique' => 'Tên loại giao dịch đã tồn tại'
        ],
        'category' => [
            'required' => 'Phân loại là bắt buộc',
            'in_list' => 'Phân loại phải là thu hoặc chi'
        ]
    ];

    /**
     * Lấy danh sách loại giao dịch theo phân loại
     */
    public function getByCategory($category, $activeOnly = true)
    {
        $builder = $this->builder();
        $builder->where('category', $category);

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        $builder->orderBy('sort_order', 'ASC');
        $builder->orderBy('name', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Lấy danh sách loại giao dịch thu
     */
    public function getIncomeTypes($activeOnly = true)
    {
        return $this->getByCategory('income', $activeOnly);
    }

    /**
     * Lấy danh sách loại giao dịch chi
     */
    public function getExpenseTypes($activeOnly = true)
    {
        return $this->getByCategory('expense', $activeOnly);
    }

    /**
     * Lấy tất cả loại giao dịch đang hoạt động
     */
    public function getActiveTypes()
    {
        return $this->where('is_active', 1)
            ->orderBy('category', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Lấy loại giao dịch theo tên
     */
    public function getByName($name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Tạo loại giao dịch mới
     */
    public function createType($data)
    {
        // Tự động set sort_order nếu không có
        if (!isset($data['sort_order']) || empty($data['sort_order'])) {
            $maxOrder = $this->where('category', $data['category'])
                ->selectMax('sort_order')
                ->first();
            $data['sort_order'] = ($maxOrder['sort_order'] ?? 0) + 10;
        }

        return $this->insert($data);
    }

    /**
     * Cập nhật trạng thái hoạt động
     */
    public function toggleActive($id)
    {
        $type = $this->find($id);
        if (!$type) {
            return false;
        }

        $newStatus = $type['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Lấy thống kê số lượng giao dịch theo loại
     */
    public function getTransactionCounts()
    {
        $db = \Config\Database::connect();

        // Đếm giao dịch trong financial_transactions
        $financialCounts = $db->table('financial_transactions ft')
            ->select('ft.transaction_type_id, tt.name, COUNT(*) as count')
            ->join('transaction_types tt', 'tt.id = ft.transaction_type_id', 'left')
            ->where('ft.transaction_type_id IS NOT NULL')
            ->groupBy('ft.transaction_type_id')
            ->get()
            ->getResultArray();

        // Đếm giao dịch trong customer_transactions
        $customerCounts = $db->table('customer_transactions ct')
            ->select('ct.transaction_type_id, tt.name, COUNT(*) as count')
            ->join('transaction_types tt', 'tt.id = ct.transaction_type_id', 'left')
            ->where('ct.transaction_type_id IS NOT NULL')
            ->groupBy('ct.transaction_type_id')
            ->get()
            ->getResultArray();

        // Gộp kết quả
        $result = [];
        foreach ($financialCounts as $count) {
            $result[$count['transaction_type_id']] = [
                'name' => $count['name'],
                'financial_count' => $count['count'],
                'customer_count' => 0,
                'total_count' => $count['count']
            ];
        }

        foreach ($customerCounts as $count) {
            if (isset($result[$count['transaction_type_id']])) {
                $result[$count['transaction_type_id']]['customer_count'] = $count['count'];
                $result[$count['transaction_type_id']]['total_count'] += $count['count'];
            } else {
                $result[$count['transaction_type_id']] = [
                    'name' => $count['name'],
                    'financial_count' => 0,
                    'customer_count' => $count['count'],
                    'total_count' => $count['count']
                ];
            }
        }

        return $result;
    }

    /**
     * Lấy thống kê theo khoảng thời gian
     */
    public function getStatistics($dateFrom, $dateTo, $category = null)
    {
        $db = \Config\Database::connect();

        // Query financial transactions
        $financialQuery = $db->table('financial_transactions ft')
            ->select('ft.transaction_type_id, tt.name, tt.category, COUNT(*) as transaction_count, SUM(ft.amount) as total_amount, AVG(ft.amount) as average_amount')
            ->join('transaction_types tt', 'tt.id = ft.transaction_type_id', 'left')
            ->where('ft.transaction_type_id IS NOT NULL')
            ->where('ft.created_at >=', $dateFrom . ' 00:00:00')
            ->where('ft.created_at <=', $dateTo . ' 23:59:59')
            ->where('ft.status', 'approved');

        if ($category) {
            $financialQuery->where('tt.category', $category);
        }

        $financialStats = $financialQuery->groupBy('ft.transaction_type_id, tt.name, tt.category')
            ->get()
            ->getResultArray();

        // Query customer transactions
        $customerQuery = $db->table('customer_transactions ct')
            ->select('ct.transaction_type_id, tt.name, tt.category, COUNT(*) as transaction_count, SUM(ct.amount) as total_amount, AVG(ct.amount) as average_amount')
            ->join('transaction_types tt', 'tt.id = ct.transaction_type_id', 'left')
            ->where('ct.transaction_type_id IS NOT NULL')
            ->where('ct.created_at >=', $dateFrom . ' 00:00:00')
            ->where('ct.created_at <=', $dateTo . ' 23:59:59');

        if ($category) {
            $customerQuery->where('tt.category', $category);
        }

        $customerStats = $customerQuery->groupBy('ct.transaction_type_id, tt.name, tt.category')
            ->get()
            ->getResultArray();

        // Merge results
        $mergedStats = [];

        // Process financial transactions
        foreach ($financialStats as $stat) {
            $typeId = $stat['transaction_type_id'];
            $mergedStats[$typeId] = [
                'name' => $stat['name'],
                'category' => $stat['category'],
                'transaction_count' => $stat['transaction_count'],
                'total_amount' => $stat['total_amount'],
                'average_amount' => $stat['average_amount']
            ];
        }

        // Process customer transactions
        foreach ($customerStats as $stat) {
            $typeId = $stat['transaction_type_id'];
            if (isset($mergedStats[$typeId])) {
                $mergedStats[$typeId]['transaction_count'] += $stat['transaction_count'];
                $mergedStats[$typeId]['total_amount'] += $stat['total_amount'];
                // Recalculate average
                $totalCount = $mergedStats[$typeId]['transaction_count'];
                $totalAmount = $mergedStats[$typeId]['total_amount'];
                $mergedStats[$typeId]['average_amount'] = $totalCount > 0 ? $totalAmount / $totalCount : 0;
            } else {
                $mergedStats[$typeId] = [
                    'name' => $stat['name'],
                    'category' => $stat['category'],
                    'transaction_count' => $stat['transaction_count'],
                    'total_amount' => $stat['total_amount'],
                    'average_amount' => $stat['average_amount']
                ];
            }
        }

        // Calculate summary
        $summary = [
            'total_income' => 0,
            'total_expense' => 0,
            'total_amount' => 0,
            'total_transactions' => 0
        ];

        foreach ($mergedStats as $stat) {
            $summary['total_transactions'] += $stat['transaction_count'];
            $summary['total_amount'] += $stat['total_amount'];

            if ($stat['category'] === 'income') {
                $summary['total_income'] += $stat['total_amount'];
            } else {
                $summary['total_expense'] += $stat['total_amount'];
            }
        }

        $summary['net_amount'] = $summary['total_income'] - $summary['total_expense'];

        // Sort by category and total amount
        usort($mergedStats, function ($a, $b) {
            if ($a['category'] !== $b['category']) {
                return $a['category'] === 'income' ? -1 : 1;
            }
            return $b['total_amount'] <=> $a['total_amount'];
        });

        return [
            'summary' => $summary,
            'details' => array_values($mergedStats)
        ];
    }
}
