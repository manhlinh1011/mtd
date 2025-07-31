<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h2><?= esc($title) ?></h2>
    <?php if (session('success')): ?>
        <div class="alert alert-success"> <?= session('success') ?> </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger"> <?= session('error') ?> </div>
    <?php endif; ?>
    <a href="<?= site_url('transaction-action-config/create') ?>" class="btn btn-primary mb-3">Thêm mapping mới</a>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Action Code</th>
                <th>Loại giao dịch</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($configs as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= esc($row['action_code']) ?></td>
                    <td><?= esc($row['transaction_type_id']) ?></td>
                    <td><?= esc($row['description']) ?></td>
                    <td>
                        <a href="<?= site_url('transaction-action-config/edit/' . $row['id']) ?>" class="btn btn-sm btn-warning">Sửa</a>
                        <form action="<?= site_url('transaction-action-config/delete/' . $row['id']) ?>" method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xoá?');">
                            <button type="submit" class="btn btn-sm btn-danger">Xoá</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $this->endSection(); ?>