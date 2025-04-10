<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Danh sách giao dịch<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="card-title mb-0">Danh sách giao dịch</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <span class="badge bg-success p-2">Tổng nạp: <?= number_format($totalDeposit, 0, ',', '.') ?> đ</span> &nbsp; &nbsp;
                                <span class="badge bg-info p-2">Tổng thanh toán: <?= number_format($totalPayment, 0, ',', '.') ?> đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form tìm kiếm -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= base_url('transactions') ?>" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Mã khách hàng</label>
                            <input type="text" class="form-control" name="customer_code" value="<?= $customerCode ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $startDate ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $endDate ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Loại giao dịch</label>
                            <select class="form-control" name="transaction_type">
                                <option value="">Tất cả</option>
                                <option value="deposit" <?= ($transactionType == 'deposit') ? 'selected' : '' ?>>Nạp tiền</option>
                                <option value="payment" <?= ($transactionType == 'payment') ? 'selected' : '' ?>>Thanh toán</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Bảng danh sách giao dịch -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Thời gian</th>
                                    <th class="text-center">Mã KH</th>
                                    <th class="text-center">Tên khách hàng</th>
                                    <th class="text-center">Loại GD</th>
                                    <th class="text-center">Số tiền</th>
                                    <th class="text-center">Phiếu xuất</th>
                                    <th class="text-center">Người tạo</th>
                                    <th class="text-center">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td class="text-center"><?= $transaction['id'] ?></td>
                                            <td class="text-center"><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('customers/detail/' . $transaction['customer_id']) ?>" class="text-decoration-none">
                                                    <?= esc($transaction['customer_code']) ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('customers/detail/' . $transaction['customer_id']) ?>" class="text-decoration-none">
                                                    <?= esc($transaction['customer_name']) ?>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($transaction['transaction_type'] == 'deposit'): ?>
                                                    <span class="badge bg-success">Nạp tiền</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Thanh toán</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($transaction['amount'], 0, ',', '.') ?> đ
                                            </td>
                                            <td class="text-center">
                                                <?php if ($transaction['invoice_id']): ?>
                                                    <a href="<?= base_url('invoices/detail/' . $transaction['invoice_id']) ?>" class="btn btn-sm btn-outline-primary">
                                                        #<?= $transaction['invoice_id'] ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= esc($transaction['created_by_name']) ?></td>
                                            <td class="text-center"><?= esc($transaction['notes']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="row">
                        <div class="col-md-6">
                            <p>Hiển thị <?= count($transactions) ?> / <?= $total ?> giao dịch</p>
                        </div>
                        <div class="col-md-6">
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>