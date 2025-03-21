<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container">
    <h1 class="text-center">PHIẾU XUẤT HÀNG</h1>
    <h4 class="text-center">#<?= $invoice['id'] ?></h4>
    <div class="mt-4">
        <div class="row">
            <div class="col-6">
                <h5>Thông tin Phiếu Xuất</h5>
                <p><strong>Khách hàng:</strong> <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)<br>
                    <strong>Địa chỉ:</strong> <?= $customer['address'] ?><br>
                    <strong>Số điện thoại:</strong>
                    <?= substr($customer['phone'], 0, 3) . str_repeat('*', strlen($customer['phone']) - 6) . substr($customer['phone'], -3) ?><br>
                    <strong>Trạng thái giao hàng:</strong>
                    <span class="badge <?= $invoice['shipping_status'] === 'pending' ? 'bg-secondary' : 'bg-success' ?>">
                        <?= ucfirst($invoice['shipping_status']) === 'Pending' ? 'Chưa giao' : 'Đã giao' ?>
                    </span><br>
                    <strong>Trạng thái thanh toán</strong>
                    <span class="badge 
                        <?php
                        if ($invoice['payment_status'] === 'paid') {
                            echo 'bg-success';
                        } else {
                            echo 'bg-danger';
                        }
                        ?> me-2">
                        <?php
                        if ($invoice['payment_status'] === 'paid') {
                            echo 'Đã thanh toán';
                        } else {
                            echo 'Chưa thanh toán';
                        }
                        ?>
                    </span><br>
                    <?php if ($invoice['payment_status'] === 'paid'): ?>
                        <span>Đã thanh toán: <?= number_format($total_amount, 0, ',', '.') ?>đ</span><br>
                    <?php endif; ?>
                    Tổng số tiền cần thanh toán: <?= number_format($total_amount, 0, ',', '.') ?>đ<br>
                </p>
                <p><strong>Người tạo:</strong> <?= $creator['fullname'] ?? 'Không rõ' ?><br />
                    <?php if ($invoice['shipping_confirmed_by']): ?>
                        <strong>Xác nhận giao:</strong> <?= $shipping_confirmed_by['fullname'] ?? 'Không rõ' ?> <br />
                        Thời gian: <?= $invoice['shipping_confirmed_at'] ? date('H:i d/m/Y', strtotime($invoice['shipping_confirmed_at'])) : 'Chưa xác định' ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-6 text-right">
                <?= $invoice['created_at'] ?><br>
                Số kiện: <?= count($orders) ?>
            </div>
        </div>
    </div>

    <!-- Danh sách đơn hàng -->
    <div class="mt-4">
        <h5>Danh sách Đơn Hàng</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Mã vận chuyển</th>
                    <th>Mã bao</th>
                    <th>Hàng</th>
                    <th>Số lượng</th>
                    <th>KL (kg)</th>
                    <th>Kích thước</th>
                    <th>Khối m³</th>
                    <th>Giá/kg</th>
                    <th>Giá/khối</th>
                    <th>Phí nội địa</th>
                    <th>Tính theo</th>
                    <th>Tổng giá</th>
                    <?php if (in_array(session('role'), ['Quản lý'])): ?>
                        <th class="d-print-none">Hành động</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                $totalWeight = 0;
                $totalVolume = 0;
                $totalQuantity = 0; // Thêm biến tổng số lượng
                $totalDomesticFee = 0; // Thêm biến tổng phí nội địa
                ?>
                <?php foreach ($orders as $index => $order): ?>
                    <?php
                    $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                    $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                    $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
                    $total += $finalPrice;
                    $priceMethod = ($priceByWeight >= $priceByVolume) ? "Cân nặng" : "Thể tích";
                    // Tính tổng các giá trị
                    $totalWeight += $order['total_weight'];
                    $totalVolume += $order['volume'];
                    $totalQuantity += $order['quantity']; // Cộng dồn số lượng
                    $totalDomesticFee += $order['domestic_fee']; // Cộng dồn phí nội địa
                    ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td class="text-center"><?= $order['tracking_code'] ?></td>
                        <td class="text-center"><?= $order['package_code'] ?></td>
                        <td class="text-center"><?= $order['product_type_name'] ?></td>
                        <td class="text-center"><?= $order['quantity'] ?></td>
                        <td class="text-center"><?= $order['total_weight'] ?></td>
                        <td class="text-center"><?= $order['length'] ?>x<?= $order['width'] ?>x<?= $order['height'] ?></td>
                        <td class="text-center"><?= $order['volume'] ?></td>
                        <td class="text-center"><?= number_format($order['price_per_kg'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= number_format($order['domestic_fee'], 2, '.', ',') ?></td>
                        <td class="text-center"><?= $priceMethod ?></td>
                        <td class="text-center"><?= number_format($finalPrice, 0, ',', '.') ?> đ</td>
                        <?php if (in_array(session('role'), ['Quản lý'])): ?>
                            <td class="text-center d-print-none">
                                <button class="btn btn-sm btn-warning reassign-order-btn" data-order-id="<?= $order['id'] ?>" data-toggle="modal" data-target="#reassignOrderModal">
                                    <i class="mdi mdi-account-switch mr-1"></i> Chuyển
                                </button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Tổng</th>
                    <th><?= number_format($totalQuantity, 0, ',', '.') ?></th>
                    <th><?= number_format($totalWeight, 2, ',', '.') ?></th>
                    <th></th>
                    <th><?= number_format($totalVolume, 3, ',', '.') ?></th>
                    <th colspan="2"></th>
                    <th><?= number_format($totalDomesticFee, 2, '.', ',') ?></th>
                    <th></th>
                    <th><?= number_format($total, 0, ',', '.') ?> đ</th>
                    <?php if (in_array(session('role'), ['Quản lý'])): ?>
                        <th class="d-print-none"></th>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th colspan="12">Phí giao hàng</th>
                    <th><?= number_format($invoice['shipping_fee'], 0, ',', '.') ?> đ</th>
                    <?php if (in_array(session('role'), ['Quản lý'])): ?>
                        <th class="d-print-none"></th>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th colspan="12">Phí khác</th>
                    <th><?= number_format($invoice['other_fee'], 0, ',', '.') ?> đ</th>
                    <?php if (in_array(session('role'), ['Quản lý'])): ?>
                        <th class="d-print-none"></th>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th colspan="12">Tổng cộng</th>
                    <th><?= number_format($total + $invoice['shipping_fee'] + $invoice['other_fee'], 0, ',', '.') ?> đ</th>
                    <?php if (in_array(session('role'), ['Quản lý'])): ?>
                        <th class="d-print-none"></th>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row" style="margin-top: 50px;">
        <div class="col-12">
            <?php if (!empty($invoice['notes'])): ?>
                <strong>Ghi chú:</strong> <?= esc($invoice['notes']) ?><br>
            <?php endif; ?>
        </div>
        <div class="col-6 text-center d-none d-print-block">
            <strong>Người lập phiếu</strong>
        </div>
        <div class="col-6 text-center d-none d-print-block">
            <strong>Người nhận hàng</strong><br>
            <i>(Ký và Xác nhận)</i>
        </div>
    </div>

    <div class="fixed-bottom content-page">
        <div class="row ml-1">
            <div class="col-12" style="background-color: #f4f4f4;">
                <div class="mt-3 mb-3 text-center d-print-none">
                    <a href="javascript:window.print()" class="btn btn-primary">
                        <i class="mdi mdi-printer mr-1"></i> In Phiếu
                    </a>

                    <?php if (in_array(session('role'), ['Nhân viên', 'Quản lý']) && $invoice['shipping_status'] !== 'confirmed'): ?>
                        <a href="/invoices/confirmShipping/<?= $invoice['id'] ?>" class="btn btn-success">
                            <i class="mdi mdi-truck-check mr-1"></i> Xác nhận đã giao
                        </a>
                    <?php endif; ?>

                    <?php if (in_array(session('role'), ['Kế toán', 'Quản lý']) && $invoice['payment_status'] === 'unpaid'): ?>
                        <button id="payButton" class="btn btn-success" data-invoice-id="<?= $invoice['id'] ?>">
                            <i class="mdi mdi-cash mr-1"></i> Xác nhận thanh toán
                        </button>
                    <?php endif; ?>

                    <?php if (in_array(session('role'), ['Kế toán', 'Quản lý']) && $invoice['payment_status'] === 'unpaid'): ?>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editShippingFeeModal">
                            <i class="mdi mdi-truck-delivery mr-1"></i> Sửa phí giao hàng
                        </button>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editOtherFeeModal">
                            <i class="mdi mdi-currency-usd mr-1"></i> Sửa phí khác
                        </button>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editBulkPriceModal">
                            <i class="mdi mdi-table-edit mr-1"></i> Sửa giá
                        </button>
                    <?php endif; ?>

                    <a href="/invoices" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left mr-1"></i> Quay Lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa phí giao hàng -->
    <div class="modal fade" id="editShippingFeeModal" tabindex="-1" role="dialog" aria-labelledby="editShippingFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editShippingFeeModalLabel">Sửa Phí Giao Hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editShippingFeeForm">
                        <div class="form-group">
                            <label for="shippingFeeInput" class="form-label">Phí giao hàng (VNĐ)</label>
                            <input type="number" class="form-control" id="shippingFeeInput" name="shipping_fee" value="<?= number_format($invoice['shipping_fee'], 0, '', '') ?>" required min="0">
                        </div>
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="saveShippingFeeBtn">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa phí khác -->
    <div class="modal fade" id="editOtherFeeModal" tabindex="-1" role="dialog" aria-labelledby="editOtherFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editOtherFeeModalLabel">Sửa Phí Khác</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editOtherFeeForm">
                        <div class="form-group">
                            <label for="otherFeeInput" class="form-label">Phí khác (VNĐ)</label>
                            <input type="number" class="form-control" id="otherFeeInput" name="other_fee" value="<?= number_format($invoice['other_fee'], 0, '', '') ?>" required min="0">
                        </div>
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="saveOtherFeeBtn">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cảnh báo giao hàng chưa xác nhận -->
    <div class="modal fade" id="shippingNotConfirmedModal" tabindex="-1" role="dialog" aria-labelledby="shippingNotConfirmedModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shippingNotConfirmedModalLabel">Cảnh báo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="shippingNotConfirmedMessage">
                    Phiếu xuất phải được xác nhận giao hàng trước khi thanh toán.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Không đủ số dư -->
    <div class="modal fade" id="insufficientBalanceModal" tabindex="-1" role="dialog" aria-labelledby="insufficientBalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insufficientBalanceModalLabel">Không đủ số dư</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="insufficientBalanceMessage">
                    Số dư hiện tại: <span id="currentBalance"></span>. Vui lòng nạp thêm tiền.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <a href="<?= base_url('customers/detail/' . $customer['id']) ?>" target="_blank" class="btn btn-primary">Nạp tiền</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cảnh báo đơn hàng cần cập nhật giá -->
    <div class="modal fade" id="invalidPriceModal" tabindex="-1" role="dialog" aria-labelledby="invalidPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invalidPriceModalLabel">Cảnh báo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="invalidPriceMessage">
                    <!-- Nội dung sẽ được điền động qua JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <a href="/orders" class="btn btn-primary">Cập nhật giá</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cảnh báo phí giao hàng và phí khác là 0 -->
    <div class="modal fade" id="zeroFeesWarningModal" tabindex="-1" role="dialog" aria-labelledby="zeroFeesWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="zeroFeesWarningModalLabel">Cảnh báo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="zeroFeesWarningMessage">
                    <!-- Nội dung sẽ được điền động qua JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="confirmZeroFeesPaymentBtn" data-invoice-id="<?= $invoice['id'] ?>">Xác nhận thanh toán</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thanh toán thành công -->
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1" role="dialog" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentSuccessModalLabel">Thành công</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="paymentSuccessMessage">
                    <!-- Nội dung sẽ được điền động qua JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa giá hàng loạt -->
    <div class="modal fade" id="editBulkPriceModal" tabindex="-1" role="dialog" aria-labelledby="editBulkPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBulkPriceModalLabel">Sửa Giá Hàng Loạt</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editBulkPriceForm">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Mã vận chuyển</th>
                                    <th>Giá / 1kg (VNĐ)</th>
                                    <th>Giá / 1m³ (VNĐ)</th>
                                    <th>Phí nội địa (Tệ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td style="width: 300px;"><?= $order['tracking_code'] ?></td>
                                        <td>
                                            <input type="text" name="orders[<?= $order['id'] ?>][price_per_kg]" class="form-control price-input text-center"
                                                value="<?= number_format($order['price_per_kg'], 0, ',', '.') ?>" placeholder="Nhập giá 1kg">
                                        </td>
                                        <td>
                                            <input type="text" name="orders[<?= $order['id'] ?>][price_per_cubic_meter]" class="form-control price-input text-center"
                                                value="<?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?>" placeholder="Nhập giá 1m³">
                                        </td>
                                        <td>
                                            <input type="text" name="orders[<?= $order['id'] ?>][domestic_fee]" class="form-control fee-input text-center"
                                                value="<?= number_format($order['domestic_fee'], 2, '.', '') ?>" placeholder="Nhập phí nội địa">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="saveBulkPriceBtn">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thông báo thành công -->
    <div class="modal fade" id="updateSuccessModal" tabindex="-1" role="dialog" aria-labelledby="updateSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateSuccessModalLabel">Thông báo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="updateSuccessMessage">
                    <!-- Nội dung sẽ được điền động qua JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chuyển khách hàng -->
    <div class="modal fade" id="reassignOrderModal" tabindex="-1" role="dialog" aria-labelledby="reassignOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reassignOrderModalLabel">Chuyển đơn hàng sang khách hàng khác</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="reassignOrderForm">
                        <div class="form-group">
                            <label for="newCustomerId">Chọn khách hàng mới:</label>
                            <select class="form-control" id="newCustomerId" name="new_customer_id" required>
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>"><?= $customer['customer_code'] ?> - <?= $customer['fullname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="order_id" id="orderId">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="confirmReassignBtn">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>

</div>




<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Định nghĩa hàm checkBalanceAndPay toàn cục
    function checkBalanceAndPay(invoiceId) {
        $.ajax({
            url: '<?= base_url("invoices/addPayment/") ?>' + invoiceId,
            method: 'POST',
            data: {},
            success: function(response) {
                if (response.success) {
                    // Hiển thị modal thành công
                    showSuccessModal(response.message, response.total_paid, response.new_balance, response.customer_detail_link);
                } else {
                    if (response.modal_type === 'shipping_not_confirmed') {
                        // Hiển thị modal cảnh báo giao hàng chưa xác nhận
                        $('#shippingNotConfirmedMessage').text(response.message);
                        $('#shippingNotConfirmedModal').modal('show');
                    } else if (response.modal_type === 'insufficient_balance') {
                        // Hiển thị modal không đủ số dư
                        $('#currentBalance').text(response.current_balance);
                        $('#insufficientBalanceModal').modal('show');
                    } else if (response.modal_type === 'invalid_price') {
                        // Hiển thị modal cảnh báo đơn hàng cần cập nhật giá
                        $('#invalidPriceMessage').html(response.message + '<ul>' + response.invalid_orders.map(order => '<li>' + order + '</li>').join('') + '</ul>');
                        $('#invalidPriceModal').modal('show');
                    } else if (response.modal_type === 'zero_fees_warning') {
                        // Hiển thị modal cảnh báo phí giao hàng và phí khác là 0, yêu cầu xác nhận
                        $('#zeroFeesWarningMessage').html(response.message + '<p>Tổng số tiền cần thanh toán: ' + response.total_amount + ' VNĐ</p><p>Số dư hiện tại: ' + response.current_balance + ' VNĐ</p>');
                        $('#zeroFeesWarningModal').modal('show');
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    }

    $(document).ready(function() {
        // Gắn sự kiện click cho nút "Xác nhận thanh toán"
        $('#payButton').on('click', function() {
            const invoiceId = $(this).data('invoice-id');
            checkBalanceAndPay(invoiceId);
        });

        // Xử lý nút "Cập nhật" cho Phí giao hàng
        $('#saveShippingFeeBtn').on('click', function() {
            var shippingFee = parseInt($('#shippingFeeInput').val());
            if (isNaN(shippingFee) || shippingFee < 0) {
                alert('Phí giao hàng phải là số nguyên không âm.');
                return;
            }
            $.ajax({
                url: '/invoices/updateShippingFee/<?= $invoice['id'] ?>',
                type: 'POST',
                data: $('#editShippingFeeForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editShippingFeeModal').modal('hide');
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    console.log('AJAX Error:', xhr, status, error);
                }
            });
        });

        // Xử lý nút "Cập nhật" cho Phí khác
        $('#saveOtherFeeBtn').on('click', function() {
            var otherFee = parseInt($('#otherFeeInput').val());
            if (isNaN(otherFee) || otherFee < 0) {
                alert('Phí khác phải là số nguyên không âm.');
                return;
            }
            $.ajax({
                url: '/invoices/updateOtherFee/<?= $invoice['id'] ?>',
                type: 'POST',
                data: $('#editOtherFeeForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editOtherFeeModal').modal('hide');
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    console.log('AJAX Error:', xhr, status, error);
                }
            });
        });

        // Xử lý nút xác nhận thanh toán khi phí là 0
        $('#confirmZeroFeesPaymentBtn').on('click', function() {
            const invoiceId = $(this).data('invoice-id');
            $.ajax({
                url: '<?= base_url("invoices/addPayment/") ?>' + invoiceId,
                method: 'POST',
                data: {
                    confirm_zero_fees: true
                },
                success: function(response) {
                    if (response.success) {
                        showSuccessModal(response.message, response.total_paid, response.new_balance, response.customer_detail_link);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            });
            $('#zeroFeesWarningModal').modal('hide');
        });

        // Xử lý nút "Xác nhận" trong modal sửa giá hàng loạt
        $('#saveBulkPriceBtn').on('click', function() {
            $.ajax({
                url: '/invoices/updateBulkPrices/<?= $invoice['id'] ?>',
                type: 'POST',
                data: $('#editBulkPriceForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#editBulkPriceModal').modal('hide');
                        $('#updateSuccessMessage').text(response.message); // Điền nội dung thông báo
                        $('#updateSuccessModal').modal('show'); // Hiển thị modal
                        $('#updateSuccessModal').on('hidden.bs.modal', function() {
                            location.reload(); // Tải lại trang sau khi đóng modal
                        });
                    } else {
                        $('#updateSuccessMessage').text(response.message);
                        $('#updateSuccessModal').modal('show');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updateSuccessMessage').text('Có lỗi xảy ra. Vui lòng thử lại.');
                    $('#updateSuccessModal').modal('show');
                    console.log('AJAX Error:', xhr, status, error);
                }
            });
        });

        // Định dạng giá khi người dùng nhập
        $('.price-input').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, ''); // Chỉ cho phép số
            if (value) {
                $(this).val(Number(value).toLocaleString('vi-VN')); // Định dạng số VN
            }
        });

        $('.fee-input').on('input', function() {
            let value = $(this).val().replace(/[^0-9.,]/g, ''); // Cho phép số, dấu chấm, dấu phẩy
            $(this).val(value);
        });


        // Gắn sự kiện click cho nút "Loại bỏ và Chuyển khách hàng"
        $('.reassign-order-btn').on('click', function() {
            const orderId = $(this).data('order-id');
            $('#orderId').val(orderId);
        });

        // Xử lý nút "Xác nhận" trong modal chuyển khách hàng
        $('#confirmReassignBtn').on('click', function() {
            const orderId = $('#orderId').val();
            $.ajax({
                url: '<?= base_url("invoices/reassignOrder/") ?>' + orderId,
                type: 'POST',
                data: $('#reassignOrderForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#reassignOrderModal').modal('hide');
                        // Thay thế \n bằng <br> để hiển thị xuống dòng
                        $('#updateSuccessMessage').html(response.message.replace(/\n/g, '<br>'));
                        $('#updateSuccessModal').modal('show');
                        $('#updateSuccessModal').on('hidden.bs.modal', function() {
                            location.reload(); // Tải lại trang sau khi đóng modal
                        });
                    } else {
                        $('#updateSuccessMessage').text(response.message);
                        $('#updateSuccessModal').modal('show');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updateSuccessMessage').text('Có lỗi xảy ra. Vui lòng thử lại.');
                    $('#updateSuccessModal').modal('show');
                }
            });
        });
    });

    // Hàm hiển thị modal thanh toán thành công
    function showSuccessModal(message, totalPaid, newBalance, customerDetailLink) {
        $('#paymentSuccessMessage').html(message + '<p><a href="' + customerDetailLink + '" class="btn btn-info">Xem chi tiết khách hàng</a></p>');
        $('#paymentSuccessModal').modal('show');
        $('#paymentSuccessModal').on('hidden.bs.modal', function() {
            location.reload(); // Reload trang sau khi đóng modal
        });
    }
</script>
<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .modal-content {
        border-radius: 10px;
    }

    .modal-header {
        background-color: #f4f4f4;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }


    .modal-body {
        font-size: 16px;
        text-align: center;
    }

    .modal-footer .btn-primary {
        background-color: #28a745;
        border: none;
    }
</style>
<?= $this->endSection() ?>