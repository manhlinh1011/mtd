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

            <div class="form-group row" id="subCustomerRow" style="display: none;">
                <label for="sub_customer_id" class="col-sm-2 col-form-label text-right">Chọn mã phụ</label>
                <div class="col-sm-10">
                    <select class="form-control" id="sub_customer_id" name="sub_customer_id">
                        <option value="">-- Không chọn mã phụ --</option>
                        <!-- Sub customer options will be loaded via AJAX -->
                    </select>
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