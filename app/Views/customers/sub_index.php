<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="my-4">Danh sách Mã Phụ Khách Hàng</h5>
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
                            <?php if (in_array(session('role'), ['Quản lý'])): ?>
                                <a href="<?= base_url('/customers/sub-customers/create') ?>" class="btn btn-success">Thêm mã phụ</a>
                            <?php endif; ?>
                            <!-- Bộ lọc -->
                            <form method="get" class="mt-3">
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <select name="customer_id" class="form-control">
                                            <option value="">Tất cả</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?= $customer['id'] ?>" <?= $selectedCustomerId == $customer['id'] ? 'selected' : '' ?>>
                                                    <?= $customer['fullname'] ?> (<?= $customer['customer_code'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                                    </div>
                                </div>
                            </form>

                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Mã phụ</th>
                                        <th>Họ và tên</th>
                                        <th>Khách hàng chính</th>
                                        <th>Số điện thoại</th>
                                        <th>Địa chỉ</th>
                                        <th>Số đơn hàng</th>
                                        <th>Phiếu xuất</th>
                                        <?php if (in_array(session('role'), ['Quản lý'])): ?>
                                            <th style="width: 110px;">Hành động</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subCustomers as $subCustomer): ?>
                                        <tr>
                                            <td class="text-center"><?= $subCustomer['id'] ?></td>
                                            <td class="text-center"><?= $subCustomer['sub_customer_code'] ?></td>
                                            <td class="text-center"><?= $subCustomer['fullname'] ?></td>
                                            <td class="text-center"><?= $subCustomer['customer_code'] ?> (<?= $subCustomer['customer_fullname'] ?>)</td>
                                            <td class="text-center"><?= $subCustomer['phone'] ?></td>
                                            <td><?= $subCustomer['address'] ?></td>
                                            <td class="text-center"><?= $subCustomer['order_count'] ?></td>
                                            <td class="text-center"><?= $subCustomer['paid_invoice_count'] ?>/<?= $subCustomer['invoice_count'] ?></td>

                                            <?php if (in_array(session('role'), ['Quản lý'])): ?>
                                                <td class="text-center" style="padding: 2px;">
                                                    <a href="/customers/sub-detail/<?= $subCustomer['id'] ?>" class="btn btn-info btn-sm" style="padding: 2px 8px;"><i class="mdi mdi-eye"></i></a>
                                                    <a href="/customers/edit-sub/<?= $subCustomer['id'] ?>" class="btn btn-warning btn-sm" style="padding: 2px 8px;"><i class="mdi mdi-pencil"></i></a>
                                                    <a href="/customers/delete-sub/<?= $subCustomer['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')" style="padding: 2px 8px;"><i class="mdi mdi-trash-can"></i></a>
                                                </td>
                                            <?php else: ?>
                                                <td class="text-center" style="padding: 2px;">
                                                    <a href="/customers/sub-detail/<?= $subCustomer['id'] ?>" class="btn btn-info btn-sm" style="padding: 2px 8px;"><i class="mdi mdi-eye"></i></a>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('') ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url('') ?>assets/js/pages/dashboard.init.js"></script>
<script src="<?= base_url('') ?>assets/js/app.min.js"></script>
<script src="<?= base_url('') ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url('') ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        $("#datatable").DataTable({
            pageLength: 20,
            order: [
                [0, 'desc']
            ],
            lengthMenu: [
                [10, 15, 20, 50, 100],
                [10, 15, 20, 50, 100]
            ],
            columnDefs: [{
                    targets: [4, 5, 6, 7, 8], // SĐT, Địa chỉ, Số đơn hàng, Phiếu xuất, Hành động (nếu có)
                    orderable: false
                },
                {
                    targets: 0,
                    type: 'num',
                    render: function(data, type) {
                        return type === 'sort' ? parseInt(data) || 0 : data;
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