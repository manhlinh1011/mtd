<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Kiểm tra đơn hàng</li>
                    </ol>
                </div>
                <h4 class="page-title">Quản lý yêu cầu kiểm tra đơn hàng</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Thống kê -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?= $totalPending ?></h4>
                                    <p class="mb-0">Chờ thông báo</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-bell fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?= $totalNotified ?></h4>
                                    <p class="mb-0">Đã thông báo</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nút thao tác -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="<?= base_url('order-inspections/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm yêu cầu kiểm tra
                    </a>
                    <button type="button" class="btn btn-success" onclick="markAllAsNotified()">
                        <i class="fas fa-check-double"></i> Đánh dấu tất cả đã thông báo
                    </button>
                </div>
            </div>

            <!-- Thông báo -->
            <?php if (session()->has('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Bảng dữ liệu -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="15%">Mã vận chuyển</th>
                                    <th width="30%">Ghi chú</th>
                                    <th width="12%">Ngày tạo</th>
                                    <th width="12%">Trạng thái TB</th>
                                    <th width="12%">Trạng thái đơn</th>
                                    <th width="14%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inspections)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Không có yêu cầu kiểm tra nào.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inspections as $inspection): ?>
                                        <tr>
                                            <td class="text-center"><?= $inspection['id'] ?></td>
                                            <td>
                                                <strong><?= esc($inspection['tracking_code']) ?></strong>
                                            </td>
                                            <td><?= !empty($inspection['notes']) ? esc($inspection['notes']) : '<em class="text-muted">Không có ghi chú</em>' ?></td>
                                            <td class="text-center">
                                                <?= isset($inspection['created_at']) ? date('d/m/Y H:i', strtotime($inspection['created_at'])) : '-' ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (isset($inspection['notify_checked']) && $inspection['notify_checked'] == 0): ?>
                                                    <span class="badge badge-warning">Chờ thông báo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Đã thông báo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (isset($inspection['order_id']) && $inspection['order_id']): ?>
                                                    <?php if (isset($inspection['vietnam_stock_date']) && $inspection['vietnam_stock_date']): ?>
                                                        <span class="badge badge-success">Đã về kho VN</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info">Đã về kho TQ</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Chưa về kho</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (isset($inspection['notify_checked']) && $inspection['notify_checked'] == 0): ?>
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        onclick="markAsNotified(<?= $inspection['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                    data-id="<?= $inspection['id'] ?>"
                                                    data-tracking="<?= esc($inspection['tracking_code']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
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


<script>
    // Add event listeners for delete buttons
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const tracking = this.getAttribute('data-tracking');
                deleteInspection(id, tracking);
            });
        });
    });

    function deleteInspection(id, trackingCode) {
        const deleteUrl = '<?= base_url('order-inspections/delete') ?>/' + id;
        Swal.fire({
            title: 'Xác nhận xóa',
            text: `Bạn có chắc muốn xóa yêu cầu kiểm tra cho mã "${trackingCode}"?`,
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Có, xóa',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#d33'
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Đã xóa!',
                                text: response.message,
                                type: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Lỗi!', response.message || 'Có lỗi xảy ra khi xóa!', 'error');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Có lỗi xảy ra khi xóa!';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {}
                        Swal.fire('Lỗi!', errorMessage, 'error');
                    }
                });
            }
        });
    }

    function markAsNotified(id) {
        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn đánh dấu đã thông báo?',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Có',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.value) {
                $.post('<?= base_url('order-inspections/mark-as-notified') ?>/' + id)
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire('Thành công!', response.message, 'success')
                                .then(() => {
                                    location.reload();
                                });
                        } else {
                            Swal.fire('Lỗi!', response.message, 'error');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra khi thực hiện yêu cầu.', 'error');
                    });
            }
        });
    }

    function markAllAsNotified() {
        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn đánh dấu tất cả đã thông báo?',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Có',
            cancelButtonText: 'Không'
        }).then((result) => {
            if (result.value) {
                $.post('<?= base_url('order-inspections/mark-all-as-notified') ?>')
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire('Thành công!', response.message, 'success')
                                .then(() => {
                                    location.reload();
                                });
                        } else {
                            Swal.fire('Lỗi!', response.message, 'error');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra khi thực hiện yêu cầu.', 'error');
                    });
            }
        });
    }
</script>

<?= $this->endSection() ?>