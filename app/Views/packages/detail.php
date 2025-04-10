<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Mã bao: <strong><?= empty($package_code) ? 'KHÔNG CÓ MÃ BAO' : $package_code ?></strong>
                        | Ngày: <strong><?= date('d/m/Y', strtotime($package_date)) ?></strong>
                        | Tổng số đơn: <strong><?= $total_orders ?></strong>
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Thông tin mã bao -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Mã bao:</th>
                                    <td class="text-center"><strong><?= empty($package_code) ? 'KHÔNG CÓ MÃ BAO' : $package_code ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Ngày nhập:</th>
                                    <td class="text-center"><strong><?= date('d/m/Y', strtotime($package_date)) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Tổng số đơn:</th>
                                    <td class="text-center"><strong><?= $total_orders ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <a href="#" class="btn btn-primary btn-sm"><i class="mdi mdi-check"></i> Xác nhận tất cả đã về kho VN</a>
                        </div>
                    </div>

                    <!-- Danh sách đơn hàng -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Chọn</th>
                                    <th class="text-center">Thời gian</th>
                                    <th class="text-center">Kho VN</th>
                                    <th class="text-center">Mã vận đơn</th>
                                    <th class="text-center">Khách hàng</th>
                                    <th class="text-center">Mã Lô</th>
                                    <th class="text-center">Mã bao</th>
                                    <th class="text-center">Loại hàng</th>
                                    <th class="text-center">Cân nặng (kg)</th>
                                    <th class="text-center">Khối (m³)</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="order_ids[]" value="<?= $order['id'] ?>">
                                        </td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td class="text-center">
                                            <?= $order['vietnam_stock_date'] ? date('d/m/Y', strtotime($order['vietnam_stock_date'])) : '-' ?>
                                        </td>
                                        <td class="text-center"><?= $order['tracking_code'] ?></td>
                                        <td class="text-center">
                                            <?= $order['customer_code'] ?> - <?= $order['customer_name'] ?>
                                        </td>
                                        <td class="text-center"><?= $order['order_code'] ?></td>
                                        <td class="text-center"><?= empty($order['package_code']) ? '<span class="text-danger">KHÔNG CÓ MÃ BAO</span>' : $order['package_code'] ?></td>
                                        <td class="text-center"><?= $order['product_type_name'] ?></td>
                                        <td class="text-center"><?= number_format($order['total_weight'], 2) ?></td>
                                        <td class="text-center"><?= number_format($order['volume'], 3) ?></td>
                                        <td class="text-center">
                                            <?php if ($order['vietnam_stock_date'] === null): ?>
                                                <span class="badge bg-primary">Kho TQ</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Kho VN</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                            <?php if ($order['vietnam_stock_date'] === null): ?>
                                                <a href="#" class="btn btn-warning btn-sm">Xác nhận về kho VN</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>