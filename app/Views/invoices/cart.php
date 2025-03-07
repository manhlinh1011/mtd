<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h3>#tạo phiếu xuất kho</h3>
    <!-- Form nhập mã vận đơn -->
    <form action="<?= base_url('/invoices/addToCartByTrackingCode') ?>" method="POST" class="mb-3">
        <?= csrf_field() ?>
        <div class="input-group">
            <input type="text" name="tracking_code" class="form-control" placeholder="Nhập mã vận đơn..." required>
            <button type="submit" class="btn btn-primary">Thêm vào giỏ</button>
        </div>
    </form>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (!empty($customerCart)): ?>
        <?php foreach ($customerCart as $customerId => $customerData): ?>
            <div class="customer-section" style="margin-bottom: 30px; margin-top: 20px;">
                <div class="row">
                    <div class="col-md-6">
                        <a href="<?= base_url('invoices/create/' . $customerId) ?>" class="btn btn-primary btn-sm"><i class="mdi mdi-plus"></i> Tạo phiếu
                            - <?= $customerData['customer_code'] ?>
                        </a>
                        - <?= count($customerData['orders']) ?> đơn hàng
                    </div>
                </div>
                <table id="datatable" class="table table-bordered dt-responsive nowrap dataTable no-footer dtr-inline collapsed" style="border-collapse: collapse; border-spacing: 0px; width: 100%;" role="grid" aria-describedby="datatable_info">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã vận chuyển</th>
                            <th>Mã lô</th>
                            <th>SL</th>
                            <th>Số kg</th>
                            <th>Kích thước</th>
                            <th>Khối</th>
                            <th>Giá kg</th>
                            <th>Giá khối</th>
                            <th>Phí tệ</th>
                            <th>Tỷ giá</th>
                            <th>Tổng phí nội địa</th>
                            <th>Giá cuối cùng</th>
                            <th>Phương thức tính giá</th>
                            <th>Ngày tạo</th>
                            <th>Ngày xuất kho</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customerData['orders'] as $order): ?>
                            <tr>
                                <td class="text-center"><?= esc($order['order_id']) ?></td>
                                <td class="text-center"><?= esc($order['tracking_code']) ?></td>
                                <td class="text-center"><?= esc($order['order_code']) ?></td>
                                <td class="text-center"><?= esc($order['quantity']) ?></td>
                                <td class="text-center"><?= esc($order['total_weight']) ?></td>
                                <td class="text-center"><?= esc($order['dimensions']) ?></td>
                                <td class="text-center"><?= esc($order['volume']) ?></td>
                                <td class="text-center"><?= number_format($order['price_per_kg'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($order['domestic_fee'], 2, '.', '') ?></td>
                                <td class="text-center"><?= number_format($order['exchange_rate'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($order['total_domestic_fee'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($order['final_price'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= esc($order['pricing_method']) ?></td>
                                <td class="text-center"><?= esc($order['created_at']) ?></td>
                                <td class="text-center"><?= esc($order['export_date']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm remove-from-cart" data-order-id="<?= esc($order['order_id']) ?>"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Không có đơn hàng nào trong giỏ hàng</p>
    <?php endif; ?>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Xử lý sự kiện "Xóa khỏi giỏ hàng"
        document.querySelectorAll(".remove-from-cart").forEach(function(button) {
            button.addEventListener("click", function() {
                const orderId = this.dataset.orderId; // Lấy order_id từ data attribute

                if (!orderId) {
                    alert("Không tìm thấy mã đơn hàng để xóa.");
                    return;
                }

                // Gửi AJAX để xóa đơn hàng khỏi giỏ hàng
                fetch('<?= base_url('invoices/removeOrderFromCart') ?>/' + orderId, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>' // Thêm CSRF token để bảo mật
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Đã xóa khỏi giỏ hàng!');
                            location.reload(); // Làm mới trang sau khi xóa thành công
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
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Focus vào ô nhập tracking code khi tải trang
        const trackingInput = document.querySelector("input[name='tracking_code']");
        if (trackingInput) {
            trackingInput.focus();
        }
    });
</script>
<?= $this->endSection() ?>