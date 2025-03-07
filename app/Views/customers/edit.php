<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

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

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Update thông tin khách hàng</h3>

            <form action="<?= base_url('/customers/edit/') ?><?= $customer['id'] ?>" method="POST">
                <!-- CSRF Protection -->
                <?= csrf_field() ?>

                <div class="form-group mb-3">
                    <label for="customer_code">Mã Khách Hàng</label>
                    <input type="text" name="customer_code" id="customer_code" class="form-control" value="<?= $customer['customer_code'] ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="fullname">Họ và Tên</label>
                    <input type="text" name="fullname" id="fullname" class="form-control" value="<?= $customer['fullname'] ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="phone">Số Điện Thoại</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= $customer['phone'] ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="address">Địa Chỉ</label>
                    <input type="text" name="address" id="address" class="form-control" value="<?= $customer['address'] ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="zalo_link">Link Zalo</label>
                    <input type="text" name="zalo_link" id="zalo_link" class="form-control" value="<?= $customer['zalo_link'] ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= $customer['email'] ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="price_per_kg">Giá cho 1kg</label>
                    <input type="number" name="price_per_kg" id="price_per_kg" class="form-control" value="<?= $customer['price_per_kg'] ?>" placeholder="Nhập giá cho 1kg" required>
                </div>

                <div class="form-group mb-3">
                    <label for="price_per_cubic_meter">Giá cho 1 mét khối</label>
                    <input type="number" name="price_per_cubic_meter" id="price_per_cubic_meter" class="form-control" value="<?= $customer['price_per_cubic_meter'] ?>" placeholder="Nhập giá cho 1 mét khối" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                    <a href="/customers" class="btn btn-secondary">Quay Lại</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>