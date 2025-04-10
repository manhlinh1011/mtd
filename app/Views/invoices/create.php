<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Tạo phiếu xuất</h5>
                </div>
                <div class="card-body">
                    <!-- Thông tin khách hàng -->
                    <div class="mb-4">
                        <strong>Mã khách hàng:</strong> <?= esc($customer['customer_code']) ?><br>
                        <strong>Tên khách hàng:</strong> <?= esc($customer['fullname']) ?><br>
                        <strong>Địa chỉ:</strong> <?= esc($customer['address']) ?><br>
                        <strong>Số điện thoại:</strong> <?= esc($customer['phone']) ?><br>
                        <?php if (!empty($orders[0]['sub_customer_code'])): ?>
                            <strong>Mã phụ:</strong> <?= esc($orders[0]['sub_customer_code']) ?><br>
                        <?php endif; ?>
                    </div>
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-warning text-center">Không có đơn hàng nào để tạo phiếu xuất.</div>
                    <?php else: ?>
                        <!-- Danh sách đơn hàng -->
                        <div class="table-responsive">
                            <h5>Danh sách đơn hàng</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Mã vận chuyển</th>
                                        <th>Số lượng</th>
                                        <th>Khối lượng</th>
                                        <th>Kích thước</th>
                                        <th>Khối</th>
                                        <th>Giá/kg</th>
                                        <th>Giá/khối</th>
                                        <th>Phí nội địa</th>
                                        <th>Tính theo</th>
                                        <th>Tổng giá</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $total = 0; ?>
                                    <?php foreach ($orders as $index => $order): ?>
                                        <?php
                                        $priceByWeight = $order['total_weight'] * $order['price_per_kg'];
                                        $priceByVolume = $order['volume'] * $order['price_per_cubic_meter'];
                                        $pricemMethod = ($priceByWeight >= $priceByVolume) ? "Cân nặng" : "Thể tích";
                                        $finalPrice = max($priceByWeight, $priceByVolume) + ($order['domestic_fee'] * $order['exchange_rate']);
                                        $total += $finalPrice;
                                        ?>
                                        <tr data-order-id="<?= esc($order['order_id']) ?>" data-invoice-id="<?= esc($order['invoice_id'] ?? '') ?>">
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td class="text-center"><?= esc($order['tracking_code']) ?></td>
                                            <td class="text-center"><?= esc($order['quantity']) ?></td>
                                            <td class="text-center"><?= esc($order['total_weight']) ?> kg</td>
                                            <td class="text-center"><?= esc($order['length']) ?>x<?= esc($order['width']) ?>x<?= esc($order['height']) ?></td>
                                            <td class="text-center"><?= esc($order['volume']) ?> m³</td>
                                            <td class="text-center"><?= number_format($order['price_per_kg'], 0, ',', '.') ?> VNĐ</td>
                                            <td class="text-center"><?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?> VNĐ</td>
                                            <td class="text-center"><?= number_format($order['domestic_fee'], 2, '.', ',') ?> VNĐ</td>
                                            <td class="text-center"><?= esc($pricemMethod) ?></td>
                                            <td class="text-center"><?= number_format($finalPrice, 0, ',', '.') ?> VNĐ</td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm remove-from-cart" data-order-id="<?= esc($order['order_id']) ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="11">Tổng cộng</th>
                                        <th><?= number_format($total, 0, ',', '.') ?> VNĐ</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Nhập phí giao hàng và phí khác -->
                        <form id="createInvoiceForm" action="<?= base_url('/invoices/store/' . $customer['id']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <label for="shipping_fee">Phí giao hàng:</label>
                                <input type="number" step="1000" class="form-control" id="shipping_fee" name="shipping_fee" value="0" required>
                            </div>
                            <div class="form-group">
                                <label for="other_fee">Phí khác (VNĐ)</label>
                                <input type="number" class="form-control" id="other_fee" name="other_fee" value="0" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">Ghi chú:</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Nhập ghi chú nếu có"></textarea>
                            </div>
                            <!-- Tổng cộng -->
                            <div class="mt-3">
                                <h5><strong>Tổng tiền:</strong> <span id="total_price"><?= number_format($total, 0, ',', '.') ?></span> VNĐ</h5>
                            </div>

                            <input type="hidden" name="order_ids" id="order_ids" value="<?= implode(',', array_column($orders, 'order_id')) ?>">
                            <input type="hidden" name="sub_customer_id" id="sub_customer_id" value="<?= esc($sub_customer_id) ?>">

                            <!-- Nút tạo phiếu -->
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Tạo Phiếu Xuất</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thông báo lỗi -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Thông báo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="errorModalMessage">
                <!-- Nội dung thông báo sẽ được chèn bằng JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Hàm tính tổng tiền
    function updateTotalPrice() {
        const shippingFee = parseFloat(document.getElementById('shipping_fee').value) || 0;
        const otherFee = parseFloat(document.getElementById('other_fee').value) || 0;
        const rows = document.querySelectorAll('tbody tr');
        let totalOrderPrice = 0;
        rows.forEach(row => {
            const finalPriceCell = row.cells[10];
            if (finalPriceCell) {
                totalOrderPrice += parseFloat(finalPriceCell.textContent.replace(/[^0-9,-]+/g, '').replace(',', '')) || 0;
            }
        });
        const totalPrice = totalOrderPrice + shippingFee + otherFee;

        document.getElementById('total_price').textContent = totalPrice.toLocaleString('vi-VN') + ' VNĐ';
    }

    // Lắng nghe sự kiện input trên shipping_fee và other_fee
    document.getElementById('shipping_fee').addEventListener('input', updateTotalPrice);
    document.getElementById('other_fee').addEventListener('input', updateTotalPrice);

    // Hiển thị Modal nếu có lỗi từ server
    <?php if (session()->getFlashdata('modal_error')): ?>
        $(document).ready(function() {
            $('#errorModalMessage').html('<?= session()->getFlashdata('modal_error') ?>');
            $('#errorModal').modal('show');
        });
    <?php endif; ?>

    // Xử lý xóa đơn hàng khỏi giỏ hàng và kiểm tra trước khi submit
    document.addEventListener("DOMContentLoaded", function() {
        // Xử lý xóa đơn hàng
        document.querySelectorAll(".remove-from-cart").forEach(function(button) {
            button.addEventListener("click", function() {
                const orderId = this.dataset.orderId;

                if (!orderId) {
                    alert("Không tìm thấy mã đơn hàng để xóa.");
                    return;
                }

                fetch('<?= base_url('invoices/removeOrderFromCart') ?>/' + orderId, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const row = this.closest('tr');
                            row.remove();

                            const rows = document.querySelectorAll('tbody tr');
                            let newOrderIds = [];
                            rows.forEach(row => {
                                const removeButton = row.querySelector('.remove-from-cart');
                                if (removeButton) {
                                    newOrderIds.push(removeButton.dataset.orderId);
                                }
                            });

                            const totalCell = document.querySelector('tfoot th:nth-child(2)');
                            if (totalCell) {
                                totalCell.textContent = rows.length > 0 ? document.getElementById('total_price').textContent : '0 VNĐ';
                            }
                            const orderIdsInput = document.getElementById('order_ids');
                            if (orderIdsInput) {
                                orderIdsInput.value = newOrderIds.join(',');
                            }
                            updateTotalPrice();

                            if (rows.length === 0) {
                                location.reload();
                            }
                        } else {
                            alert('Xóa khỏi giỏ hàng thất bại: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Đã xảy ra lỗi khi xóa khỏi giỏ hàng.');
                    });
            });
        });

        // Kiểm tra trước khi submit form
        document.getElementById('createInvoiceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const rows = document.querySelectorAll('tbody tr');
            if (rows.length === 0) {
                $('#errorModalMessage').html('Giỏ hàng trống. Vui lòng thêm đơn hàng trước khi tạo phiếu xuất.');
                $('#errorModal').modal('show');
                return;
            }

            let hasExistingInvoice = false;
            rows.forEach(row => {
                const invoiceId = row.dataset.invoiceId;
                const trackingCode = row.cells[1] ? row.cells[1].textContent : 'Không xác định';
                if (invoiceId && invoiceId !== '') {
                    const invoiceLink = '<?= base_url('/invoices/detail/') ?>' + invoiceId;
                    $('#errorModalMessage').html(`Đơn hàng <strong>${trackingCode}</strong> đã thuộc phiếu xuất <a href="${invoiceLink}">#${invoiceId}</a>.`);
                    $('#errorModal').modal('show');
                    hasExistingInvoice = true;
                }
            });

            if (!hasExistingInvoice) {
                this.submit();
            }
        });
    });
</script>
<?= $this->endSection() ?>