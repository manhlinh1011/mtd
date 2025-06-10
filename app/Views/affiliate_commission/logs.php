<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lịch sử thay đổi hoa hồng</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Cộng tác viên:</strong> <?= $commission['aff_name'] ?></p>
                            <p><strong>Mã đơn hàng:</strong> <?= $commission['order_code'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Số tiền hiện tại:</strong> <?= number_format($commission['commission_amount'], 2) ?></p>
                            <p><strong>Trạng thái hiện tại:</strong>
                                <?php
                                $statuses = [
                                    'pending' => '<span class="badge badge-warning">Chờ duyệt</span>',
                                    'approved' => '<span class="badge badge-info">Đã duyệt</span>',
                                    'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
                                    'cancelled' => '<span class="badge badge-danger">Đã hủy</span>'
                                ];
                                echo $statuses[$commission['payment_status']] ?? $commission['payment_status'];
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Thời gian</th>
                                    <th class="text-center">Số tiền</th>
                                    <th class="text-center">Loại hoa hồng</th>
                                    <th class="text-center">Lý do thay đổi</th>
                                    <th class="text-center">Người thay đổi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log) : ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y H:i:s', strtotime($log['changed_at'])) ?></td>
                                        <td class="text-center"><?= number_format($log['commission_amount'], 2) ?></td>
                                        <td class="text-center">
                                            <?php
                                            $types = [
                                                'weight' => 'Theo cân nặng',
                                                'volume' => 'Theo thể tích',
                                                'other' => 'Khác'
                                            ];
                                            echo $types[$log['commission_type']] ?? $log['commission_type'];
                                            ?>
                                        </td>
                                        <td class="text-center"><?= $log['change_reason'] ?></td>
                                        <td class="text-center"><?= $log['changed_by_name'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="<?= base_url('affiliate-commission') ?>" class="btn btn-secondary">Quay lại</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>