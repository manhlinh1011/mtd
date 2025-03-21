<?php

namespace App\Controllers;

use App\Models\OrderModel;

class DashboardController
{
    public function index()
    {
        // Lấy đơn hàng gần đây
        $orderModel = new \App\Models\OrderModel();
        $data['recent_orders'] = $orderModel->select('orders.*, 
            customers.fullname AS customer_name, 
            customers.customer_code AS customer_code,
            product_types.name AS product_type_name,
            i.shipping_confirmed_at,
            i.id AS invoice_id')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('product_types', 'product_types.id = orders.product_type_id', 'left')
            ->join('invoices i', 'i.id = orders.invoice_id', 'left')
            ->orderBy('orders.id', 'DESC')
            ->limit(10)
            ->find();

        return view('dashboard/index', $data);
    }
}
