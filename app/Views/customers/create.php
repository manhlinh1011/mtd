<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let errors = [];

        // Kiểm tra Họ và Tên
        let fullname = document.getElementById('fullname').value;
        if (fullname.length < 3 || fullname.length > 100) {
            errors.push("Họ và Tên phải có từ 3 đến 100 ký tự.");
        }

        // Kiểm tra Số Điện Thoại
        let phone = document.getElementById('phone').value;
        if (!/^[0-9]{10,15}$/.test(phone)) {
            errors.push("Số Điện Thoại phải là số và có độ dài từ 10 đến 15 ký tự.");
        }

        // Kiểm tra Địa Chỉ
        let address = document.getElementById('address').value;
        if (address.length < 5 || address.length > 255) {
            errors.push("Địa chỉ phải có từ 5 đến 255 ký tự.");
        }

        // Kiểm tra Mã Khách Hàng
        let customerCode = document.getElementById('customer_code').value;
        if (!/^[\p{L}0-9\- ]+$/u.test(customerCode)) {
            errors.push("Mã Khách Hàng chỉ được chứa chữ cái, số và dấu gạch ngang.");
        }

        // Kiểm tra Giá 1kg
        let pricePerKg = document.getElementById('price_per_kg').value;
        if (isNaN(pricePerKg) || pricePerKg < 0) {
            errors.push("Giá cho 1kg phải là số không âm.");
        }

        // Kiểm tra Giá 1 mét khối
        let pricePerCubicMeter = document.getElementById('price_per_cubic_meter').value;
        if (isNaN(pricePerCubicMeter) || pricePerCubicMeter < 0) {
            errors.push("Giá cho 1 mét khối phải là số không âm.");
        }

        // Hiển thị lỗi nếu có
        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Thêm mới khách hàng</h3>

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

            <form action="/customers/create" method="POST">
                <!-- CSRF Protection -->
                <?= csrf_field() ?>

                <div class="form-group mb-3">
                    <label for="customer_code">Mã Khách Hàng</label>
                    <input type="text" name="customer_code" id="customer_code" class="form-control" placeholder="Nhập mã khách hàng" required>
                </div>

                <div class="form-group mb-3">
                    <label for="fullname">Họ và Tên</label>
                    <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Nhập họ và tên" required>
                </div>

                <div class="form-group mb-3">
                    <label for="phone">Số Điện Thoại</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Nhập số điện thoại" required>
                </div>

                <div class="form-group mb-3">
                    <label for="address">Địa Chỉ</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="Nhập địa chỉ" required>
                </div>

                <div class="form-group mb-3">
                    <label for="zalo_link">Link Zalo</label>
                    <input type="text" name="zalo_link" id="zalo_link" class="form-control" placeholder="Nhập link Zalo (nếu có)">
                </div>

                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Nhập email (nếu có)">
                </div>

                <div class="form-group mb-3">
                    <label for="price_per_kg">Giá cho 1kg</label>
                    <input type="number" name="price_per_kg" id="price_per_kg" class="form-control" placeholder="Nhập giá cho 1kg" required>
                </div>

                <div class="form-group mb-3">
                    <label for="price_per_cubic_meter">Giá cho 1 mét khối</label>
                    <input type="number" name="price_per_cubic_meter" id="price_per_cubic_meter" class="form-control" placeholder="Nhập giá cho 1 mét khối" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Thêm Khách Hàng</button>
                    <a href="/customers" class="btn btn-secondary">Quay Lại</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>