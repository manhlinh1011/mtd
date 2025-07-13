<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Quản lý thu chi</h3>
            <a href="/financial" class="btn btn-secondary">Danh sách tất cả</a>
            <a href="/financial/income" class="btn btn-success">Danh sách thu</a>
            <a href="/financial/expense" class="btn btn-danger">Danh sách chi</a>
            <a href="/financial/create" class="btn btn-primary"><i class="mdi mdi-plus"></i> Tạo phiếu mới</a>
        </div>
        <div class="col-12 mt-4">
            <div class="card" style="background-color: #f4f4f4;">
                <div class="card-body">
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Quỹ</label>
                                <select name="fund_id" class="form-control">
                                    <option value="">Tất cả quỹ</option>
                                    <?php foreach ($funds as $fund): ?>
                                        <option value="<?= $fund['id'] ?>" <?= ($fundFilter == $fund['id']) ? 'selected' : '' ?>>
                                            <?= esc($fund['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Loại phiếu</label>
                                <select name="type" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="income" <?= $typeFilter === 'income' ? 'selected' : '' ?>>Thu</option>
                                    <option value="expense" <?= $typeFilter === 'expense' ? 'selected' : '' ?>>Chi</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Từ ngày</label>
                                <input type="date" name="date_from" class="form-control" value="<?= esc($dateFromFilter) ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Đến ngày</label>
                                <input type="date" name="date_to" class="form-control" value="<?= esc($dateToFilter) ?>">
                            </div>
                            <div class="col-md-2">
                                <label> </label>
                                <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-filter"></i> Lọc</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-4 mb-4">
            <div class="card" style="background-color:rgb(231, 252, 236);">
                <div class="card-body py-2">
                    <h3 class="text-center text-success">Tổng thu: <?= number_format($totalIncome + $totalCustomerDeposit, 0, ',', '.') ?> đ</h3>
                    <p class="text-center">(Khách nạp: <?= number_format($totalCustomerDeposit, 0, ',', '.') ?> đ - Thu ngoài: <?= number_format($totalIncome, 0, ',', '.') ?> đ)</p>
                    <p class="text-center"><a href="<?= base_url('transactions') ?>" class="btn btn-primary btn-sm">Khách nạp</a> <a href="<?= base_url('financial/income') ?>" class="btn btn-success btn-sm">Tổng thu</a></p>
                </div>
            </div>
        </div>
        <div class="col-4 mb-4">
            <div class="card" style="background-color:rgb(255, 214, 218);">
                <div class="card-body py-2">
                    <h3 class="text-center text-danger">Tổng chi: <?= number_format($totalExpense, 0, ',', '.') ?> đ</h3>
                    <p class="text-center">(Chỉ tính những phiếu chi được duyệt)</p>
                    <p class="text-center"><a href="<?= base_url('financial/expense') ?>" class="btn btn-danger btn-sm">Xem chi tiết</a></p>
                </div>
            </div>
        </div>
        <div class="col-4 mb-4">
            <div class="card" style="background-color:rgb(210, 231, 253);">
                <div class="card-body py-2">
                    <h3 class="text-center text-primary">Số dư: <?= number_format($balance, 0, ',', '.') ?> đ</h3>
                    <p class="text-center">(Tổng thu - Tổng chi)</p>
                    <p class="text-center"><a href="<?= base_url('financial') ?>" class="btn btn-primary btn-sm">Tất cả</a></p>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ngày</th>
                                <th>Ngày giao dịch</th>
                                <th>Loại</th>
                                <th>Số tiền</th>
                                <th>Mô tả</th>
                                <th>Trạng thái</th>
                                <th>Người tạo</th>
                                <th>Người duyệt</th>
                                <th>Tài khoản thu/chi</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td class="text-center"><?= $t['id'] ?></td>
                                    <td class="text-center"><?= $t['created_at'] ?></td>
                                    <td class="text-center">
                                        <?php if (!$t['transaction_date']): ?>
                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#updateDateModal<?= $t['id'] ?>">Cập nhật</button>
                                            <!-- Modal cập nhật ngày giao dịch -->
                                            <div class="modal fade" id="updateDateModal<?= $t['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="updateDateModalLabel<?= $t['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form method="post" action="<?= base_url('financial/updateTransactionDate/' . $t['id']) ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="updateDateModalLabel<?= $t['id'] ?>">Cập nhật ngày giao dịch</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Ngày giao dịch</label>
                                                                    <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d', strtotime($t['created_at'])) ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                                <button type="submit" class="btn btn-primary">Lưu</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <?= date('d/m/Y', strtotime($t['transaction_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $t['type'] === 'income' ? 'Thu' : 'Chi' ?></td>
                                    <td class="text-center"><?= $t['type'] === 'income' ? '+' : '-' ?><?= number_format($t['amount'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= $t['description'] ?></td>
                                    <td class="text-center">
                                        <?php if ($t['status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Chờ duyệt</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Đã duyệt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= esc($t['creator_name'] ?? 'Không xác định') ?></td>
                                    <td class="text-center"><?= esc($t['approver_name'] ?? '-') ?></td>
                                    <td class="text-center"><?= $t['fund_name'] ?? '-' ?></td>
                                    <td class="text-center">
                                        <?php if ($t['type'] === 'expense' && $t['status'] === 'pending' && session('role') === 'Quản lý'): ?>
                                            <a href="/financial/approve/<?= $t['id'] ?>" class="btn btn-success btn-sm">Duyệt</a>
                                            <a href="/financial/reject/<?= $t['id'] ?>" class="btn btn-danger btn-sm">Từ chối</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (isset($pager)): ?>
                        <div class="d-flex justify-content-center mt-3">
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>