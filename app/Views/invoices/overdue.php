<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <a href="<?= base_url('invoices') ?>" class="btn btn-secondary mb-3">
                <i class="mdi mdi-format-list-bulleted"></i> Tất cả phiếu xuất
            </a>
            <a href="<?= base_url('invoices/pending') ?>" class="btn btn-secondary mb-3">
                <i class="mdi mdi-clock"></i> Phiếu xuất đang chờ giao
            </a>
            <a href="<?= base_url('invoices/overdue') ?>" class="btn btn-danger mb-3">
                <i class="mdi mdi-calendar-alert"></i> Phiếu xuất quá hạn
            </a>
            <h4>Danh sách phiếu xuất quá hạn thanh toán</h4>

            <!-- Bộ lọc -->
            <form action="<?= base_url('invoices/overdue') ?>" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select name="days" class="form-control">
                            <option value="3" <?= isset($days) && $days == 3 ? 'selected' : '' ?>>3 ngày</option>
                            <option value="4" <?= isset($days) && $days == 4 ? 'selected' : '' ?>>4 ngày</option>
                            <option value="5" <?= isset($days) && $days == 5 ? 'selected' : '' ?>>5 ngày</option>
                            <option value="6" <?= isset($days) && $days == 6 ? 'selected' : '' ?>>6 ngày</option>
                            <option value="7" <?= isset($days) && $days == 7 ? 'selected' : '' ?>>7 ngày</option>
                            <option value="10" <?= isset($days) && $days == 10 ? 'selected' : '' ?>>10 ngày</option>
                            <option value="15" <?= isset($days) && $days == 15 ? 'selected' : '' ?>>15 ngày</option>
                            <option value="30" <?= isset($days) && $days == 30 ? 'selected' : '' ?>>30 ngày</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="customer_code" name="customer_code">
                            <option value="ALL" <?= isset($customer_code) && $customer_code === 'ALL' ? 'selected' : '' ?>>Tất cả khách hàng</option>
                            <?php if (isset($customers) && is_array($customers)): ?>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= esc($customer['customer_code'] ?? 'N/A') ?>" <?= isset($customer_code) && $customer_code === ($customer['customer_code'] ?? 'N/A') ? 'selected' : '' ?>>
                                        <?= esc($customer['customer_code'] ?? 'N/A') ?> - <?= esc($customer['fullname'] ?? 'N/A') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-body" style="background-color:rgb(211, 211, 211)">
                    <ul class="list-group list-group-flush" style="border-radius: 5px;">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="mdi mdi-account-group mr-2 text-warning"></i>Số khách hàng quá hạn thanh toán</strong>
                            <span class="badge badge-pill badge-warning py-1 px-2"><?= esc($overdue_customers_count) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="mdi mdi-file-document mr-2 text-danger"></i>Số phiếu xuất quá hạn thanh toán</strong>
                            <span class="badge badge-pill badge-danger py-1 px-2"><?= esc($overdue_invoices_count) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="mdi mdi-currency-usd mr-2 text-success"></i>Tổng tiền quá hạn thanh toán</strong>
                            <span class="font-weight-bold text-danger" style="font-size: 20px;"><?= number_format($total_overdue_amount, 0, ',', '.') ?> đ</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong><i class="mdi mdi-clock-alert mr-2 text-info"></i>Ngày quá hạn lâu nhất</strong>
                            <span class="badge badge-pill badge-secondary py-1 px-2"><?= esc($max_days_overdue) ?> ngày</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Chú ý</h5>
                    <p class="card-text">Với những khách hàng có phiếu xuất quá hạn thanh toan 15 ngày, không được phép xuất hàng.</p>
                </div>
            </div>
        </div>
        <div class="col-12">

            <?php if (!isset($invoices) || empty($invoices)): ?>
                <div class="alert alert-info">Không có phiếu xuất nào quá hạn thanh toán.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Ngày xuất</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Số ngày quá hạn</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td class="text-center">#<?= esc($invoice['id'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($invoice['created_at'] ?? 'now')) ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url("customers/detail/" . esc($invoice['customer_id'] ?? 0)) ?>">
                                            <?= esc($invoice['customer_code'] ?? 'N/A') ?> - <?= esc($invoice['customer_name'] ?? 'N/A') ?>
                                        </a>
                                    </td>
                                    <td class="text-center"><?= number_format(floatval($invoice['total_amount'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">
                                            <?= intval($invoice['days_overdue'] ?? 0) ?> ngày
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">Chưa thanh toán</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url("invoices/detail/" . esc($invoice['id'] ?? 0)) ?>"
                                            class="btn btn-info btn-sm">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if (isset($pager)): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Hiển thị <?= count($invoices) ?> / <?= $total ?> phiếu xuất quá hạn (trong tổng số <?= $totalInvoices ?> phiếu xuất chưa thanh toán)
                        </div>
                        <div>
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>