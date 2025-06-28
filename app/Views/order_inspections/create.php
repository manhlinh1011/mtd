<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('order-inspections') ?>">Kiểm tra đơn hàng</a></li>
                        <li class="breadcrumb-item active">Thêm yêu cầu kiểm tra</li>
                    </ol>
                </div>
                <h4 class="page-title">Thêm yêu cầu kiểm tra đơn hàng</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= base_url('order-inspections/store') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tracking_code">Mã vận chuyển <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control <?= session('errors.tracking_code') ? 'is-invalid' : '' ?>"
                                        id="tracking_code"
                                        name="tracking_code"
                                        value="<?= old('tracking_code') ?>"
                                        placeholder="Nhập mã vận chuyển cần kiểm tra"
                                        required>
                                    <?php if (session('errors.tracking_code')): ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.tracking_code') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Ghi chú <small class="text-muted">(không bắt buộc)</small></label>
                                    <textarea class="form-control <?= session('errors.notes') ? 'is-invalid' : '' ?>"
                                        id="notes"
                                        name="notes"
                                        rows="4"
                                        placeholder="Mô tả chi tiết về việc kiểm tra cần thực hiện... (có thể để trống)"><?= old('notes') ?></textarea>
                                    <?php if (session('errors.notes')): ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.notes') ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">
                                        Ví dụ: Kiểm tra hàng hư hỏng, kiểm tra số lượng, kiểm tra chất lượng... (có thể để trống)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Lưu yêu cầu kiểm tra
                                    </button>
                                    <a href="<?= base_url('order-inspections') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Focus vào ô tracking code khi trang load
        document.getElementById('tracking_code').focus();

        // Auto uppercase cho tracking code
        document.getElementById('tracking_code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>

<?= $this->endSection() ?>