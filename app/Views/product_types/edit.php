<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>




<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">#update thông tin phân loại hàng</h2>
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url() ?>/product-types/edit/<?= $product_type['id'] ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-group mb-3">
                    <label for="name">Tên Loại Hàng</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= $product_type['name'] ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="description">Mô Tả</label>
                    <textarea name="description" id="description" class="form-control"><?= $product_type['description'] ?></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                    <a href="<?= base_url() ?>/product-types" class="btn btn-secondary">Quay Lại</a>
                </div>
            </form>
        </div>
    </div>
    <?= $this->endSection() ?>