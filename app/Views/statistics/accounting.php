<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <form action="<?= base_url('/accounting-statistics') ?>" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="customer_code" class="form-control" placeholder="Nhập mã khách hàng" value="<?= $filters['customer_code'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?? '' ?>" placeholder="Từ ngày">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?? '' ?>" placeholder="Đến ngày">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
                    </div>
                </div>
            </form>
            <h3 class="mb-4">Thống Kê Công Nợ Khách Hàng</h3>
            <?php
            $totalDebt = 0;
            $totalBalance = 0;
            $totalCustomer = 0;
            $totalInvoices = 0;
            $totalPaid = 0;
            foreach ($debtSummary as $summary) {
                $totalDebt += $summary['debt'];
                $totalBalance += $summary['balance'];
                $totalCustomer++;
                $totalInvoices += $summary['total_invoices'];
                $totalPaid += $summary['total_paid'];
            }
            ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Mã Khách Hàng
                            <br>
                            <span class="badge bg-info px-2 py-1"><?= number_format($totalCustomer, 0, ',', '.') ?></span>
                        </th>
                        <th>Tên Khách Hàng
                            <br>
                            <span class="badge bg-info px-2 py-1"><?= number_format($totalCustomer, 0, ',', '.') ?></span>
                        </th>
                        <th>Số Dư Tài Khoản
                            <br>
                            <span class="badge bg-warning px-2 py-1"><?= number_format($totalBalance, 0, ',', '.') ?></span>
                        </th>
                        <th>Tổng Tiền Hóa Đơn
                            <br>
                            <span class="badge bg-info px-2 py-1"><?= number_format($totalInvoices, 0, ',', '.') ?></span>
                        </th>
                        <th>Tổng Tiền Đã Thanh Toán
                            <br>
                            <span class="badge bg-success px-2 py-1"><?= number_format($totalPaid, 0, ',', '.') ?></span>
                        </th>
                        <th>Số Tiền Còn Nợ
                            <br>
                            <span class="badge bg-danger px-2 py-1"><?= number_format($totalDebt, 0, ',', '.') ?></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($debtSummary)): ?>
                        <?php foreach ($debtSummary as $summary): ?>
                            <tr>
                                <td class="text-center"><a href="<?= base_url() ?>customers/detail/<?= $summary['id'] ?>"><?= $summary['customer_code'] ?></a></td>
                                <td class="text-center"><?= $summary['fullname'] ?></td>
                                <td class="text-center"><?= number_format($summary['balance'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($summary['total_invoices'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($summary['total_paid'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($summary['debt'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan=" 6" class="text-center">Không có dữ liệu công nợ.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>