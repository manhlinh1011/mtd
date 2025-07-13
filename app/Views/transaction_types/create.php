<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('transaction-types') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <ul class="mb-0">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('transaction-types') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tên loại giao dịch <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                                        id="name" name="name" value="<?= old('name') ?>"
                                        placeholder="Nhập tên loại giao dịch" required>
                                    <?php if (session('errors.name')): ?>
                                        <div class="invalid-feedback"><?= session('errors.name') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Phân loại <span class="text-danger">*</span></label>
                                    <select class="form-control <?= session('errors.category') ? 'is-invalid' : '' ?>"
                                        id="category" name="category" required>
                                        <option value="">Chọn phân loại</option>
                                        <?php foreach ($categories as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= old('category') == $key ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (session('errors.category')): ?>
                                        <div class="invalid-feedback"><?= session('errors.category') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                                        id="description" name="description" rows="3"
                                        placeholder="Nhập mô tả chi tiết về loại giao dịch này"><?= old('description') ?></textarea>
                                    <?php if (session('errors.description')): ?>
                                        <div class="invalid-feedback"><?= session('errors.description') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_order">Thứ tự hiển thị</label>
                                    <input type="number" class="form-control <?= session('errors.sort_order') ? 'is-invalid' : '' ?>"
                                        id="sort_order" name="sort_order" value="<?= old('sort_order', 0) ?>"
                                        placeholder="0" min="0">
                                    <small class="form-text text-muted">Số càng nhỏ hiển thị càng trước</small>
                                    <?php if (session('errors.sort_order')): ?>
                                        <div class="invalid-feedback"><?= session('errors.sort_order') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu loại giao dịch
                            </button>
                            <a href="<?= base_url('transaction-types') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
<?= $this->endSection() ?>