<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Chi tiết mã phụ: <?= esc($subCustomer['sub_customer_code']) ?> - <?= esc($subCustomer['fullname']) ?></h3>
            <p>Thuộc khách hàng: <strong><?= esc($customer['customer_code']) ?> - <?= esc($customer['fullname']) ?></strong></p>

            <!-- Thông báo -->
            <?php if (session()->has('success')): ?>
                <div class="alert alert-success"><?= session('success') ?></div>
            <?php endif; ?>
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6">
            <!-- Thông tin cơ bản -->
            <div class="card mb-4">
                <div class="card-header">Thông tin cơ bản</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Mã phụ</th>
                            <td><?= esc($subCustomer['sub_customer_code']) ?></td>
                        </tr>
                        <tr>
                            <th>Họ và tên</th>
                            <td><?= esc($subCustomer['fullname']) ?></td>
                        </tr>
                        <tr>
                            <th>Số điện thoại</th>
                            <td><?= esc($subCustomer['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Địa chỉ</th>
                            <td><?= esc($subCustomer['address']) ?></td>
                        </tr>
                        <tr>
                            <th>Link Zalo</th>
                            <td><?= $subCustomer['zalo_link'] ? '<a href="' . esc($subCustomer['zalo_link']) . '" target="_blank">Zalo</a>' : 'Không có' ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= esc($subCustomer['email']) ?: 'Không có' ?></td>
                        </tr>
                    </table>
                    <a href="/customers/edit-sub/<?= $subCustomer['id'] ?>" class="btn btn-warning">Sửa thông tin</a>
                    <a href="/customers/detail/<?= $customer['id'] ?>" class="btn btn-info">Xem khách hàng chính</a>
                </div>
            </div>
        </div>
        <div class="col-6">
            <!-- Thống kê đơn hàng và phiếu xuất -->
            <div class="card mb-4">
                <div class="card-header">Thống kê</div>
                <div class="card-body">
                    <p>Ngày tạo: <strong><?= date('d/m/Y H:i', strtotime($subCustomer['created_at'])) ?></strong></p>
                    <p>Tổng số tiền đơn hàng: <strong class="text-success"><?= number_format($totalOrderAmount, 0, ',', '.') ?> VNĐ</strong></p>
                    <hr />
                    <div class="row">
                        <div class="col-6">
                            <h6>Thông tin đơn hàng</h6>
                            <p>
                                Tổng số đơn hàng: <strong><?= $totalOrders ?></strong>
                            </p>
                        </div>
                        <div class="col-6">
                            <h6>Thông tin phiếu xuất</h6>
                            <p>
                                Tổng phiếu xuất: <strong><?= $totalInvoices ?></strong><br />
                                Phiếu xuất đã thanh toán: <strong><?= $paidInvoices ?></strong><br />
                                Phiếu xuất chưa thanh toán: <strong class="text-danger"><?= $totalInvoices - $paidInvoices ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <!-- 10 đơn hàng gần nhất -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">10 đơn hàng gần nhất</h3>
                    <div class="card-tools">
                        <a href="/orders?sub_customer_id=<?= $subCustomer['id'] ?>" class="btn btn-primary btn-sm">Xem tất cả</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentOrders)): ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Mã theo dõi</th>
                                    <th>Ngày tạo</th>
                                    <th>Loại hàng</th>
                                    <th>Số lượng</th>
                                    <th>Khối lượng</th>
                                    <th>Thể tích</th>
                                    <th>Thành tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="text-center"><a href="/orders/edit/<?= $order['id'] ?>">#<?= $order['id'] ?></a></td>
                                        <td class="text-center"><?= esc($order['tracking_code']) ?></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td class="text-center"><?= esc($order['product_type_name'] ?? 'N/A') ?></td>
                                        <td class="text-center"><?= $order['quantity'] ?? 0 ?></td>
                                        <td class="text-center"><?= number_format($order['total_weight'] ?? 0, 2) ?> kg</td>
                                        <td class="text-center"><?= number_format($order['volume'] ?? 0, 3) ?> m³</td>
                                        <td class="text-center"><?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                        <td class="text-center">
                                            <?php
                                            $status = '';
                                            $badgeClass = '';

                                            if (!isset($order['vietnam_stock_date']) || $order['vietnam_stock_date'] === null) {
                                                $status = 'Kho TQ';
                                                $badgeClass = 'bg-primary';
                                            } elseif (!isset($order['invoice_id']) || $order['invoice_id'] === null) {
                                                $status = 'Tồn kho';
                                                $badgeClass = 'bg-danger';
                                            } elseif (isset($order['shipping_confirmed_at']) && $order['shipping_confirmed_at'] !== null) {
                                                $status = 'Đã giao';
                                                $badgeClass = 'bg-success';
                                            } else {
                                                $status = 'Chờ giao';
                                                $badgeClass = 'bg-warning';
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center">Không có đơn hàng nào.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($recentInvoices)): ?>
                <!-- 10 phiếu xuất gần nhất -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">10 phiếu xuất gần nhất</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày tạo</th>
                                    <th>Số đơn hàng</th>
                                    <th>Khối lượng</th>
                                    <th>Thể tích</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái xuất</th>
                                    <th>Thanh toán</th>
                                    <th>Người tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvoices as $invoice): ?>
                                    <tr>
                                        <td class="text-center"><a href="/invoices/detail/<?= $invoice['id'] ?>">#<?= $invoice['id'] ?></a></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></td>
                                        <td class="text-center"><?= $invoice['total_orders'] ?></td>
                                        <td class="text-center"><?= number_format($invoice['total_weight'] ?? 0, 2) ?> kg</td>
                                        <td class="text-center"><?= number_format($invoice['total_volume'] ?? 0, 3) ?> m³</td>
                                        <td class="text-center"><?= number_format($invoice['total_amount'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                        <td class="text-center">
                                            <span class="badge <?= !isset($invoice['shipping_confirmed_at']) || $invoice['shipping_confirmed_at'] === null ? 'bg-warning' : 'bg-success' ?>">
                                                <?= !isset($invoice['shipping_confirmed_at']) || $invoice['shipping_confirmed_at'] === null ? 'Chưa xuất' : 'Đã xuất' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= !isset($invoice['payment_status']) || $invoice['payment_status'] == 'unpaid' ? 'bg-danger' : 'bg-primary' ?>">
                                                <?= !isset($invoice['payment_status']) || $invoice['payment_status'] == 'unpaid' ? 'Chưa thanh toán' : 'Đã thanh toán' ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><?= esc($invoice['created_by_name'] ?? 'Không rõ') ?></td>
                                        <td class="text-center">
                                            <a href="/invoices/detail/<?= $invoice['id'] ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>