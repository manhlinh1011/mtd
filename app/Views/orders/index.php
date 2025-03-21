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
                input.select();
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
                        <div class="col-md-3">
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">Thống kê đơn hàng</div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Tổng số đơn hàng:</span>
                                        <span class="badge bg-primary"><?= $orderStats['total'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Đơn hàng kho TQ:</span>
                                        <span class="badge bg-primary"><?= $orderStats['china_stock'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Đơn hàng tồn kho:</span>
                                        <span class="badge bg-danger"><?= $orderStats['in_stock'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Đơn hàng chờ giao:</span>
                                        <span class="badge bg-warning"><?= $orderStats['pending_shipping'] ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Đơn hàng đã giao:</span>
                                        <span class="badge bg-success"><?= $orderStats['shipped'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                <h3 class="my-1">#đơn hàng</h3>
                                <div class="row">
                                    <div class="col">
                                        <a href="/orders" class="btn btn-dark mb-3">
                                            <i class="mdi mdi-format-list-bulleted mr-1"></i> Danh sách
                                        </a>
                                        <a href="/orders/create" class="btn btn-primary mb-3">
                                            <i class="mdi mdi-plus mr-1"></i> Thêm Đơn hàng
                                        </a>
                                        <button type="submit" class="btn btn-success mb-3">
                                            <i class="mdi mdi-update mr-1"></i> Cập nhật giá
                                        </button>
                                    </div>
                                    <div class="col text-right">
                                        <a href="<?= base_url('/orders/vncheck') ?>" class="btn btn-danger mb-3">
                                            <i class="mdi mdi-warehouse"></i> Kiểm tra kho VN
                                        </a>
                                        <a href="<?= base_url('/orders/import') ?>" class="btn btn-outline-primary mb-3">
                                            <i class="mdi mdi-file-import"></i> Import
                                        </a>
                                        <a href="<?= base_url('/orders/export-vn-today') ?>" class="btn btn-outline-info mb-3">
                                            <i class="mdi mdi-file-export"></i> Export VN Today
                                        </a>
                                    </div>
                                </div>

                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nhập TQ</th>
                                            <th>Nhập VN</th>
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
                                                $formattedDate = $date->format('Y-m-d H:i');
                                                ?>
                                                <td class="text-center"><?= $formattedDate ?></td>
                                                <td class="text-center"><?= $order['vietnam_stock_date'] ?></td>
                                                <td class="text-center"><?= $order['tracking_code'] ?></td>
                                                <td class="text-center"><?= $order['order_code'] ?></td>
                                                <td class="text-center">
                                                    <?php if ($order['package_code']): ?>
                                                        <?php
                                                        $date = DateTime::createFromFormat('Y-m-d H:i:s', $order['created_at']);
                                                        $dateStr = $date->format('Y-m-d');
                                                        $encodedPackageCode = urlencode($order['package_code']);
                                                        ?>
                                                        <a href="<?= base_url("packages/detail/{$encodedPackageCode}/{$dateStr}") ?>"><?= $order['package_code'] ?></a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
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
                                                    <?php if ($order['vietnam_stock_date'] === null): ?>
                                                        <span class="badge bg-primary">Kho TQ</span>
                                                    <?php elseif ($order['invoice_id'] === null): ?>
                                                        <span class="badge bg-danger">Tồn kho</span>
                                                    <?php elseif ($order['shipping_confirmed_at'] !== null): ?>
                                                        <span class="badge bg-success">Đã giao</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Chờ giao</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center" style="padding: 2px; width: 120px;">
                                                    <!-- Nút Thêm vào giỏ hàng -->
                                                    <?php if ($order['invoice_id'] === null): ?>
                                                        <?php if ($order['vietnam_stock_date'] === null): ?>
                                                            <button class="btn btn-success btn-sm add-to-cart" style="padding: 2px 8px; color: #fff;"
                                                                data-order-id="<?= $order['id'] ?>"
                                                                data-tracking-code="<?= $order['tracking_code'] ?>"
                                                                data-toggle="modal"
                                                                data-target="#notInVietnamModal<?= $order['id'] ?>">
                                                                <i class="mdi mdi-cart"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <a class="btn btn-success btn-sm add-to-cart" style="padding: 2px 8px; color: #fff;"
                                                                data-order-id="<?= $order['id'] ?>">
                                                                <i class="mdi mdi-cart"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <!-- Nút Chỉnh sửa -->
                                                    <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-warning btn-sm" style="padding: 2px 8px;">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>

                                                    <!-- Nút Xóa -->
                                                    <button class="btn btn-danger btn-sm delete-btn" style="padding: 2px 8px;"
                                                        data-order-id="<?= $order['id'] ?>"
                                                        data-invoice-id="<?= $order['invoice_id'] ?>"
                                                        data-tracking-code="<?= $order['tracking_code'] ?>"
                                                        data-toggle="modal"
                                                        data-target="#<?= $order['invoice_id'] === null ? 'deleteConfirmModal' : 'cannotDeleteModal' ?><?= $order['id'] ?>">
                                                        <i class="mdi mdi-trash-can"></i>
                                                    </button>

                                                    <!-- Modal thông báo đơn hàng chưa về kho VN -->
                                                    <?php if ($order['vietnam_stock_date'] === null): ?>
                                                        <div class="modal fade" id="notInVietnamModal<?= $order['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="notInVietnamModalLabel<?= $order['id'] ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="notInVietnamModalLabel<?= $order['id'] ?>">Không thể thêm vào giỏ hàng</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Đơn hàng #<?= $order['id'] ?> (<strong><?= $order['tracking_code'] ?></strong>) chưa về kho Việt Nam. Vui lòng đợi đơn hàng về kho trước khi thêm vào giỏ hàng.
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Modal xác nhận xóa -->
                                                    <?php if ($order['invoice_id'] === null): ?>
                                                        <div class="modal fade" id="deleteConfirmModal<?= $order['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel<?= $order['id'] ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteConfirmModalLabel<?= $order['id'] ?>">Xác nhận xóa</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Bạn có chắc chắn muốn xóa đơn hàng #<?= $order['id'] ?> (<strong><?= $order['tracking_code'] ?></strong>) không?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                                        <button type="button" class="btn btn-danger confirm-delete" data-order-id="<?= $order['id'] ?>">OK</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Modal thông báo không được phép xóa -->
                                                    <?php if ($order['invoice_id'] !== null): ?>
                                                        <div class="modal fade" id="cannotDeleteModal<?= $order['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="cannotDeleteModalLabel<?= $order['id'] ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="cannotDeleteModalLabel<?= $order['id'] ?>">Không thể xóa</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Đơn hàng #<?= $order['id'] ?> (<strong><?= $order['tracking_code'] ?></strong>) đã tồn tại trong phiếu xuất <a href="<?= base_url("invoices/detail/{$order['invoice_id']}") ?>" target="_blank">#<?= $order['invoice_id'] ?></a>. Bạn không được phép xóa.
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
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
        // Xử lý nút thêm vào giỏ hàng
        document.querySelectorAll(".add-to-cart").forEach(function(button) {
            button.addEventListener("click", async function(e) {
                e.preventDefault();
                const orderId = this.dataset.orderId;
                const trackingCode = this.dataset.trackingCode;

                // Nếu đơn hàng chưa về kho VN, hiển thị modal
                if (trackingCode) {
                    $(`#notInVietnamModal${orderId}`).modal('show');
                    return;
                }

                try {
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
                        showToast('Sản phẩm đã được thêm vào giỏ hàng!', 'success');
                        updateCartCount(data.cart_count);
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

        // Xử lý nút xóa
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const invoiceId = this.dataset.invoiceId;
                const orderId = this.dataset.orderId;

                if (invoiceId === '' || invoiceId === null) {
                    $(`#deleteConfirmModal${orderId}`).modal('show');
                } else {
                    $(`#cannotDeleteModal${orderId}`).modal('show');
                }
            });
        });

        // Xử lý nút xác nhận xóa
        document.querySelectorAll('.confirm-delete').forEach(function(button) {
            button.addEventListener('click', async function() {
                const orderId = this.dataset.orderId;

                try {
                    const response = await fetch(`/orders/delete/${orderId}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>',
                        },
                    });

                    if (response.redirected) {
                        $(`#deleteConfirmModal${orderId}`).modal('hide');
                        window.location.href = response.url;
                    } else {
                        const data = await response.json();
                        showToast(data.message, 'danger');
                        $(`#deleteConfirmModal${orderId}`).modal('hide');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Lỗi kết nối. Vui lòng thử lại.', 'danger');
                    $(`#deleteConfirmModal${orderId}`).modal('hide');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>