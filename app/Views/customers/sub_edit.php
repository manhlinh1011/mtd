<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="card-title mt-2">#<?= $subCustomer['id'] ?> - Thông tin mã phụ</h5>
                        </div>
                        <div class="col-6 text-right">
                            <a class="btn btn-outline-primary" href="<?= base_url('customers/sub-customers') ?>">Danh sách mã phụ</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success">
                            <?= session('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('errors')): ?>
                        <div class="alert alert-danger">
                            <?php foreach (session('errors') as $error): ?>
                                <p><?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('customers/edit-sub/' . $subCustomer['id']) ?>" method="post" class="form-horizontal">
                        <?= csrf_field() ?>

                        <!-- Mã phụ -->
                        <div class="form-group row">
                            <label for="sub_customer_code" class="col-sm-3 col-form-label text-right">Mã phụ</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="sub_customer_code" name="sub_customer_code" value="<?= $subCustomer['sub_customer_code'] ?>" required>
                            </div>
                        </div>

                        <!-- Họ và Tên -->
                        <div class="form-group row">
                            <label for="fullname" class="col-sm-3 col-form-label text-right">Họ và Tên</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?= $subCustomer['fullname'] ?>" required>
                            </div>
                        </div>

                        <!-- Số Điện Thoại -->
                        <div class="form-group row">
                            <label for="phone" class="col-sm-3 col-form-label text-right">Số Điện Thoại</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= $subCustomer['phone'] ?>" required>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div class="form-group row">
                            <label for="address" class="col-sm-3 col-form-label text-right">Địa chỉ</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="address" name="address" rows="3" required><?= $subCustomer['address'] ?></textarea>
                            </div>
                        </div>

                        <!-- Link Zalo -->
                        <div class="form-group row">
                            <label for="zalo_link" class="col-sm-3 col-form-label text-right">Link Zalo</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="zalo_link" name="zalo_link" value="<?= $subCustomer['zalo_link'] ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-form-label text-right">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" id="email" name="email" value="<?= $subCustomer['email'] ?>">
                            </div>
                        </div>

                        <!-- Nút lưu -->
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <a href="<?= base_url('/customers/sub-customers') ?>" class="btn btn-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mt-2">Thông tin thêm</h5>
                </div>
                <div class="card-body">
                    <h3 class="mt-1">Khách hàng chính: <strong class="text-success"><?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)</strong></h3>
                    <p>Ngày tạo: <strong><?= date('d/m/Y H:i', strtotime($subCustomer['created_at'])) ?></strong></p>
                    <hr />
                    <div class="row">
                        <!-- Cột trái -->
                        <div class="col-6">
                            <h6>Thông tin đơn hàng</h6>
                            <p>
                                Tổng số đơn hàng: <strong><?= $totalOrders ?></strong>
                            </p>
                        </div>
                        <!-- Cột phải -->
                        <div class="col-6">
                            <h6>Thông tin phiếu xuất</h6>
                            <p>
                                Tổng phiếu xuất: <strong><?= $totalInvoices ?></strong><br />
                                Phiếu xuất đã thanh toán: <strong><?= $paidInvoices ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>