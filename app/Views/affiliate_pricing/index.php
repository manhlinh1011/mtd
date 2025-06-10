<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="m-t-0 header-title">Danh sách bảng giá cộng tác viên</h4>
                    <a href="<?= base_url('affiliate-pricing/create') ?>" class="btn btn-primary">
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
                                <th>Mã lô</th>
                                <th>Giá/kg</th>
                                <th>Giá/m³</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pricings as $pricing): ?>
                                <tr>
                                    <td><?= $pricing['id'] ?></td>
                                    <td><?= $pricing['aff_name'] ?></td>
                                    <td><?= $pricing['order_code'] ?></td>
                                    <td><?= number_format($pricing['aff_price_per_kg'], 0, ',', '.') ?> VNĐ</td>
                                    <td><?= number_format($pricing['aff_price_per_cubic_meter'], 0, ',', '.') ?> VNĐ</td>
                                    <td><?= date('d/m/Y', strtotime($pricing['start_date'])) ?></td>
                                    <td>
                                        <?= $pricing['end_date'] ? date('d/m/Y', strtotime($pricing['end_date'])) : 'Không giới hạn' ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($pricing['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('affiliate-pricing/edit/' . $pricing['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $pricing['id'] ?>)">
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
        if (confirm('Bạn có chắc chắn muốn xóa bảng giá này?')) {
            window.location.href = '<?= base_url('affiliate-pricing/delete/') ?>' + id;
        }
    }
</script>

<?= $this->endSection() ?>