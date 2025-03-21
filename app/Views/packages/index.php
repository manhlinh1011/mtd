<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách mã bao</h3>
                </div>
                <div class="card-body">
                    <!-- Form tìm kiếm -->
                    <form method="get" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Mã bao</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        value="<?= esc($search) ?>" placeholder="Nhập mã bao...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Từ ngày</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="<?= esc($start_date) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">Đến ngày</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="<?= esc($end_date) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                        <a href="<?= base_url('packages') ?>" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Làm mới
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Hiển thị thông tin phân trang -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="text-muted">
                                Trang <?= $pager->getCurrentPage() ?>/<?= $pager->getPageCount() ?>
                                (Tổng số: <?= $pager->getTotal() ?> mã bao)
                            </span>
                        </div>
                    </div>

                    <!-- Bảng danh sách mã bao -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Ngày nhập</th>
                                    <th class="text-center">Mã bao</th>
                                    <th class="text-center">Số đơn</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $package): ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($package['package_date'])) ?></td>
                                        <td class="text-center">
                                            <?php if ($package['package_code'] === null || $package['package_code'] === ''): ?>
                                                <a href="<?= base_url('packages/detail/no-code/' . $package['package_date']) ?>">
                                                    <span class="text-danger">KHÔNG CÓ MÃ BAO</span>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('packages/detail/' . $package['package_code'] . '/' . $package['package_date']) ?>">
                                                    <?= $package['package_code'] ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $package['order_count'] ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('packages/detail/' . ($package['package_code'] ? $package['package_code'] : 'no-code') . '/' . $package['package_date']) ?>"
                                                class="btn btn-info btn-sm">
                                                Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($pager) : ?>
                        <div class="mt-3">
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>