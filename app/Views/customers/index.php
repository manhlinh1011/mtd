<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="my-4">Danh sách Khách hàng</h5>
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
                                <?php if (in_array(session('role'), ['Quản lý'])): ?>
                                    <a href="/customers/create" class="btn btn-primary mb-3"><i class="mdi mdi-account-plus"></i> Thêm Khách hàng</a>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-success mb-3"><i class="mdi mdi-sync"></i> Cập Nhật Hàng Loạt</button>
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Mã khách hàng</th>
                                            <th>Họ và tên</th>
                                            <th>Số dư</th>
                                            <th>Giá kg</th>
                                            <th>Giá khối</th>
                                            <th>Số điện thoại</th>
                                            <th>Địa chỉ</th>
                                            <th>Link Zalo</th>
                                            <th>Email</th>
                                            <th>Số đơn hàng</th>
                                            <th>Phiếu xuất</th>
                                            <th>Số ngày giới hạn thanh toán</th>
                                            <?php if (in_array(session('role'), ['Quản lý'])): ?>
                                                <th>Hành động</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td class="text-center"><?= $customer['id'] ?></td>
                                                <td class="text-center"><a href="<?= base_url() ?>customers/detail/<?= $customer['id'] ?>"> <?= $customer['customer_code'] ?></a></td>
                                                <td class="text-center"><?= $customer['fullname'] ?></td>
                                                <td class="text-center"><?= number_format($customer['dynamic_balance'], 0, ',', '.') ?></td>
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
                                                <td class="text-center"><?= $customer['phone'] ?></td>
                                                <td><?= $customer['address'] ?></td>
                                                <td class="text-center">
                                                    <?php if (!empty($customer['zalo_link'])): ?>
                                                        <a href="<?= $customer['zalo_link'] ?>" target="_blank">Zalo</a>
                                                    <?php else: ?>
                                                        Không có
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $customer['email'] ?></td>
                                                <td class="text-center"><a href="<?= base_url() ?>orders?customer_code=<?= $customer['customer_code'] ?>"><?= $customer['order_count'] ?></a></td>
                                                <td class="text-center"><?= $customer['paid_invoice_count'] ?>/<?= $customer['invoice_count'] ?></td>
                                                <td class="text-center"><?= $customer['payment_limit_days'] ?></td>
                                                <?php if (in_array(session('role'), ['Quản lý'])): ?>
                                                    <td class="text-center" style="padding: 2px;">
                                                        <a href="/customers/edit/<?= $customer['id'] ?>" class="btn btn-warning btn-sm" style="padding: 2px 8px;"><i class="mdi mdi-pencil"></i></a>

                                                        <a href="/customers/delete/<?= $customer['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')" style="padding: 2px 8px;"><i class="mdi mdi-trash-can"></i></a>

                                                    </td>
                                                <?php endif; ?>
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




<?= $this->section('scripts') ?>
<!-- Scripts -->
<script src="<?= base_url('') ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url('') ?>assets/js/pages/dashboard.init.js"></script>
<script src="<?= base_url('') ?>assets/js/app.min.js"></script>
<!-- Required datatable js -->
<script src="<?= base_url('') ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url('') ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        $("#datatable").DataTable({
            pageLength: 15,
            order: [
                [0, 'desc']
            ],
            lengthMenu: [
                [10, 15, 20, 50, 100],
                [10, 15, 20, 50, 100]
            ],
            columnDefs: [{
                    targets: [4, 5, 6, 7, 8, 9, 12], // Giá kg, Giá khối, SĐT, Địa chỉ, Zalo, Email, Hành động
                    orderable: false
                },
                {
                    targets: 0, // Cột ID
                    type: 'num',
                    render: function(data, type) {
                        if (type === 'sort') {
                            return parseInt(data.replace(/[^0-9]/g, '')) || 0;
                        }
                        return data;
                    }
                },
                {
                    targets: 10, // Cột Số đơn hàng
                    type: 'num',
                    render: function(data, type) {
                        return type === 'sort' ? parseInt(data) || 0 : data;
                    }
                },
                {
                    targets: 3, // Cột Số dư
                    type: 'num',
                    render: function(data, type) {
                        if (type === 'sort') {
                            return parseInt(data.replace(/\./g, '')) || 0;
                        }
                        return data;
                    }
                },
                {
                    targets: 11, // Cột Phiếu xuất
                    type: 'num',
                    render: function(data, type) {
                        if (type === 'sort') {
                            // Sắp xếp dựa trên tổng số phiếu xuất (phần sau dấu /)
                            const parts = data.split('/');
                            return parseInt(parts[1]) || 0;
                        }
                        return data;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            }
        });
    });
</script>

<?= $this->endSection() ?>