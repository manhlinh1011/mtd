<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách hoa hồng cộng tác viên</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('affiliate-commission/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Thêm mới
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Cộng tác viên</th>
                                    <th class="text-center">Mã đơn hàng</th>
                                    <th class="text-center">Số tiền</th>
                                    <th class="text-center">Loại hoa hồng</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commissions as $commission) : ?>
                                    <tr>
                                        <td class="text-center"><?= $commission['id'] ?></td>
                                        <td class="text-center"><?= $commission['aff_name'] ?></td>
                                        <td class="text-center"><?= $commission['order_code'] ?></td>
                                        <td class="text-center"><?= number_format($commission['commission_amount'], 2) ?></td>
                                        <td class="text-center">
                                            <?php
                                            $types = [
                                                'weight' => 'Theo cân nặng',
                                                'volume' => 'Theo thể tích',
                                                'other' => 'Khác'
                                            ];
                                            echo $types[$commission['commission_type']] ?? $commission['commission_type'];
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statuses = [
                                                'pending' => '<span class="badge badge-warning">Chờ duyệt</span>',
                                                'approved' => '<span class="badge badge-info">Đã duyệt</span>',
                                                'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
                                                'cancelled' => '<span class="badge badge-danger">Đã hủy</span>'
                                            ];
                                            echo $statuses[$commission['payment_status']] ?? $commission['payment_status'];
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('affiliate-commission/edit/' . $commission['id']) ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('affiliate-commission/logs/' . $commission['id']) ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-history"></i>
                                            </a>
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