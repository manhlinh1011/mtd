<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2>Lịch sử hệ thống</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thực thể</th>
                        <th>ID</th>
                        <th>Hành động</th>
                        <th>Người thực hiện</th>
                        <th>Thời gian</th>
                        <th>Chi tiết</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Không có bản ghi nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 + ($pager->getCurrentPage() - 1) * $pager->getPerPage() ?></td>
                                <td class="text-center"><?= ucfirst($log['entity_type']) ?></td>
                                <td class="text-center"><?= $log['entity_id'] ?></td>
                                <td class="text-center"><?= ucfirst($log['action_type']) ?></td>
                                <td class="text-center"><?= $log['created_by_user']['fullname'] ?? 'Không rõ' ?></td>
                                <td class="text-center"><?= $log['created_at'] ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info view-details-btn" data-details='<?= htmlspecialchars($log['details'], ENT_QUOTES, 'UTF-8') ?>'>Xem chi tiết</button>
                                </td>
                                <td class="text-center"><?= $log['notes'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Phần phân trang -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <?php if (isset($pager)): ?>
                        <p>Hiển thị <?= $pager->getPerPage() ?> bản ghi trên tổng số <?= $pager->getTotal() ?> bản ghi.</p>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (isset($pager)): ?>
                        <?= $pager->links('default', 'bootstrap_pagination') ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailsModalLabel">Chi tiết hành động</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetails">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.view-details-btn').on('click', function() {
            const details = $(this).data('details');
            // Kiểm tra nếu details đã là chuỗi JSON hợp lệ
            let parsedDetails;
            try {
                parsedDetails = JSON.parse(details);
            } catch (e) {
                // Nếu không parse được, hiển thị nguyên văn chuỗi
                parsedDetails = details;
            }
            // Hiển thị dữ liệu trong modal, định dạng JSON nếu là object
            $('#logDetails').html(typeof parsedDetails === 'object' ? '<pre>' + JSON.stringify(parsedDetails, null, 2) + '</pre>' : '<p>' + parsedDetails + '</p>');
            $('#viewDetailsModal').modal('show');
        });
    });
</script>

<?= $this->endSection() ?>