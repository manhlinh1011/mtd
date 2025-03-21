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