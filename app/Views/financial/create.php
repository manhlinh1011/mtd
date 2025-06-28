<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3>Tạo phiếu thu/chi</h3>
            <div class="alert alert-warning">
                <strong>Lưu ý:</strong> Phiếu chi cần qua phê duyệt của quản lý.
            </div>
            <form method="post" action="<?= base_url('financial/store') ?>">
                <div class="form-group">
                    <label>Loại phiếu</label>
                    <select name="type" class="form-control">
                        <option value="income">Thu</option>
                        <option value="expense">Chi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Chọn quỹ <span class="text-danger">*</span></label>
                    <select name="fund_id" class="form-control" required>
                        <option value="">-- Chọn quỹ --</option>
                        <?php foreach ($funds as $fund): ?>
                            <option value="<?= $fund['id'] ?>">
                                <?= esc($fund['name']) ?>
                                <?php if ($fund['account_number']): ?>
                                    (<?= esc($fund['bank_name']) ?> - <?= esc($fund['account_number']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số tiền</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>Ngày giao dịch</label>
                    <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>