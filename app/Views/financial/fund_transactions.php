<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3">Danh sách thu chi tổng hợp các quỹ</h3>
            <a href="/financial/fundTransactions" class="btn btn-primary">Tất cả giao dịch</a>
            <a href="/financial" class="btn btn-secondary">Quay lại quản lý thu chi</a>
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
                                <label>Từ ngày</label>
                                <input type="date" name="date_from" class="form-control" value="<?= esc($dateFromFilter) ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Đến ngày</label>
                                <input type="date" name="date_to" class="form-control" value="<?= esc($dateToFilter) ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="approved" <?= (isset($statusFilter) && $statusFilter === 'approved') ? 'selected' : '' ?>>Đã duyệt</option>
                                    <option value="pending" <?= (isset($statusFilter) && $statusFilter === 'pending') ? 'selected' : '' ?>>Chờ duyệt</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-filter"></i> Lọc</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php /*
        // XÓA TOÀN BỘ ĐOẠN NÀY:
        // Tính toán tổng số giao dịch, tổng thu, tổng chi, số dư quỹ
        // $totalTransactions = $pager['total'];
        // ...
        // unset($fb);
        // $displayBalance = ...
        */ ?>
        <div class="col-12">
            <div class="row text-center">
                <div class="col-md-3 mb-2">
                    <div class="card" style="background-color: #e6f7ff;">
                        <div class="card-body py-2">
                            <h5 class="mb-1">Tổng số giao dịch</h5>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #1890ff;">
                                <?= number_format($totalTransactions) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card" style="background-color: #eaffea;">
                        <div class="card-body py-2">
                            <h5 class="mb-1">Tổng thu</h5>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #52c41a;">
                                <?= number_format($totalIncome, 0, ',', '.') ?> đ
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card" style="background-color: #fff1f0;">
                        <div class="card-body py-2">
                            <h5 class="mb-1">Tổng chi</h5>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #f5222d;">
                                <?= number_format($totalExpense, 0, ',', '.') ?> đ
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card" style="background-color: #f0f5ff;">
                        <div class="card-body py-2">
                            <h5 class="mb-1">Số dư quỹ<?= $fundFilter && isset($fundBalances[$fundFilter]) ? ' (' . $fundBalances[$fundFilter]['name'] . ')' : '' ?></h5>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #2f54eb;">
                                <?= number_format($soDuQuy, 0, ',', '.') ?> đ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <form method="get" action="<?= base_url('financial/export-fund-transactions') ?>" target="_blank">
                <input type="hidden" name="fund_id" value="<?= esc($fundFilter) ?>">
                <input type="hidden" name="date_from" value="<?= esc($dateFromFilter) ?>">
                <input type="hidden" name="date_to" value="<?= esc($dateToFilter) ?>">
                <input type="hidden" name="status" value="<?= esc($statusFilter) ?>">
                <button class="btn btn-success"> <i class="mdi mdi-file-excel"></i> Xuất file excel</button>
            </form>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th style="width: 130px;">Ngày tạo</th>
                                <th style="width: 120px;">Ngày giao dịch</th>
                                <th style="width: 50px;">Loại</th>
                                <th style="width: 100px;">Thu</th>
                                <th style="width: 100px;">Chi</th>
                                <th style="width: 220px;">Quỹ</th>
                                <th>Mô tả</th>
                                <th style="width: 100px;">Trạng thái</th>
                                <th style="width: 100px;">Người tạo</th>
                                <th style="width: 100px;">Người duyệt</th>
                                <th style="width: 110px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = ($pager['currentPage'] - 1) * $pager['perPage'] + 1; ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td class="text-center"><?= $stt++ ?></td>
                                    <td class="text-center"><?= $t['created_at'] ? date('d/m/Y H:i', strtotime($t['created_at'])) : '' ?></td>
                                    <td class="text-center">
                                        <?php if (empty($t['transaction_date'])): ?>
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
                                                                <input type="hidden" name="is_customer_deposit" value="<?= !empty($t['is_customer_deposit']) ? 1 : 0 ?>">
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
                                    <td class="text-center">
                                        <?php if ($t['type'] === 'income'): ?>
                                            <?php if (!empty($t['is_customer_deposit'])): ?>
                                                <span class="badge bg-success">Nạp</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Thu</span>
                                            <?php endif; ?>
                                        <?php elseif ($t['type'] === 'withdraw' && !empty($t['is_customer_withdraw'])): ?>
                                            <span class="badge bg-warning">Rút</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Chi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($t['type'] === 'income'): ?>
                                            <?= '+' . number_format($t['amount'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($t['type'] === 'expense'): ?>
                                            <?= '-' . number_format($t['amount'], 0, ',', '.') ?>
                                        <?php elseif ($t['type'] === 'withdraw' && !empty($t['is_customer_withdraw'])): ?>
                                            <?= '-' . number_format(abs($t['amount']), 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= esc($t['fund_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($t['is_customer_deposit']) && $t['type'] === 'income'): ?>
                                            <span class="badge bg-info"><?= esc($t['customer_code'] ?? 'Khách nạp') ?></span>
                                        <?php endif; ?>
                                        <?= $t['description'] ?? '' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($t['status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Chờ duyệt</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Đã duyệt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= esc($t['creator_name'] ?? '-') ?></td>
                                    <td class="text-center"><?= esc($t['approver_name'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if ($t['status'] === 'pending' && $t['type'] === 'expense' && session('role') === 'Quản lý'): ?>
                                            <a href="<?= base_url('financial/approve/' . $t['id']) ?>" class="btn btn-success btn-sm mb-1" style="width: 80px; padding: 2px;"> <i class="mdi mdi-check"></i> Duyệt</a>
                                            <a href="<?= base_url('financial/reject/' . $t['id']) ?>" class="btn btn-danger btn-sm" style="width: 80px; padding: 2px;"> <i class="mdi mdi-close"></i> Từ chối</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                    // Hiển thị phân trang thủ công
                    $totalPages = ceil($pager['total'] / $pager['perPage']);
                    $currentPage = $pager['currentPage'];
                    $range = 2; // số trang lân cận hiển thị
                    $startPage = max(1, $currentPage - $range);
                    $endPage = min($totalPages, $currentPage + $range);
                    ?>
                    <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?= $fundFilter ? '&fund_id=' . $fundFilter : '' ?><?= $dateFromFilter ? '&date_from=' . $dateFromFilter : '' ?><?= $dateToFilter ? '&date_to=' . $dateToFilter : '' ?>">&laquo;</a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= $fundFilter ? '&fund_id=' . $fundFilter : '' ?><?= $dateFromFilter ? '&date_from=' . $dateFromFilter : '' ?><?= $dateToFilter ? '&date_to=' . $dateToFilter : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $totalPages ?><?= $fundFilter ? '&fund_id=' . $fundFilter : '' ?><?= $dateFromFilter ? '&date_from=' . $dateFromFilter : '' ?><?= $dateToFilter ? '&date_to=' . $dateToFilter : '' ?>">&raquo;</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>