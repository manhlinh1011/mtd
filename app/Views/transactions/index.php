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
                                <option value="withdraw" <?= ($transactionType == 'withdraw') ? 'selected' : '' ?>>Rút tiền</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quỹ</label>
                            <select class="form-control" name="fund_id">
                                <option value="">Tất cả</option>
                                <?php if (isset($funds)): ?>
                                    <?php foreach ($funds as $fund): ?>
                                        <option value="<?= $fund['id'] ?>" <?= (isset($fundId) && $fundId == $fund['id']) ? 'selected' : '' ?>>
                                            <?= esc($fund['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

    <?php if (isset($filteredDeposit)): ?>
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-body text-center" style="background-color:rgb(231, 252, 236);">
                    <h5 class="text-success mb-2">Tổng Nạp (theo bộ lọc)</h5>
                    <h4 class="fw-bold mb-0"><?= number_format($filteredDeposit, 0, ',', '.') ?> đ</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body text-center" style="background-color:rgb(255, 225, 225);">
                    <h5 class="text-danger mb-2">Tổng Thanh Toán (theo bộ lọc)</h5>
                    <h4 class="fw-bold mb-0"><?= number_format(abs($filteredPayment), 0, ',', '.') ?> đ</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body text-center" style="background-color:rgb(255, 193, 7);">
                    <h5 class="text-warning mb-2">Tổng Rút (theo bộ lọc)</h5>
                    <h4 class="fw-bold mb-0"><?= number_format(abs($filteredWithdraw ?? 0), 0, ',', '.') ?> đ</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body text-center" style="background-color:rgb(210, 231, 253);">
                    <h5 class="text-primary mb-2">Số Dư (theo bộ lọc)</h5>
                    <h4 class="fw-bold mb-0"><?= number_format($filteredBalance, 0, ',', '.') ?> đ</h4>
                </div>
            </div>
        </div>
    <?php endif; ?>

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
                                    <th class="text-center">Tên quỹ</th>
                                    <th class="text-center">Phiếu xuất</th>
                                    <th class="text-center">Người tạo</th>
                                    <th class="text-center">Ghi chú</th>
                                    <?php if (in_array(session('role'), ['Quản lý', 'admin'])): ?>
                                        <th class="text-center">Thao tác</th>
                                    <?php endif; ?>
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
                                                <?php elseif ($transaction['transaction_type'] == 'payment'): ?>
                                                    <span class="badge bg-info">Thanh toán</span>
                                                <?php elseif ($transaction['transaction_type'] == 'withdraw'): ?>
                                                    <span class="badge bg-warning">Rút tiền</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($transaction['amount'], 0, ',', '.') ?> đ
                                            </td>
                                            <td class="text-center"><?= esc($transaction['fund_name'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <?php if ($transaction['invoice_id']): ?>
                                                    <a href="<?= base_url('invoices/detail/' . $transaction['invoice_id']) ?>" class="btn btn-sm btn-outline-primary">
                                                        #<?= $transaction['invoice_id'] ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= esc($transaction['created_by_name']) ?></td>
                                            <td class="text-center"><?= esc($transaction['notes']) ?></td>
                                            <?php if (in_array(session('role'), ['Quản lý', 'admin']) && $transaction['transaction_type'] == 'deposit'): ?>
                                                <td class="text-center">
                                                    <button class="btn btn-danger btn-sm btn-delete-transaction"
                                                        data-id="<?= $transaction['id'] ?>"
                                                        data-customer-id="<?= $transaction['customer_id'] ?>"
                                                        data-amount="<?= $transaction['amount'] ?>">
                                                        Xóa
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">Không có dữ liệu</td>
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
<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteTransactionModal" tabindex="-1" aria-labelledby="deleteTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTransactionModalLabel">Xác nhận xóa giao dịch</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa giao dịch này không?</p>
                <div id="delete-transaction-error" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTransaction">Xóa</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let transactionToDelete = null;
    let customerBalanceCache = {};
    $(document).on('click', '.btn-delete-transaction', function() {
        const id = $(this).data('id');
        const customerId = $(this).data('customer-id');
        const amount = parseFloat($(this).data('amount'));
        transactionToDelete = id;
        // Kiểm tra số dư realtime
        if (customerBalanceCache[customerId] === undefined) {
            $.get('/api/customer/balance/' + customerId, function(res) {
                customerBalanceCache[customerId] = parseFloat(res.balance);
                if (amount > customerBalanceCache[customerId]) {
                    $('#delete-transaction-error').removeClass('d-none').text('Số dư khách hàng không đủ để xóa giao dịch này!');
                    $('#confirmDeleteTransaction').prop('disabled', true);
                } else {
                    $('#delete-transaction-error').addClass('d-none').text('');
                    $('#confirmDeleteTransaction').prop('disabled', false);
                }
                $('#deleteTransactionModal').modal('show');
            });
        } else {
            if (amount > customerBalanceCache[customerId]) {
                $('#delete-transaction-error').removeClass('d-none').text('Số dư khách hàng không đủ để xóa giao dịch này!');
                $('#confirmDeleteTransaction').prop('disabled', true);
            } else {
                $('#delete-transaction-error').addClass('d-none').text('');
                $('#confirmDeleteTransaction').prop('disabled', false);
            }
            $('#deleteTransactionModal').modal('show');
        }
    });

    $('#confirmDeleteTransaction').on('click', function() {
        if (!transactionToDelete) return;
        $.ajax({
            url: '/transactions/delete/' + transactionToDelete,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    location.reload();
                } else {
                    $('#delete-transaction-error').removeClass('d-none').text(res.message);
                }
            },
            error: function() {
                $('#delete-transaction-error').removeClass('d-none').text('Có lỗi xảy ra, vui lòng thử lại!');
            }
        });
    });
</script>
<?= $this->endSection() ?>