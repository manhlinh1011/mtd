<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">


            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div>
                <form action="<?= base_url('/invoices') ?>" method="GET" class="mb-3">
                    <div class="row">
                        <!-- Lọc theo Mã Khách Hàng -->
                        <div class="col-md-2">
                            <select name="customer_code" class="form-control">
                                <option value="ALL" <?= $customer_code === 'ALL' || empty($customer_code) ? 'selected' : '' ?>>TẤT CẢ KHÁCH HÀNG</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['customer_code'] ?>" <?= $customer['customer_code'] === $customer_code ? 'selected' : '' ?>>
                                        <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Lọc theo Thời Gian -->
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="<?= esc($from_date ?? '') ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="<?= esc($to_date ?? '') ?>" placeholder="Đến ngày">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="invoice_id" class="form-control" placeholder="Nhập mã phiếu xuất" value="<?= esc($invoice_id ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="tracking_code" class="form-control" placeholder="Nhập mã vận chuyển" value="<?= esc($tracking_code ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
                        </div>
                    </div>
                </form>
                <?php if (empty($invoices)): ?>
                    <div class="alert alert-warning text-center">Không tìm thấy phiếu xuất nào.</div>
                <?php endif; ?>
            </div>

            <h3>Danh sách phiếu xuất</h3>
            <!-- Cập nhật phần bảng và phân trang -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thời Gian Tạo</th>
                        <th>Mã Khách Hàng</th>
                        <th>Tên Khách Hàng</th>
                        <th>Phí Giao Hàng</th>
                        <th>Số Order</th>
                        <th>Số tiền</th>
                        <th>Giao hàng</th>
                        <th>Thanh Toán</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td class="text-center"><?= $invoice['id'] ?></td>
                                <td class="text-center"><?= date('H:i d-m-Y', strtotime($invoice['created_at'])) ?></td>
                                <td class="text-center"><?= $invoice['customer_code'] ?></td>
                                <td class="text-center"><?= $invoice['fullname'] ?></td>
                                <td class="text-center"><?= number_format($invoice['shipping_fee'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= $invoice['order_count'] ?></td>
                                <td class="text-center" style="line-height: 1.2; margin: 0; font-size: 0.9rem;">
                                    <strong><?= number_format($invoice['total_amount'], 0, ',', '.') ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($invoice['shipping_status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Đang xuất</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Đã xuất</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge 
                                        <?php
                                        if ($invoice['payment_status'] === 'paid') {
                                            echo 'bg-success';
                                        } else {
                                            echo 'bg-danger';
                                        }
                                        ?>">
                                        <?php
                                        if ($invoice['payment_status'] === 'paid') {
                                            echo 'Đã thanh toán';
                                        } else {
                                            echo 'Chưa thanh toán';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url("invoices/detail/{$invoice['id']}") ?>" class="btn btn-info btn-sm">Chi Tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">Không có phiếu xuất nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Hiển thị phân trang -->
            <div class="d-flex justify-content-center">
                <?= $pager->links('default', 'bootstrap_pagination') ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>