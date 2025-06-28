<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Chi tiết quỹ</h1>
        <div>
            <a href="<?= base_url('funds') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="<?= base_url('funds/edit/' . $fund['id']) ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <?php if (session()->has('message')): ?>
        <div class="alert alert-success">
            <?= session('message') ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin quỹ</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Tên quỹ:</th>
                            <td><?= $fund['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Số tài khoản:</th>
                            <td><?= $fund['account_number'] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Ngân hàng:</th>
                            <td><?= $fund['bank_name'] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Chủ tài khoản:</th>
                            <td><?= $fund['account_holder'] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th>Số dư hiện tại:</th>
                            <td class="fw-bold <?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($balance, 0, ',', '.') ?> VNĐ
                            </td>
                        </tr>
                        <tr>
                            <th>QR thanh toán:</th>
                            <td><?= $fund['payment_qr'] ?? '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thống kê giao dịch</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted">Tổng thu</h6>
                                <h4 class="text-success mb-0">
                                    <?= number_format($totalIncome ?? 0, 0, ',', '.') ?> VNĐ
                                </h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h6 class="text-muted">Tổng chi</h6>
                                <h4 class="text-danger mb-0">
                                    <?= number_format($totalExpense ?? 0, 0, ',', '.') ?> VNĐ
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Giao dịch gần nhất</h5>
            <a href="<?= base_url('funds/transactions/' . $fund['id']) ?>" class="btn btn-primary btn-sm">
                Xem tất cả
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Số tiền</th>
                            <th>Mô tả</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($financialTransactions, 0, 20) as $transaction): ?>
                            <tr>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $transaction['type'] === 'income' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $transaction['type'] === 'income' ? 'Thu' : 'Chi' ?>
                                    </span>
                                </td>
                                <td class="text-center <?= $transaction['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($transaction['amount'], 0, ',', '.') ?> VNĐ
                                </td>
                                <td><?= $transaction['description'] ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $transaction['status'] === 'approved' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $transaction['status'] === 'approved' ? 'Đã duyệt' : 'Chờ duyệt' ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $transaction['created_by'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Giao dịch gần nhất</h5>
            <a href="<?= base_url('funds/customer-transactions/' . $fund['id']) ?>" class="btn btn-primary btn-sm">
                Xem tất cả
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">Ngày</th>
                            <th class="text-center">Loại</th>
                            <th class="text-center">Số tiền</th>
                            <th>Khách hàng</th>
                            <th>Ghi chú</th>
                            <th class="text-center">Người tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($customerTransactions, 0, 20) as $customerTransaction): ?>
                            <tr>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($customerTransaction['created_at'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $customerTransaction['transaction_type'] === 'deposit' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $customerTransaction['transaction_type'] === 'deposit' ? 'Nạp tiền' : 'Thanh toán' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?= number_format($customerTransaction['amount'], 0, ',', '.') ?> VNĐ
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('customers/detail/' . $customerTransaction['customer_id']) ?>">
                                        <?= esc($customerTransaction['customer_name']) ?>
                                    </a>
                                </td>
                                <td><?= esc($customerTransaction['notes']) ?: '-' ?></td>
                                <td class="text-center"><?= esc($customerTransaction['created_by_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>