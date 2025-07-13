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
                    <input type="text" name="amount" class="form-control" required>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        if (amountInput) {
            amountInput.addEventListener('input', function(e) {
                // Lấy giá trị chỉ gồm số
                let value = this.value.replace(/[^0-9]/g, '');
                if (value) {
                    this.value = Number(value).toLocaleString('vi-VN');
                } else {
                    this.value = '';
                }
            });

            // Khi submit form, bỏ dấu chấm để gửi số thuần về server
            amountInput.form.addEventListener('submit', function() {
                amountInput.value = amountInput.value.replace(/\./g, '').replace(/,/g, '');
            });
        }
    });
</script>
<?= $this->endSection() ?>