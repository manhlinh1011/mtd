<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let errors = [];

        // Kiểm tra Khách hàng chính
        let customerId = document.getElementById('customer_id').value;
        if (!customerId) {
            errors.push("Vui lòng chọn khách hàng chính.");
        }

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

        // Kiểm tra Mã Phụ
        let subCustomerCode = document.getElementById('sub_customer_code').value;
        if (!/^[\p{L}0-9\- ]+$/u.test(subCustomerCode)) {
            errors.push("Mã Phụ chỉ được chứa chữ cái, số và dấu gạch ngang.");
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
        <div class="col-6">


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

            <!-- Hiển thị lỗi validation nếu có -->
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <?php foreach (session('errors') as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">
                    <h5>Thêm mới mã phụ</h5>
                </div>
                <div class="card-body">
                    <form action="/customers/sub-customers/store" method="POST">
                        <!-- CSRF Protection -->
                        <?= csrf_field() ?>

                        <div class="form-group row mb-3">
                            <label for="customer_id" class="col-sm-3 col-form-label text-right">Khách hàng chính</label>
                            <div class="col-sm-9">
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" <?= old('customer_id') == $customer['id'] ? 'selected' : '' ?>>
                                            <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="sub_customer_code" class="col-sm-3 col-form-label text-right">Mã Phụ</label>
                            <div class="col-sm-9">
                                <input type="text" name="sub_customer_code" id="sub_customer_code" class="form-control" placeholder="Nhập mã phụ" value="<?= old('sub_customer_code') ?>" required>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="fullname" class="col-sm-3 col-form-label text-right">Họ và Tên</label>
                            <div class="col-sm-9">
                                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Nhập họ và tên" value="<?= old('fullname') ?>" required>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="phone" class="col-sm-3 col-form-label text-right">Số Điện Thoại</label>
                            <div class="col-sm-9">
                                <input type="text" name="phone" id="phone" class="form-control" placeholder="Nhập số điện thoại" value="<?= old('phone') ?>" required>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="address" class="col-sm-3 col-form-label text-right">Địa Chỉ</label>
                            <div class="col-sm-9">
                                <input type="text" name="address" id="address" class="form-control" placeholder="Nhập địa chỉ" value="<?= old('address') ?>" required>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="zalo_link" class="col-sm-3 col-form-label text-right">Link Zalo</label>
                            <div class="col-sm-9">
                                <input type="text" name="zalo_link" id="zalo_link" class="form-control" placeholder="Nhập link Zalo (nếu có)" value="<?= old('zalo_link') ?>">
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="email" class="col-sm-3 col-form-label text-right">Email</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" id="email" class="form-control" placeholder="Nhập email (nếu có)" value="<?= old('email') ?>">
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">Thêm Mã Phụ</button>
                                <a href="/customers/sub-customers" class="btn btn-secondary">Quay Lại</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5>Lưu ý</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Mã phụ được quản lý bởi khách hàng chính.</li>
                        <li>Mã phụ sẽ được hiển thị trên phiếu xuất hàng và phiếu xuất kho.</li>
                        <li>Không được thay đổi mã khách hàng chính. Do vậy cần lưu ý khi nhập mã phụ.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>