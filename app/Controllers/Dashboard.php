<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\OrderModel;
use App\Models\ProductTypeModel;
use App\Models\InvoiceModel;

class Dashboard extends BaseController
{
    protected $customerModel;
    protected $orderModel;
    protected $productTypeModel;
    protected $invoiceModel;
    protected $db;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->orderModel = new OrderModel();
        $this->productTypeModel = new ProductTypeModel();
        $this->invoiceModel = new InvoiceModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Thống kê đơn hàng hôm nay
        $today = date('Y-m-d');
        $data['todayStats'] = [
            'china_import' => $this->orderModel
                ->where('DATE(created_at)', $today)
                ->where('vietnam_stock_date IS NULL')
                ->countAllResults(),
            'vietnam_import' => $this->orderModel
                ->where('DATE(vietnam_stock_date)', $today)
                ->countAllResults(),
            'in_stock' => $this->orderModel
                ->where('invoice_id IS NULL')
                ->countAllResults(),
            'exported' => $this->orderModel
                ->join('invoices i', 'i.id = orders.invoice_id')
                ->where('DATE(i.created_at)', $today)
                ->countAllResults()
        ];

        // Thống kê số bao và số lô hôm nay
        $data['packageStats'] = $this->orderModel->getPackageAndBatchStats($today);

        // Thống kê giao dịch hôm nay
        $data['transactionStats'] = [
            'deposit_count' => $this->db->table('customer_transactions')
                ->where('transaction_type', 'deposit')
                ->where('DATE(created_at)', $today)
                ->countAllResults(),
            'payment_count' => $this->db->table('invoice_payments')
                ->where('DATE(payment_date)', $today)
                ->countAllResults(),
            'deposit_amount' => $this->db->table('customer_transactions')
                ->selectSum('amount')
                ->where('transaction_type', 'deposit')
                ->where('DATE(created_at)', $today)
                ->get()
                ->getRow()
                ->amount ?? 0,
            'payment_amount' => $this->db->table('invoice_payments')
                ->selectSum('amount')
                ->where('DATE(payment_date)', $today)
                ->get()
                ->getRow()
                ->amount ?? 0
        ];

        // Thống kê phiếu xuất hôm nay
        $data['invoiceStats'] = [
            'new' => $this->invoiceModel
                ->where('DATE(created_at)', $today)
                ->countAllResults(),
            'shipped' => $this->invoiceModel
                ->where('DATE(shipping_confirmed_at)', $today)
                ->countAllResults(),
            'total_orders' => $this->orderModel
                ->join('invoices i', 'i.id = orders.invoice_id')
                ->where('DATE(i.created_at)', $today)
                ->countAllResults()
        ];

        // Đếm số phiếu xuất quá hạn 7 ngày
        $data['totalOverdueInvoices'] = $this->invoiceModel
            ->where('payment_status', 'unpaid')
            ->where('created_at <=', date('Y-m-d', strtotime('-7 days')))
            ->countAllResults();

        // Gán vào invoiceStats để dùng chung
        $data['invoiceStats']['overdue'] = $data['totalOverdueInvoices'];

