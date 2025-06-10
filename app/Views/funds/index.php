<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Quản lý quỹ</h1>
        <a href="<?= base_url('funds/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm quỹ mới
        </a>
    </div>

    <?php if (session()->has('message')): ?>
        <div class="alert alert-success">
            <?= session('message') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên quỹ</th>
                            <th>Số tài khoản</th>
                            <th>Ngân hàng</th>
                            <th>Chủ tài khoản</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($funds as $fund): ?>
                            <tr>
                                <td class="text-center"><?= $fund['id'] ?></td>
                                <td class="text-center"><?= $fund['name'] ?></td>
                                <td class="text-center"><?= $fund['account_number'] ?? '-' ?></td>
                                <td class="text-center"><?= $fund['bank_name'] ?? '-' ?></td>
                                <td class="text-center"><?= $fund['account_holder'] ?? '-' ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('funds/detail/' . $fund['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('funds/edit/' . $fund['id']) ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $fund['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Bạn có chắc chắn muốn xóa quỹ này?')) {
            window.location.href = `<?= base_url('funds/delete/') ?>${id}`;
        }
    }
</script>
<?= $this->endSection() ?>