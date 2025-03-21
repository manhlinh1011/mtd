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
                                    <td><?= $package_code === 'no-code' ? '<span class="text-danger">KHÔNG CÓ MÃ BAO</span>' : $package_code ?></td>
                                </tr>
                                <tr>
                                    <th>Ngày nhập:</th>
                                    <td><?= date('d/m/Y', strtotime($package_date)) ?></td>
                                </tr>
                                <tr>
                                    <th>Tổng số đơn:</th>
                                    <td><?= $total_orders ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Danh sách đơn hàng -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Mã Lô</th>
                                    <th class="text-center">Mã vận đơn</th>
                                    <th class="text-center">Mã bao</th>
                                    <th class="text-center">Khách hàng</th>
                                    <th class="text-center">Loại hàng</th>
                                    <th class="text-center">Cân nặng (kg)</th>
                                    <th class="text-center">Khối (m³)</th>
                                    <th class="text-center">Thời gian</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="text-center"><?= $order['order_code'] ?></td>
                                        <td class="text-center"><?= $order['tracking_code'] ?></td>
                                        <td class="text-center"><?= empty($order['package_code']) ? '<span class="text-danger">KHÔNG CÓ MÃ BAO</span>' : $order['package_code'] ?></td>
                                        <td>
                                            <?= $order['customer_code'] ?> - <?= $order['customer_name'] ?>
                                        </td>
                                        <td class="text-center"><?= $order['product_type_name'] ?></td>
                                        <td class="text-center"><?= number_format($order['total_weight'], 2) ?></td>
                                        <td class="text-center"><?= number_format($order['volume'], 4) ?></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td class="text-center">
                                            <a href="/orders/detail/<?= $order['id'] ?>" class="btn btn-info btn-sm">Chi tiết</a>
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