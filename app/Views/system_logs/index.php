<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container">
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
            <?php foreach ($logs as $index => $log): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= ucfirst($log['entity_type']) ?></td>
                    <td><?= $log['entity_id'] ?></td>
                    <td><?= ucfirst($log['action_type']) ?></td>
                    <td><?= $log['created_by_user']['fullname'] ?? 'Không rõ' ?></td>
                    <td><?= $log['created_at'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-info view-details-btn" data-details='<?= esc($log['details']) ?>'>Xem chi tiết</button>
                    </td>
                    <td><?= $log['notes'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
            $('#logDetails').html('<pre>' + JSON.stringify(JSON.parse(details), null, 2) + '</pre>');
            $('#viewDetailsModal').modal('show');
        });
    });
</script>

<?= $this->endSection() ?>