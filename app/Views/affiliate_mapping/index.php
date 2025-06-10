<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="m-t-0 header-title">Danh sách liên kết cộng tác viên</h4>
                    <a href="<?= base_url('affiliate-mapping/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cộng tác viên</th>
                                <th>Khách hàng</th>
                                <th>Khách hàng phụ</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappings as $mapping): ?>
                                <tr>
                                    <td class="text-center"><?= $mapping['id'] ?></td>
                                    <td class="text-center"><?= $mapping['aff_name'] ?></td>
                                    <td class="text-center"><?= $mapping['customer_code'] ?> - <?= $mapping['customer_name'] ?></td>
                                    <td class="text-center"><?= $mapping['sub_customer_code'] ?? 'N/A' ?> - <?= $mapping['sub_customer_name'] ?? 'N/A' ?></td>
                                    <td class="text-center"><?= date('d/m/Y H:i', strtotime($mapping['created_at'])) ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('affiliate-mapping/edit/' . $mapping['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $mapping['id'] ?>)">
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
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Bạn có chắc chắn muốn xóa liên kết này?')) {
            window.location.href = '<?= base_url('affiliate-mapping/delete/') ?>' + id;
        }
    }
</script>

<?= $this->endSection() ?>