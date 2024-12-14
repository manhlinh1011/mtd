<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h1 class="my-4">Danh sách Khách hàng</h1>
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
                            <form action="/customers/update-bulk" method="POST">
                                <?= csrf_field() ?>
                                <a href="/customers/create" class="btn btn-primary mb-3">Thêm Khách hàng</a>
                                <button type="submit" class="btn btn-success mb-3">Cập Nhật Hàng Loạt</button>
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Mã khách hàng</th>
                                            <th>Họ và tên</th>
                                            <th>Giá 1kg</th>
                                            <th>Giá 1 mét khối</th>
                                            <th>Số điện thoại</th>
                                            <th>Địa chỉ</th>
                                            <th>Link Zalo</th>
                                            <th>Email</th>

                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><?= $customer['id'] ?></td>
                                                <td><?= $customer['customer_code'] ?></td>
                                                <td><?= $customer['fullname'] ?></td>
                                                <td style="text-align: center;">
                                                    <input type="text" style="width: 60px; text-align: center;"
                                                        name="customers[<?= $customer['id'] ?>][price_per_kg]"
                                                        class="price-input"
                                                        value="<?= number_format($customer['price_per_kg'], 0, ',', '.') ?>"
                                                        data-raw="<?= $customer['price_per_kg'] ?>"
                                                        placeholder="Nhập giá 1kg">
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="text" style="width: 80px; text-align: center;"
                                                        name="customers[<?= $customer['id'] ?>][price_per_cubic_meter]"
                                                        class="price-input"
                                                        value="<?= number_format($customer['price_per_cubic_meter'], 0, ',', '.') ?>"
                                                        data-raw="<?= $customer['price_per_cubic_meter'] ?>"
                                                        placeholder="Nhập giá 1 mét khối">
                                                </td>
                                                <td><?= $customer['phone'] ?></td>
                                                <td><?= $customer['address'] ?></td>
                                                <td>
                                                    <?php if (!empty($customer['zalo_link'])): ?>
                                                        <a href="<?= $customer['zalo_link'] ?>" target="_blank">Zalo</a>
                                                    <?php else: ?>
                                                        Không có
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $customer['email'] ?></td>

                                                <td class="text-center" style="padding: 2px;">
                                                    <a href="/customers/edit/<?= $customer['id'] ?>" class="btn btn-warning btn-sm" style="padding: 2px 8px;">Sửa</a>
                                                    <a href="/customers/delete/<?= $customer['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')" style="padding: 2px 8px;">Xóa</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Khi click vào input
    document.querySelectorAll('.price-input').forEach(input => {
        input.addEventListener('focus', function() {
            const value = this.getAttribute('data-raw');
            if (value == "0") {
                this.select(); // Bôi đen toàn bộ
            } else {
                this.value = value; // Bỏ dấu . khi có dữ liệu
            }
        });

        // Khi rời khỏi input
        input.addEventListener('blur', function() {
            let value = this.value.replace(/\D/g, ''); // Chỉ giữ lại số
            value = value ? parseInt(value) : 0;
            this.value = value.toLocaleString('vi-VN'); // Định dạng lại số
            this.setAttribute('data-raw', value); // Cập nhật giá trị raw
        });
    });

    // Trước khi gửi form
    document.querySelector('form').addEventListener('submit', function(e) {
        document.querySelectorAll('.price-input').forEach(input => {
            // Loại bỏ dấu . ngăn cách trước khi gửi
            input.value = input.value.replace(/\./g, '');
        });
    });
</script>

<?= $this->endSection() ?>