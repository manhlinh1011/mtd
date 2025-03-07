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

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->orderModel = new OrderModel();
        $this->productTypeModel = new ProductTypeModel();
        $this->invoiceModel = new InvoiceModel();
    }

    public function index()
    {
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
        $data['recentOrders'] = $this->orderModel
            ->select("orders.id, orders.tracking_code, customers.fullname as customer_name, customers.customer_code as customer_code, orders.created_at, 
        CASE 
            WHEN orders.invoice_id IS NULL THEN 'in_stock'  -- Đơn tồn kho (chưa có invoice)
            WHEN i.shipping_status = 'pending' THEN 'shipping'  -- Đơn đang giao
            WHEN i.shipping_status = 'confirmed' THEN 'shipped'  -- Đơn đã giao
            ELSE 'unknown'  -- Trường hợp khác
        END AS order_status")
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left') // Kết hợp với bảng invoices
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
