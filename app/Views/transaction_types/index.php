<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('transaction-types/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Thêm loại giao dịch
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Loại giao dịch thu -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-arrow-up text-success"></i> Loại giao dịch thu
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="40%">Tên loại</th>
                                                    <th width="25%">Mô tả</th>
                                                    <th width="10%">Thứ tự</th>
                                                    <th width="10%">Trạng thái</th>
                                                    <th width="10%">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($income_types)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">Không có dữ liệu</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($income_types as $index => $type): ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td>
                                                                <strong><?= esc($type['name']) ?></strong>
                                                                <?php if (isset($transaction_counts[$type['id']])): ?>
                                                                    <span class="badge badge-info">
                                                                        <?= $transaction_counts[$type['id']]['total_count'] ?> giao dịch
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= esc($type['description']) ?></td>
                                                            <td><?= $type['sort_order'] ?></td>
                                                            <td>
                                                                <?php if ($type['is_active']): ?>
                                                                    <span class="badge badge-success">Hoạt động</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-secondary">Không hoạt động</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="<?= base_url('transaction-types/edit/' . $type['id']) ?>"
                                                                        class="btn btn-info" title="Chỉnh sửa">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <?php if (!isset($transaction_counts[$type['id']]) || $transaction_counts[$type['id']]['total_count'] == 0): ?>
                                                                        <a href="<?= base_url('transaction-types/delete/' . $type['id']) ?>"
                                                                            class="btn btn-danger" title="Xóa"
                                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa loại giao dịch này?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <a href="<?= base_url('transaction-types/toggle/' . $type['id']) ?>"
                                                                        class="btn btn-<?= $type['is_active'] ? 'warning' : 'success' ?>"
                                                                        title="<?= $type['is_active'] ? 'Tắt hoạt động' : 'Bật hoạt động' ?>">
                                                                        <i class="fas fa-<?= $type['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loại giao dịch chi -->
                        <div class="col-md-6">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <i class="fas fa-arrow-down text-danger"></i> Loại giao dịch chi
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="40%">Tên loại</th>
                                                    <th width="25%">Mô tả</th>
                                                    <th width="10%">Thứ tự</th>
                                                    <th width="10%">Trạng thái</th>
                                                    <th width="10%">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($expense_types)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">Không có dữ liệu</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($expense_types as $index => $type): ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td>
                                                                <strong><?= esc($type['name']) ?></strong>
                                                                <?php if (isset($transaction_counts[$type['id']])): ?>
                                                                    <span class="badge badge-info">
                                                                        <?= $transaction_counts[$type['id']]['total_count'] ?> giao dịch
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= esc($type['description']) ?></td>
                                                            <td><?= $type['sort_order'] ?></td>
                                                            <td>
                                                                <?php if ($type['is_active']): ?>
                                                                    <span class="badge badge-success">Hoạt động</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-secondary">Không hoạt động</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="<?= base_url('transaction-types/edit/' . $type['id']) ?>"
                                                                        class="btn btn-info" title="Chỉnh sửa">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <?php if (!isset($transaction_counts[$type['id']]) || $transaction_counts[$type['id']]['total_count'] == 0): ?>
                                                                        <a href="<?= base_url('transaction-types/delete/' . $type['id']) ?>"
                                                                            class="btn btn-danger" title="Xóa"
                                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa loại giao dịch này?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <a href="<?= base_url('transaction-types/toggle/' . $type['id']) ?>"
                                                                        class="btn btn-<?= $type['is_active'] ? 'warning' : 'success' ?>"
                                                                        title="<?= $type['is_active'] ? 'Tắt hoạt động' : 'Bật hoạt động' ?>">
                                                                        <i class="fas fa-<?= $type['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
<?= $this->endSection() ?>