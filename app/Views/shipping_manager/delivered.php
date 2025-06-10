<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('shipping-manager') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-truck"></i> Chờ giao
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

                    <div class="mb-3">
                        <form action="<?= base_url('shipping-manager/search') ?>" method="get" class="form-inline">
                            <div class="input-group">
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên người nhận, số điện thoại hoặc mã vận đơn..." value="<?= isset($keyword) ? $keyword : '' ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Mã phiếu xuất</th>
                                    <th class="text-center">Khách hàng</th>
                                    <th class="text-center">Người nhận</th>
                                    <th class="text-center">Số điện thoại</th>
                                    <th class="text-center">Địa chỉ</th>
                                    <th class="text-center">Đơn vị vận chuyển</th>
                                    <th class="text-center">Mã vận đơn</th>
                                    <th class="text-center">Phí vận chuyển</th>
                                    <th class="text-center">Ngày giao</th>
                                    <th class="text-center">Người xác nhận</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($shippings)) : ?>
                                    <tr>
                                        <td colspan="11" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($shippings as $shipping) : ?>
                                        <tr>
                                            <td class="text-center"><?= $shipping['id'] ?></td>
                                            <td class="text-center"><?= $shipping['invoice']['id'] ?? '' ?></td>
                                            <td class="text-center"><?= esc($shipping['customer']['fullname'] ?? '') ?></td>
                                            <td class="text-center"><?= esc($shipping['receiver_name']) ?></td>
                                            <td class="text-center"><?= esc($shipping['receiver_phone']) ?></td>
                                            <td class="text-center"><?= esc($shipping['receiver_address']) ?></td>
                                            <td class="text-center"><?= esc($shipping['shipping_provider']['name'] ?? '') ?></td>
                                            <td class="text-center"><?= esc($shipping['tracking_number']) ?></td>
                                            <td class="text-center"><?= number_format($shipping['shipping_fee']) ?> VNĐ</td>
                                            <td class="text-center"><?= date('d/m/Y H:i', strtotime($shipping['confirmed_at'])) ?></td>
                                            <td class="text-center"><?= esc($shipping['confirmer']['fullname'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>