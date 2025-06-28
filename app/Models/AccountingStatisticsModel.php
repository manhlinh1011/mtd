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

            // 2. Tổng tiền hóa đơn: Tính tổng tiền từng hóa đơn, tránh cộng trùng shipping_fee
            $invoices = $this->db->table('invoices')
                ->select('id, shipping_fee')
                ->where('customer_id', $customerId);
            if (!empty($filters['from_date'])) {
                $invoices->where('created_at >=', $filters['from_date'] . ' 00:00:00');
            }
            if (!empty($filters['to_date'])) {
                $invoices->where('created_at <=', $filters['to_date'] . ' 23:59:59');
            }
            $invoiceList = $invoices->get()->getResultArray();
            $totalInvoices = 0;
            foreach ($invoiceList as $invoice) {
                $orderTotal = $this->db->table('orders')
                    ->select('SUM(GREATEST(
                        COALESCE(total_weight, 0) * COALESCE(price_per_kg, 0),
                        COALESCE(volume, 0) * COALESCE(price_per_cubic_meter, 0)
                    ) + (COALESCE(domestic_fee, 0) * COALESCE(exchange_rate, 0))) as order_total')
                    ->where('invoice_id', $invoice['id'])
                    ->get()->getRow()->order_total ?? 0;
                $totalInvoices += $orderTotal + ($invoice['shipping_fee'] ?? 0);
            }
            $result['total_invoices'] = $totalInvoices;

            // 3. Tổng tiền đã thanh toán: Chỉ tính các hóa đơn đã payment
            $paidInvoices = $this->db->table('invoices')
                ->select('id, shipping_fee')
                ->where('customer_id', $customerId)
                ->where('payment_status', 'paid');
            if (!empty($filters['from_date'])) {
                $paidInvoices->where('created_at >=', $filters['from_date'] . ' 00:00:00');
            }
            if (!empty($filters['to_date'])) {
                $paidInvoices->where('created_at <=', $filters['to_date'] . ' 23:59:59');
            }
            $paidInvoiceList = $paidInvoices->get()->getResultArray();
            $totalPaid = 0;
            foreach ($paidInvoiceList as $invoice) {
                $orderTotal = $this->db->table('orders')
                    ->select('SUM(GREATEST(
                        COALESCE(total_weight, 0) * COALESCE(price_per_kg, 0),
                        COALESCE(volume, 0) * COALESCE(price_per_cubic_meter, 0)
                    ) + (COALESCE(domestic_fee, 0) * COALESCE(exchange_rate, 0))) as order_total')
                    ->where('invoice_id', $invoice['id'])
                    ->get()->getRow()->order_total ?? 0;
                $totalPaid += $orderTotal + ($invoice['shipping_fee'] ?? 0);
            }
            $result['total_paid'] = $totalPaid;

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
