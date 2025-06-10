<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chỉnh sửa hoa hồng</h3>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('errors')) : ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('affiliate-commission/update/' . $commission['id']) ?>" method="post">
                        <div class="form-group">
                            <label for="aff_id">Cộng tác viên</label>
                            <input type="text" class="form-control" value="<?= $commission['aff_name'] ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="order_id">Đơn hàng</label>
                            <input type="text" class="form-control" value="<?= $commission['order_code'] ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="commission_amount">Số tiền hoa hồng <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="commission_amount" id="commission_amount" class="form-control" value="<?= old('commission_amount', $commission['commission_amount']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="commission_type">Loại hoa hồng <span class="text-danger">*</span></label>
                            <select name="commission_type" id="commission_type" class="form-control" required>
                                <option value="">Chọn loại hoa hồng</option>
                                <option value="weight" <?= old('commission_type', $commission['commission_type']) == 'weight' ? 'selected' : '' ?>>Theo cân nặng</option>
                                <option value="volume" <?= old('commission_type', $commission['commission_type']) == 'volume' ? 'selected' : '' ?>>Theo thể tích</option>
                                <option value="other" <?= old('commission_type', $commission['commission_type']) == 'other' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="payment_status">Trạng thái thanh toán <span class="text-danger">*</span></label>
                            <select name="payment_status" id="payment_status" class="form-control" required>
                                <option value="">Chọn trạng thái</option>
                                <option value="pending" <?= old('payment_status', $commission['payment_status']) == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                <option value="approved" <?= old('payment_status', $commission['payment_status']) == 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                                <option value="paid" <?= old('payment_status', $commission['payment_status']) == 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                                <option value="cancelled" <?= old('payment_status', $commission['payment_status']) == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                            <a href="<?= base_url('affiliate-commission') ?>" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>