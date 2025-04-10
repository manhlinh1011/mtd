<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">


                    <div class="row">
                        <div class="col-6">
                            <h5 class="card-title mt-2">#<?= $customer['id'] ?> - Thông tin khách hàng</h5>
                        </div>
                        <div class="col-6 text-right">
                            <a class="btn btn-outline-primary" href="<?= base_url('customers/detail/' . $customer['id']) ?>">Chi tiết</a>
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

                    <form action="<?= base_url('customers/edit/' . $customer['id']) ?>" method="post" class="form-horizontal">
                        <?= csrf_field() ?>

                        <!-- Mã khách hàng -->
                        <div class="form-group row">
                            <label for="customer_code" class="col-sm-3 col-form-label text-right">Mã khách hàng</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="customer_code" name="customer_code" value="<?= $customer['customer_code'] ?>" disabled>
                                <input type="hidden" name="customer_code" value="<?= $customer['customer_code'] ?>">
                            </div>
                        </div>

                        <!-- Họ và Tên -->
                        <div class="form-group row">
                            <label for="fullname" class="col-sm-3 col-form-label text-right">Họ và Tên</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?= $customer['fullname'] ?>" required>
                            </div>
                        </div>

                        <!-- Số Điện Thoại -->
                        <div class="form-group row">
                            <label for="phone" class="col-sm-3 col-form-label text-right">Số Điện Thoại</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= $customer['phone'] ?>" required>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div class="form-group row">
                            <label for="address" class="col-sm-3 col-form-label text-right">Địa chỉ</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="address" name="address" rows="3" required><?= $customer['address'] ?></textarea>
                            </div>
                        </div>

                        <!-- Link Zalo -->
                        <div class="form-group row">
                            <label for="zalo_link" class="col-sm-3 col-form-label text-right">Link Zalo</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="zalo_link" name="zalo_link" value="<?= $customer['zalo_link'] ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-form-label text-right">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" id="email" name="email" value="<?= $customer['email'] ?>">
                            </div>
                        </div>

                        <!-- Giá 1 kg -->
                        <div class="form-group row">
                            <label for="price_per_kg" class="col-sm-3 col-form-label text-right">Giá 1 kg (VNĐ)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="price_per_kg" name="price_per_kg" value="<?= number_format($customer['price_per_kg'], 0, ',', '.') ?>" required>
                            </div>
                        </div>

                        <!-- Giá 1 khối -->
                        <div class="form-group row">
                            <label for="price_per_cubic_meter" class="col-sm-3 col-form-label text-right">Giá 1 khối (VNĐ)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="price_per_cubic_meter" name="price_per_cubic_meter" value="<?= number_format($customer['price_per_cubic_meter'], 0, ',', '.') ?>" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="payment_limit_days" class="col-sm-3 col-form-label text-right">Số ngày giới hạn thanh toán</label>
                            <div class="col-sm-9">
                                <input type="number" name="payment_limit_days" class="form-control" value="<?= $customer['payment_limit_days'] ?>" min="1" required>
                            </div>
                        </div>

                        <!-- Nút lưu -->
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <a href="<?= base_url('/customers') ?>" class="btn btn-secondary">Hủy</a>
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
                    <h3 class="mt-1">Số dư: <strong class="text-success"><?= number_format($customer['balance'], 0, ',', '.') ?> VNĐ</strong></h3>
                    <p>Ngày tạo: <strong><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></strong></p>
                    <hr />
                    <div class="row">
                        <!-- Cột trái -->
                        <div class="col-6">
                            <h6>Thông tin đơn hàng</h6>
                            <p>
                                Tổng số đơn hàng: <strong><?= $totalOrders ?></strong><br />
                                Đơn hàng kho Trung Quốc: <strong><?= $chinaStock ?></strong><br />
                                Đơn hàng tồn kho: <strong class="text-danger"><?= $stockOrders ?></strong><br />
                                Đơn hàng chờ giao: <strong class="text-warning"><?= $pendingShipping ?></strong><br />
                                Đơn hàng đã giao: <strong class="text-success"><?= $shippedOrders ?></strong>
                            </p>
                        </div>
                        <!-- Cột phải -->
                        <div class="col-6">
                            <h6>Thông tin phiếu xuất</h6>
                            <p>
                                Tổng phiếu xuất: <strong><?= $totalInvoices ?></strong><br />
                                Phiếu xuất đã thanh toán: <strong><?= $paidInvoices ?></strong><br />
                                Phiếu xuất chưa thanh toán: <strong class="text-danger"><?= $unpaidInvoices ?></strong><br />
                                Phiếu xuất chờ giao: <strong class="text-warning"><?= $pendingInvoices ?></strong><br />
                                Phiếu xuất đã giao: <strong class="text-success"><?= $deliveredInvoices ?></strong>
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript để định dạng số tiền -->
<script>
    // Hàm định dạng số với phần nghìn
    function formatCurrency(input) {
        let value = input.value.replace(/[^0-9]/g, ''); // Lọc chỉ giữ số
        if (value === '') value = '0';
        let formattedValue = Number(value).toLocaleString('vi-VN');
        input.value = formattedValue;
    }

    // Áp dụng cho các input giá
    document.addEventListener('DOMContentLoaded', function() {
        const pricePerKgInput = document.getElementById('price_per_kg');
        const pricePerCubicMeterInput = document.getElementById('price_per_cubic_meter');

        // Định dạng giá trị ban đầu
        if (pricePerKgInput) {
            formatCurrency(pricePerKgInput);
        }
        if (pricePerCubicMeterInput) {
            formatCurrency(pricePerCubicMeterInput);
        }

        // Định dạng khi nhập
        if (pricePerKgInput) {
            pricePerKgInput.addEventListener('input', function() {
                formatCurrency(this);
            });
        }
        if (pricePerCubicMeterInput) {
            pricePerCubicMeterInput.addEventListener('input', function() {
                formatCurrency(this);
            });
        }

        // Khi submit form, chuyển về số nguyên để lưu vào database
        document.querySelector('form').addEventListener('submit', function() {
            if (pricePerKgInput) {
                pricePerKgInput.value = pricePerKgInput.value.replace(/\./g, '');
            }
            if (pricePerCubicMeterInput) {
                pricePerCubicMeterInput.value = pricePerCubicMeterInput.value.replace(/\./g, '');
            }
        });
    });
</script>


<?= $this->endSection() ?>