        // Tổng quan số liệu
        $data['totalCustomers'] = $this->customerModel->countAll();
        $data['totalOrders'] = $this->orderModel->countAll();
        $data['newCustomers'] = $this->customerModel
            ->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))
            ->countAllResults();
        $data['newOrders'] = $this->orderModel
            ->where('MONTH(created_at)', date('m'))
            ->where('YEAR(created_at)', date('Y'))
            ->countAllResults();

        // Lấy 5 phiếu xuất quá hạn gần đây nhất
        $data['recentOverdueInvoices'] = $this->db->table('invoices i')
            ->select('
                i.*,
                c.fullname as customer_name,
                c.customer_code,
                c.id as customer_id,
                DATEDIFF(CURDATE(), DATE(i.created_at)) as days_overdue
            ')
            ->join('customers c', 'c.id = i.customer_id', 'left')
            ->where('i.payment_status', 'unpaid')
            ->where('i.created_at <=', date('Y-m-d', strtotime('-7 days')))
            ->orderBy('i.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // Tính total_amount cho mỗi phiếu xuất quá hạn
        foreach ($data['recentOverdueInvoices'] as &$invoice) {
            try {
                $orders = $this->orderModel->where('invoice_id', $invoice['id'])->findAll();
                $total = 0;
                foreach ($orders as $order) {
                    $totalWeight = floatval($order['total_weight'] ?? 0);
                    $pricePerKg = floatval($order['price_per_kg'] ?? 0);
                    $volume = floatval($order['volume'] ?? 0);
                    $pricePerCubicMeter = floatval($order['price_per_cubic_meter'] ?? 0);
                    $domesticFee = floatval($order['domestic_fee'] ?? 0);
                    $exchangeRate = floatval($order['exchange_rate'] ?? 0);

                    $priceByWeight = $totalWeight * $pricePerKg;
                    $priceByVolume = $volume * $pricePerCubicMeter;
                    $finalPrice = max($priceByWeight, $priceByVolume) + ($domesticFee * $exchangeRate);
                    $total += $finalPrice;
                }

                $shippingFee = floatval($invoice['shipping_fee'] ?? 0);
                $otherFee = floatval($invoice['other_fee'] ?? 0);
                $invoice['total_amount'] = $total + $shippingFee + $otherFee;
            } catch (\Exception $e) {
                log_message('error', 'Error calculating total amount for invoice ' . $invoice['id'] . ': ' . $e->getMessage());
                $invoice['total_amount'] = 0;
            }
        }

        // Lấy danh sách đơn hàng gần đây
        $data['recentOrders'] = $this->orderModel
            ->select("orders.*, orders.id, orders.tracking_code, orders.created_at, orders.vietnam_stock_date,
                customers.fullname as customer_name, customers.customer_code as customer_code,
                i.shipping_confirmed_at, i.id as invoice_id")
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->orderBy('orders.created_at', 'DESC')
            ->limit(8)
            ->findAll();

        // Lấy danh sách 10 khách hàng đặt hàng nhiều nhất
        $data['topCustomers'] = $this->customerModel->getTopCustomers();

        // Dữ liệu biểu đồ: Số lượng đơn hàng trong 30 ngày gần nhất
        $ordersData = $this->orderModel->getOrdersInLast30Days();
        $chartLabels = [];
        $chartData = [];
        $last30Days = new \DatePeriod(
            new \DateTime('-30 days'),
            new \DateInterval('P1D'),
            new \DateTime('+1 day')
        );

        foreach ($last30Days as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabels[] = $formattedDate;
            $totalOrders = 0;

            foreach ($ordersData as $order) {
                if ($order['order_date'] === $formattedDate) {
                    $totalOrders = $order['total_orders'];
                    break;
                }
            }
            $chartData[] = $totalOrders;
        }

        $data['chartLabels'] = $chartLabels;
        $data['chartData'] = $chartData;

        // Dữ liệu biểu đồ: Thống kê loại hàng được đặt
        $productTypeData = $this->productTypeModel->getProductTypeStatistics();
        $productTypeLabels = [];
        $productTypeValues = [];

        foreach ($productTypeData as $productType) {
            $productTypeLabels[] = $productType['name'];
            $productTypeValues[] = $productType['total_orders'];
        }

        $data['productTypeLabels'] = $productTypeLabels;
        $data['productTypeValues'] = $productTypeValues;

        // Lấy tổng cân nặng mỗi ngày trong 30 ngày gần nhất từ bảng orders
        $weightData = $this->orderModel
            ->select("DATE(created_at) as order_date, COALESCE(SUM(total_weight), 0) as total_weight")
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('order_date')
            ->orderBy('order_date', 'ASC')
            ->findAll();

        // Chuẩn bị dữ liệu cho biểu đồ
        $weightLabels = [];
        $weightValues = [];

        foreach ($weightData as $row) {
            $weightLabels[] = $row['order_date'];
            $weightValues[] = (float)$row['total_weight'];
        }

        // Chuyển đổi dữ liệu thành JSON để sử dụng trong view
        $data['weightLabels'] = json_encode($weightLabels);
        $data['weightValues'] = json_encode($weightValues);

        // Lấy phí giao hàng mỗi ngày trong 30 ngày gần nhất từ bảng invoices
        $shippingFeeData = $this->invoiceModel
            ->select("DATE(created_at) as invoice_date, COALESCE(SUM(shipping_fee), 0) as total_shipping_fee")
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('invoice_date')
            ->orderBy('invoice_date', 'ASC')
            ->findAll();

        // Chuẩn bị dữ liệu cho biểu đồ phí giao hàng
        $shippingFeeLabels = [];
        $shippingFeeValues = [];

        foreach ($shippingFeeData as $row) {
            $shippingFeeLabels[] = $row['invoice_date'];
            $shippingFeeValues[] = (float)$row['total_shipping_fee'];
        }

        $data['shippingFeeLabels'] = json_encode($shippingFeeLabels);
        $data['shippingFeeValues'] = json_encode($shippingFeeValues);

        return view('dashboard/index', $data);
    }
}
