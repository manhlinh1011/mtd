<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mt-2">Kiểm tra kho Việt Nam</h5>
                </div>
                <div class="card-body">
                    <form id="checkForm" method="post" action="<?= base_url('orders/checkVietnamStock') ?>" class="form-horizontal mb-4">
                        <?= csrf_field() ?>
                        <div class="form-group row">
                            <label for="tracking_code" class="col-sm-2 col-form-label text-right">Mã vận đơn</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="tracking_code" name="tracking_code" placeholder="Nhập mã vận đơn" required>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary btn-block">Kiểm tra</button>
                            </div>
                        </div>
                    </form>

                    <div id="resultArea">
                        <?php if (isset($result)): ?>
                            <?php if ($result['status'] === 'not_found'): ?>
                                <div class="alert alert-warning">Mã vận đơn <strong><?= htmlspecialchars($trackingCode) ?></strong> chưa có trong hệ thống.</div>
                            <?php elseif ($result['status'] === 'in_vn'): ?>
                                <h6>Trạng thái giao hàng</h6>
                                <p>Xem chi tiết: <a href="<?= base_url('orders/edit/' . $result['order']['id']) ?>" target="_blank">Đơn hàng #<?= $result['order']['id'] ?></a></p>
                                <ul class="timeline">
                                    <?php foreach ($result['statusHistory'] as $status): ?>
                                        <li>
                                            <strong><?= date('d/m/Y H:i', strtotime($status['time'])) ?>:</strong>
                                            <?= htmlspecialchars($status['status']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif ($result['status'] === 'not_in_vn'): ?>
                                <h6>Trạng thái giao hàng</h6>
                                <p>Xem chi tiết: <a href="<?= base_url('orders/edit/' . $result['order']['id']) ?>" target="_blank">Đơn hàng #<?= $result['order']['id'] ?></a></p>
                                <ul class="timeline">
                                    <?php foreach ($result['statusHistory'] as $status): ?>
                                        <li>
                                            <strong><?= date('d/m/Y H:i', strtotime($status['time'])) ?>:</strong>
                                            <?= htmlspecialchars($status['status']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <?php if ($result['isKHOTM']): ?>
                                    <form id="updateCustomerForm" method="post" action="<?= base_url('orders/updateCustomerAndStock') ?>" class="mt-3">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="order_id" value="<?= $result['order']['id'] ?>">
                                        <input type="hidden" name="tracking_code" value="<?= htmlspecialchars($trackingCode) ?>">
                                        <div class="form-group row">
                                            <label for="customer_id" class="col-sm-2 col-form-label text-right">Chọn khách hàng</label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="customer_id" name="customer_id" required>
                                                    <option value="">-- Chọn khách hàng --</option>
                                                    <?php foreach ($customers as $customer): ?>
                                                        <?php if ($customer['customer_code'] !== 'KHOTM'): ?>
                                                            <option value="<?= $customer['id'] ?>">
                                                                <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="submit" class="btn btn-success btn-block">Xác nhận</button>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <form id="updateStockForm" method="post" action="<?= base_url('orders/updateVietnamStockDateUI') ?>" class="mt-3">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="tracking_code" value="<?= htmlspecialchars($trackingCode) ?>">
                                        <button type="submit" class="btn btn-success">Xác nhận nhập kho VN</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningModalLabel">Cảnh báo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Vui lòng chọn khách hàng trước khi xác nhận!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
        margin-bottom: 0;
    }

    .timeline:before {
        content: "";
        position: absolute;
        top: 0;
        left: 10px;
        width: 2px;
        height: 95%;
        background: #007bff;
    }

    .timeline li {
        margin-bottom: 15px;
        position: relative;
        padding-left: 30px;
    }

    .timeline li:before {
        content: "";
        position: absolute;
        left: 3px;
        top: 5px;
        width: 16px;
        height: 16px;
        background: #007bff;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 5px #007bff;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Định nghĩa base_url từ PHP
    const base_url = '<?= base_url() ?>';

    $(document).ready(function() {
        $('#checkForm').on('submit', function(e) {
            e.preventDefault();
            let trackingCode = $('#tracking_code').val().trim();
            if (trackingCode === '') {
                $('#resultArea').html('<div class="alert alert-danger">Vui lòng nhập mã vận đơn!</div>');
                return;
            }
            $('#tracking_code').val(trackingCode);
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#resultArea').html(response);
                },
                error: function() {
                    $('#resultArea').html('<div class="alert alert-danger">Có lỗi xảy ra khi kiểm tra.</div>');
                }
            });
        });

        $('#resultArea').on('submit', '#updateCustomerForm', function(e) {
            e.preventDefault();
            if ($('#customer_id').val() === '') {
                $('#warningModal').modal('show');
                return;
            }
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200) {
                        // Hiển thị thông báo thành công và timeline
                        let html = `
                            <div class="alert alert-success">${response.message}</div>
                            <h6>Trạng thái giao hàng</h6>
                            <p>Xem chi tiết: <a href="${base_url}orders/edit/${response.order_id}" target="_blank">Đơn hàng #${response.order_id}</a></p>
                            <ul class="timeline">
                        `;
                        response.statusHistory.forEach(status => {
                            html += `
                                <li>
                                    <strong>${status.time}:</strong>
                                    ${status.status}
                                </li>
                            `;
                        });
                        html += `</ul>`;
                        $('#resultArea').html(html);
                    } else {
                        $('#resultArea').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#resultArea').html('<div class="alert alert-danger">Có lỗi xảy ra khi cập nhật.</div>');
                }
            });
        });

        $('#resultArea').on('submit', '#updateStockForm', function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200) {
                        $('#resultArea').html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        $('#resultArea').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#resultArea').html('<div class="alert alert-danger">Có lỗi xảy ra khi cập nhật.</div>');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>