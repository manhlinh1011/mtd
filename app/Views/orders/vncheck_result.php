<?php if ($status === 'not_found'): ?>
    <div class="alert alert-warning">Mã vận đơn <strong><?= htmlspecialchars($trackingCode) ?></strong> chưa có trong hệ thống.</div>
<?php elseif ($status === 'in_vn'): ?>
    <h6>Trạng thái giao hàng</h6>
    <p>Xem chi tiết: <a href="<?= base_url('orders/edit/' . $order['id']) ?>" target="_blank">Đơn hàng #<?= $order['id'] ?></a></p>
    <ul class="timeline">
        <?php foreach ($statusHistory as $status): ?>
            <li>
                <strong><?= date('d/m/Y H:i', strtotime($status['time'])) ?>:</strong>
                <?= htmlspecialchars($status['status']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (!empty($notes)): ?>
        <div class="mt-3">
            <strong>Ghi chú:</strong> <?= htmlspecialchars($notes) ?>
        </div>
    <?php endif; ?>
<?php elseif ($status === 'not_in_vn'): ?>
    <h6>Trạng thái giao hàng</h6>
    <p>Xem chi tiết: <a href="<?= base_url('orders/edit/' . $order['id']) ?>" target="_blank">Đơn hàng #<?= $order['id'] ?></a></p>
    <ul class="timeline">
        <?php foreach ($statusHistory as $status): ?>
            <li>
                <strong><?= date('d/m/Y H:i', strtotime($status['time'])) ?>:</strong>
                <?= htmlspecialchars($status['status']) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($isKHOTM): ?>
        <form id="updateCustomerForm" method="post" action="<?= base_url('orders/updateCustomerAndStock') ?>" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="tracking_code" value="<?= htmlspecialchars($trackingCode) ?>">
            <div class="form-group row">
                <label for="customer_autocomplete" class="col-sm-2 col-form-label text-right">Chọn khách hàng</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="customer_autocomplete" placeholder="Nhập mã hoặc tên khách hàng..." autocomplete="off">
                    <input type="hidden" name="customer_id" id="customer_id">
                    <small id="customer_warning" class="form-text text-danger" style="display:none;"></small>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success btn-block">Xác nhận</button>
                </div>
            </div>
            <div class="form-group row" id="subCustomerRow" style="display: none;">
                <label for="sub_customer_autocomplete" class="col-sm-2 col-form-label text-right">Chọn mã phụ</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="sub_customer_autocomplete" placeholder="Nhập mã phụ hoặc tên..." autocomplete="off">
                    <input type="hidden" name="sub_customer_id" id="sub_customer_id">
                    <small id="sub_customer_warning" class="form-text text-danger" style="display:none;"></small>
                </div>
            </div>
            <div class="form-group row">
                <label for="notes" class="col-sm-2 col-form-label text-right">Ghi chú</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Nhập ghi chú (tùy chọn)"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </form>
    <?php else: ?>
        <form id="updateStockForm" method="post" action="<?= base_url('orders/updateVietnamStockDateUI') ?>" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="tracking_code" value="<?= htmlspecialchars($trackingCode) ?>">

            <?php if (isset($hasSubCustomers) && $hasSubCustomers): ?>
                <div class="form-group row">
                    <label for="sub_customer_id" class="col-sm-2 col-form-label text-right">Chọn mã phụ</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="sub_customer_id" name="sub_customer_id">
                            <option value="">-- Không chọn mã phụ --</option>
                            <?php foreach ($subCustomers as $subCustomer): ?>
                                <option value="<?= $subCustomer['id'] ?>" <?= (isset($sub_customer_id) && $sub_customer_id == $subCustomer['id']) ? 'selected' : '' ?>>
                                    <?= $subCustomer['sub_customer_code'] ?> (<?= $subCustomer['fullname'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group row">
                <label for="notes" class="col-sm-2 col-form-label text-right">Ghi chú</label>
                <div class="col-sm-8">
                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Nhập ghi chú (tùy chọn)"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success btn-block">Xác nhận nhập kho VN</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
<?php endif; ?>

<?= $this->section('scripts') ?>
<script>
    // Tích hợp autocomplete, cảnh báo, select-all cho form động
    $(function() {
        // Tự động select all khi focus/click vào input khách hàng
        $(document).on('focus click', '#customer_autocomplete', function() {
            $(this).select();
        });
        $(document).on('focus click', '#sub_customer_autocomplete', function() {
            $(this).select();
        });

        $(document).on('input', '#customer_autocomplete', function() {
            $('#customer_warning').hide();
        });
        $(document).on('input', '#sub_customer_autocomplete', function() {
            $('#sub_customer_warning').hide();
        });

        $(document).on('blur', '#customer_autocomplete', function() {
            setTimeout(function() {
                if (!$('#customer_id').val()) {
                    $('#customer_warning').text('Vui lòng chọn đúng khách hàng từ danh sách gợi ý!').show();
                } else {
                    $('#customer_warning').hide();
                }
            }, 200);
        });
        $(document).on('blur', '#sub_customer_autocomplete', function() {
            setTimeout(function() {
                if (!$('#sub_customer_id').val() && $('#subCustomerRow').is(':visible')) {
                    $('#sub_customer_warning').text('Vui lòng chọn đúng mã phụ từ danh sách gợi ý!').show();
                } else {
                    $('#sub_customer_warning').hide();
                }
            }, 200);
        });

        // Gắn autocomplete khi form được render lại
        $(document).on('ready ajaxComplete', function() {
            $('#customer_autocomplete').autocomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: '<?= base_url('orders/search-customers') ?>',
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    $('#customer_id').val(ui.item.id);
                    if (ui.item.has_sub) {
                        $('#subCustomerRow').show();
                        $('#sub_customer_autocomplete').val('');
                        $('#sub_customer_id').val('');
                    } else {
                        $('#subCustomerRow').hide();
                        $('#sub_customer_autocomplete').val('');
                        $('#sub_customer_id').val('');
                    }
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $('#customer_id').val('');
                        $('#subCustomerRow').hide();
                        $('#sub_customer_autocomplete').val('');
                        $('#sub_customer_id').val('');
                    }
                }
            });
            $('#sub_customer_autocomplete').autocomplete({
                minLength: 1,
                source: function(request, response) {
                    var customerId = $('#customer_id').val();
                    if (!customerId) {
                        response([]);
                        return;
                    }
                    $.ajax({
                        url: '<?= base_url('orders/search-sub-customers') ?>',
                        dataType: 'json',
                        data: {
                            term: request.term,
                            customer_id: customerId
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    $('#sub_customer_id').val(ui.item.id);
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $('#sub_customer_id').val('');
                    }
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>