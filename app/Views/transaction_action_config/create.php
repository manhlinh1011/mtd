<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h2><?= esc($title) ?></h2>
    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="<?= site_url('transaction-action-config/store') ?>" method="post">
        <div class="mb-3">
            <label for="action_code" class="form-label">Action Code</label>
            <input type="text" class="form-control" id="action_code" name="action_code" value="<?= old('action_code') ?>" required maxlength="50">
        </div>
        <div class="mb-3">
            <label for="transaction_type_id" class="form-label">Loại giao dịch</label>
            <select class="form-select" id="transaction_type_id" name="transaction_type_id" required>
                <option value="">-- Chọn loại giao dịch --</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= old('transaction_type_id') == $type['id'] ? 'selected' : '' ?>><?= esc($type['name']) ?> (<?= esc($type['category']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <input type="text" class="form-control" id="description" name="description" value="<?= old('description') ?>" maxlength="255">
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="<?= site_url('transaction-action-config') ?>" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<?php $this->endSection(); ?>