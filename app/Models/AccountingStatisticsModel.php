<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountingStatisticsModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['customer_code', 'fullname', 'balance'];

    public function getCustomerBalance($customerId)
    {
        $cacheKey = "customer_balance_{$customerId}";
        $balance = cache($cacheKey);

        if (empty($balance)) {
            $balance = $this->db->table('customer_transactions')
                ->selectSum('amount', 'balance')
                ->where('customer_id', $customerId)
                ->get()->getRow()->balance ?? 0;

            cache()->save($cacheKey, $balance, 3600); // Cache trong 1 giờ
        }

        return $balance;
    }

    public function getCustomerDebtSummary($filters = [])
    {
        // Lấy danh sách khách hàng cơ bản
        $builder = $this->db->table('customers')
            ->select('
                customers.id,
                customers.customer_code,
                customers.fullname
            ');

        // Áp dụng bộ lọc nếu có
        if (!empty($filters['customer_code'])) {
            $builder->where('customers.customer_code', $filters['customer_code']);
        }

        $results = $builder->get()->getResultArray();

        // Tính toán bổ sung cho từng khách hàng
        foreach ($results as &$result) {
            $customerId = $result['id'];

            // 1. Số dư: Lấy từ hàm getCustomerBalance trong chính model này
            $result['balance'] = $this->getCustomerBalance($customerId);

            // 2. Tổng tiền hóa đơn: Tính tổng tiền tất cả hóa đơn của khách hàng
            $totalInvoicesQuery = $this->db->table('invoices i')
                ->select('SUM(
                    GREATEST(
                        COALESCE(o.total_weight, 0) * COALESCE(o.price_per_kg, 0),
                        COALESCE(o.volume, 0) * COALESCE(o.price_per_cubic_meter, 0)
                    ) + 
                    (COALESCE(o.domestic_fee, 0) * COALESCE(o.exchange_rate, 0)) + COALESCE(i.shipping_fee, 0)
                ) as total_invoices')
                ->join('orders o', 'o.invoice_id = i.id', 'left')
                ->where('i.customer_id', $customerId);

            if (!empty($filters['from_date'])) {
                $totalInvoicesQuery->where('i.created_at >=', $filters['from_date'] . ' 00:00:00');
            }
            if (!empty($filters['to_date'])) {
                $totalInvoicesQuery->where('i.created_at <=', $filters['to_date'] . ' 23:59:59');
            }

            $result['total_invoices'] = $totalInvoicesQuery->get()->getRow()->total_invoices ?? 0;

            // 3. Tổng tiền đã thanh toán: Tổng tiền hóa đơn với payment_status = 'paid'
            $totalPaidFromInvoices = $this->db->table('invoices i')
                ->select('SUM(
                    GREATEST(
                        COALESCE(o.total_weight, 0) * COALESCE(o.price_per_kg, 0),
                        COALESCE(o.volume, 0) * COALESCE(o.price_per_cubic_meter, 0)
                    ) + 
                    (COALESCE(o.domestic_fee, 0) * COALESCE(o.exchange_rate, 0)) + COALESCE(i.shipping_fee, 0)
                ) as total_paid')
                ->join('orders o', 'o.invoice_id = i.id', 'left')
                ->where('i.customer_id', $customerId)
                ->where('i.payment_status', 'paid');

            if (!empty($filters['from_date'])) {
                $totalPaidFromInvoices->where('i.created_at >=', $filters['from_date'] . ' 00:00:00');
            }
            if (!empty($filters['to_date'])) {
                $totalPaidFromInvoices->where('i.created_at <=', $filters['to_date'] . ' 23:59:59');
            }

            $result['total_paid'] = $totalPaidFromInvoices->get()->getRow()->total_paid ?? 0;

            // 4. Số tiền còn nợ: Tổng tiền hóa đơn - Tổng tiền đã thanh toán
            $result['debt'] = $result['total_invoices'] - $result['total_paid'];
        }

        // Sắp xếp theo nợ giảm dần
        usort($results, function ($a, $b) {
            return $b['debt'] <=> $a['debt'];
        });

        return $results;
    }
}
