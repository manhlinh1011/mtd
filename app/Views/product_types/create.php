<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">#thêm mới phân loại hàng</h2>

            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <!-- Hiển thị thông báo thành công nếu có -->
            <?php if (session()->has('success')): ?>
                <div class="alert alert-success">
                    <?= session('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/product-types/create') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-group mb-3">
                    <label for="name">Tên Loại Hàng</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Nhập tên loại hàng" required>
                </div>
                <div class="form-group mb-3">
                    <label for="description">Mô Tả</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Nhập mô tả"></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Thêm Loại Hàng</button>
                    <a href="<?= base_url('/product-types') ?>" class="btn btn-secondary">Quay Lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>