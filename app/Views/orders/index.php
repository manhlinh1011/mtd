<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy giá trị query `tracking_code` từ URL
        const urlParams = new URLSearchParams(window.location.search);
        const trackingCode = urlParams.get('tracking_code');

        // Nếu có `tracking_code`, focus và chọn toàn bộ giá trị trong input
        if (trackingCode) {
            const input = document.getElementById('tracking_code_input');
            if (input) {
                input.focus();
                input.select(); // Chọn toàn bộ giá trị trong input
            }
        }
    });
</script>



<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <form action="<?= base_url('/orders') ?>" method="GET" class="mb-3">
                        <div class="row">
                            <!-- Lọc theo Mã Khách Hàng -->
                            <div class="col-md-2">
                                <select id="customer_code_select" name="customer_code" class="form-control">
                                    <option value="ALL" <?= $customer_code === 'ALL' || empty($customer_code) ? 'selected' : '' ?>>
                                        TẤT CẢ KHÁCH HÀNG
                                    </option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['customer_code'] ?>"
                                            <?= $customer['customer_code'] === $customer_code ? 'selected' : '' ?>>
                                            <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Lọc theo Thời Gian -->
                            <div class="col-md-2">
                                <input type="date" id="from_date" name="from_date" class="form-control"
                                    value="<?= esc($from_date ?? '') ?>" placeholder="Từ ngày">
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="to_date" name="to_date" class="form-control"
                                    value="<?= esc($to_date ?? '') ?>" placeholder="Đến ngày">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="tracking_code_input" name="tracking_code" class="form-control"
                                    placeholder="Nhập mã vận chuyển" value="<?= esc($tracking_code ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
                            </div>
                        </div>
                    </form>
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-warning text-center">Không tìm thấy đơn hàng nào.</div>
                    <?php endif; ?>
                    <div class="row">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger">
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('success')): ?>
                            <div class="alert alert-success">
                                <?= session('success') ?>
                            </div>
                        <?php endif; ?>
                        <form action="<?= base_url() ?>/orders/update-bulk" method="POST" style="width: 100%;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="current_page" value="<?= isset($_GET['page']) ? $_GET['page'] : 1 ?>" />
                            <div class="col-12 bangdonhang">
                                <h3 class="my-4">#đơn hàng</h3>
                                <a href="/orders" class="btn btn-dark mb-3">Danh sách</a>
                                <a href="/orders/create" class="btn btn-primary mb-3">Thêm Đơn hàng</a>
                                <button type="submit" class="btn btn-success mb-3">Cập nhật giá</button>
                                <!--<a href="<?= base_url('/orders/export') ?>?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-success">Xuất Excel</a>-->
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nhập TQ</th>
                                            <th>Nhập VN</th>
                                            <th>Xuất VN</th>
                                            <th>Mã vận chuyển</th>
                                            <th>Mã lô</th>
                                            <th>Mã bao</th>
                                            <th>Khách hàng</th>
                                            <th>Hàng</th>
                                            <th>SL</th>
                                            <th>Số kg</th>
                                            <th>KT</th>
                                            <th>Khối</th>
                                            <th>Giá kg</th>
                                            <th>Giá Khối</th>
                                            <th>Phí tệ</th>
                                            <th>Tỷ giá</th>
                                            <th>Phí TQ</th>
                                            <th>Tổng</th>
                                            <th>TT</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <?php
                                            $gia_theo_cannang = ($order['total_weight'] * $order['price_per_kg']);
                                            $gia_theo_khoi = ($order['volume'] * $order['price_per_cubic_meter']);
                                            $gia_cuoi_cung = $gia_theo_cannang;
                                            $cach_tinh_gia = "KG";
                                            if ($gia_theo_khoi > $gia_theo_cannang) {
                                                $gia_cuoi_cung = $gia_theo_khoi;
                                                $cach_tinh_gia = "TT";
                                            }
                                            $gianoidia_trung = ($order['domestic_fee'] * $order['exchange_rate']);
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $order['id'] ?></td>
                                                <?php
                                                $dateString = $order['created_at'];
                                                $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
                                                $formattedDate = $date->format('Y-m-d');
                                                ?>
                                                <td class="text-center"><?= $formattedDate ?></td>
                                                <td class="text-center"><?= $order['vietnam_stock_date'] ?></td>
                                                <td class="text-center"><?= $order['export_date'] ?></td>

                                                <td class="text-center"><?= $order['tracking_code'] ?></td>
                                                <td class="text-center"><?= $order['order_code'] ?></td>
                                                <td class="text-center"><?= $order['package_code'] ?></td>
                                                <td class="text-center"><a href="<?= base_url() ?>orders?customer_code=<?= $order['customer_code'] ?>"><?= $order['customer_code'] ?></a></td>
                                                <td class="text-center"><?= $order['product_type_name'] ?></td>
                                                <td class="text-center"><?= $order['quantity'] ?></td>
                                                <td class="text-center"><?= $order['total_weight'] ?></td>
                                                <td class="text-center"><?= $order['length'] ?>x<?= $order['width'] ?>x<?= $order['height'] ?></td>
                                                <td class="text-center"><?= $order['volume'] ?></td>
                                                <td class="text-center">
                                                    <input name="orders[<?= $order['id'] ?>][price_per_kg]" class="price-input" style="width: 60px; text-align: center;"
                                                        type="text"
                                                        value="<?= number_format($order['price_per_kg'], 0, ',', '.') ?>"
                                                        data-raw="<?= $order['price_per_kg'] ?>"
                                                        placeholder="Nhập giá 1kg">
                                                </td>
                                                <td class="text-center">
                                                    <input name="orders[<?= $order['id'] ?>][price_per_cubic_meter]" class="price-input" style="width: 80px; text-align: center;"
                                                        type="text"
                                                        value="<?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?>"
                                                        data-raw="<?= $order['price_per_cubic_meter'] ?>"
                                                        placeholder="Nhập giá 1 mét khối">
                                                </td>
                                                <td class="text-center">
                                                    <input name="orders[<?= $order['id'] ?>][domestic_fee]" class="fee-input" style="width: 50px; text-align: center;"
                                                        type="text"
                                                        value="<?= number_format($order['domestic_fee'], 2, '.', '') ?>"
                                                        placeholder="Nhập phí tệ">
                                                </td>
                                                <td class="text-right"><?= number_format($order['exchange_rate'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($gianoidia_trung, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($gianoidia_trung + $gia_cuoi_cung, 0, ',', '.') ?></td>
                                                <td class="text-right" data-toggle="tooltip" data-placement="top" title="" data-original-title="KG: <?= number_format($gia_theo_cannang, 0, ',', '.') ?> - TT: <?= number_format($gia_theo_khoi, 0, ',', '.') ?>"><?= $cach_tinh_gia ?></td>
                                                <td class="text-center">
                                                    <?php if ($order['invoice_id'] === null): ?>
                                                        <span class="badge badge-danger">Tồn kho</span>
                                                    <?php elseif ($order['invoice_shipping_status'] === 'pending'): ?>
                                                        <span class="badge badge-warning">Đang xuất</span>
                                                    <?php elseif ($order['invoice_shipping_status'] === 'confirmed'): ?>
                                                        <span class="badge badge-success">Đã xuất</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center" style="padding: 2px; width: 120px;">
                                                    <!-- Nút Thêm vào giỏ hàng (chỉ hiển thị nếu order chưa tồn tại trong phiếu xuất) -->
                                                    <?php if ($order['invoice_id'] === null): ?>
                                                        <a class="btn btn-success btn-sm add-to-cart" style="padding: 2px 8px; color: #fff;" data-order-id="<?= $order['id'] ?>">
                                                            <i class="mdi mdi-cart"></i> <!-- Icon giỏ hàng -->
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Nút Chỉnh sửa -->
                                                    <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-warning btn-sm" style="padding: 2px 8px;">
                                                        <i class="mdi mdi-pencil"></i> <!-- Icon bút chì -->
                                                    </a>

                                                    <!-- Nút Xóa (phụ thuộc vào invoice_id) -->
                                                    <?php if ($order['invoice_id'] === null): ?>
                                                        <a href="/orders/delete/<?= $order['id'] ?>" class="btn btn-danger btn-sm delete-btn" style="padding: 2px 8px;" data-toggle="modal" data-target="#deleteConfirmModal<?= $order['id'] ?>">
                                                            <i class="mdi mdi-trash-can"></i> <!-- Icon thùng rác -->
                                                        </a>

                                                        <!-- Modal xác nhận xóa (cho order chưa tồn tại trong phiếu xuất) -->
                                                        <div class="modal fade" id="deleteConfirmModal<?= $order['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel<?= $order['id'] ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteConfirmModalLabel<?= $order['id'] ?>">Xác nhận xóa</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Bạn có chắc chắn muốn xóa đơn hàng #<?= $order['id'] ?>?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                                        <a href="/orders/delete/<?= $order['id'] ?>" class="btn btn-danger">OK</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Popup thông báo không được phép xóa (cho order đã tồn tại trong phiếu xuất) -->
                                                        <a href="#" class="btn btn-danger btn-sm" style="padding: 2px 8px;" data-toggle="modal" data-target="#cannotDeleteModal<?= $order['id'] ?>">
                                                            <i class="mdi mdi-trash-can"></i> <!-- Icon thùng rác -->
                                                        </a>

                                                        <!-- Modal thông báo không được phép xóa -->
                                                        <div class="modal fade" id="cannotDeleteModal<?= $order['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="cannotDeleteModalLabel<?= $order['id'] ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="cannotDeleteModalLabel<?= $order['id'] ?>">Không thể xóa</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Đơn hàng #<?= $order['id'] ?> đã tồn tại trong phiếu xuất <a href="<?= base_url("invoices/detail/{$order['invoice_id']}") ?>" target="_blank">#<?= $order['invoice_id'] ?></a>. Bạn không được phép xóa.
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <!-- Hiển thị phân trang -->
                                <div class="d-flex justify-content-center">
                                    <?= $pager->links('default', 'bootstrap_pagination') ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".add-to-cart").forEach(function(button) {
            button.addEventListener("click", async function() {
                const orderId = this.dataset.orderId;

                try {
                    // Gửi yêu cầu thêm vào giỏ hàng
                    const response = await fetch('<?= base_url('invoices/cart/add') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>',
                        },
                        body: JSON.stringify({
                            order_id: orderId
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Hiển thị Toast Notification
                        showToast('Sản phẩm đã được thêm vào giỏ hàng!', 'success');

                        // Cập nhật số lượng trong badge
                        updateCartCount(data.cart_count);

                        // Vô hiệu hóa nút sau khi thêm
                        this.disabled = true;
                    } else {
                        showToast('Thêm vào giỏ hàng thất bại: ' + data.message, 'danger');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Lỗi kết nối. Vui lòng thử lại.', 'danger');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>