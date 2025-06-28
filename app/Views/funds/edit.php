<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Chỉnh sửa quỹ</h1>
        <a href="<?= base_url('funds') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= base_url('funds/update/' . $fund['id']) ?>" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên quỹ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= $fund['name'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="account_number" class="form-label">Số tài khoản</label>
                    <input type="text" class="form-control" id="account_number" name="account_number" value="<?= $fund['account_number'] ?>">
                </div>

                <div class="mb-3">
                    <label for="bank_name" class="form-label">Tên ngân hàng</label>
                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= $fund['bank_name'] ?>">
                </div>

                <div class="mb-3">
                    <label for="account_holder" class="form-label">Chủ tài khoản</label>
                    <input type="text" class="form-control" id="account_holder" name="account_holder" value="<?= $fund['account_holder'] ?>">
                </div>

                <div class="mb-3">
                    <label for="payment_qr" class="form-label">QR thanh toán</label>
                    <input type="text" class="form-control" id="payment_qr" name="payment_qr" value="<?= $fund['payment_qr'] ?>">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>