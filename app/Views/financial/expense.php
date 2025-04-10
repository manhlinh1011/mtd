<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Danh sách thu</h1>
            <a href="/financial" class="btn btn-secondary">Danh sách tất cả</a>
            <a href="/financial/income" class="btn btn-success">Danh sách thu</a>
            <a href="/financial/expense" class="btn btn-danger">Danh sách chi</a>
            <a href="/financial/create" class="btn btn-primary">Tạo phiếu mới</a>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ngày</th>
                        <th>Loại</th>
                        <th>Số tiền</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td class="text-center"><?= $t['id'] ?></td>
                            <td class="text-center"><?= $t['created_at'] ?></td>
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
                            <td class="text-center"><?= esc($t['creator_name']) ?></td>
                            <td class="text-center">
                                <?php if ($t['type'] === 'expense' && $t['status'] === 'pending' && session('role') === 'Quản lý'): ?>
                                    <a href="/financial/approve/<?= $t['id'] ?>" class="btn btn-success btn-sm">Duyệt</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>