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
    <form class="mb-3" action="<?= base_url('/orders') ?>" method="GET">
        <div class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small mb-1" for="customer_code_select">Khách hàng</label>
                <select id="customer_code_select" name="customer_code" class="form-control">
                    <option value="ALL" <?= $customer_code === 'ALL' || empty($customer_code) ? 'selected' : '' ?>>TẤT CẢ KHÁCH HÀNG</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['customer_code'] ?>" <?= $customer['customer_code'] === $customer_code ? 'selected' : '' ?>>
                            <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small mb-1" for="sub_customer_id_select">Mã phụ</label>
                <select id="sub_customer_id_select" name="sub_customer_id" class="form-control">
                    <option value="ALL" <?= $sub_customer_id === 'ALL' || empty($sub_customer_id) ? 'selected' : '' ?>>TẤT CẢ MÃ PHỤ</option>
                    <option value="NONE" <?= $sub_customer_id === 'NONE' ? 'selected' : '' ?>>KHÔNG CÓ MÃ PHỤ</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small mb-1" for="order_code">Mã lô</label>
                <select name="order_code" class="form-control">
                    <option value="">Mã lô</option>
                    <?php foreach ($order_codes as $code): ?>
                        <option value="<?= $code ?>" <?= $order_code === $code ? 'selected' : '' ?>><?= $code ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small mb-1" for="tracking_code_input">Mã vận chuyển</label>
                <input type="text" id="tracking_code_input" name="tracking_code" class="form-control" style="border-color: #007bff;" placeholder="Nhập mã vận chuyển" value="<?= esc($tracking_code ?? '') ?>">
            </div>

        </div>
        <div class="form-row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small mb-1" for="from_date">Từ ngày nhập kho Trung Quốc</label>
                <input type="date" id="from_date" name="from_date" class="form-control" value="<?= esc($from_date ?? '') ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1" for="to_date">Đến ngày nhập kho Trung Quốc</label>
                <input type="date" id="to_date" name="to_date" class="form-control" value="<?= esc($to_date ?? '') ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1" for="vn_from_date">Từ ngày nhập kho Việt Nam</label>
                <input type="date" id="vn_from_date" name="vn_from_date" class="form-control" value="<?= esc($vn_from_date ?? '') ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1" for="vn_to_date">Đến ngày nhập kho Việt Nam</label>
                <input type="date" id="vn_to_date" name="vn_to_date" class="form-control" value="<?= esc($vn_to_date ?? '') ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small mb-1" for="shipping_status">Trạng thái</label>
                <select name="shipping_status" class="form-control">
                    <option value="ALL" <?= $shipping_status === 'ALL' || empty($shipping_status) ? 'selected' : '' ?>>TẤT CẢ TRẠNG THÁI</option>
                    <option value="china_stock" <?= $shipping_status === 'china_stock' ? 'selected' : '' ?>>Kho TQ</option>
                    <option value="in_stock" <?= $shipping_status === 'in_stock' ? 'selected' : '' ?>>Tồn kho</option>
                    <option value="pending_shipping" <?= $shipping_status === 'pending_shipping' ? 'selected' : '' ?>>Chờ giao</option>
                    <option value="shipped" <?= $shipping_status === 'shipped' ? 'selected' : '' ?>>Đã giao</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">Tìm Kiếm</button>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-5">
            <!-- Thống kê đơn hàng -->
            <div class="card mb-4" style="background-color: #f8f9fa;">
                <div class="card-body p-2">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-box">
                                <div class="stat-label">Tổng số</div>
                                <div class="stat-value text-primary"><strong><?= $orderStats['total'] ?></strong></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-box">
                                <div class="stat-label">Kho TQ</div>
                                <div class="stat-value text-info"><strong><?= $orderStats['china_stock'] ?></strong></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-box">
                                <div class="stat-label">Tồn kho</div>
                                <div class="stat-value text-danger"><strong><?= $orderStats['in_stock'] ?></strong></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-box">
                                <div class="stat-label">Chờ giao</div>
                                <div class="stat-value text-warning"><strong><?= $orderStats['pending_shipping'] ?></strong></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-box">
                                <div class="stat-label">Đã giao</div>
                                <div class="stat-value text-success"><strong><?= $orderStats['shipped'] ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <?php if (empty($orders)): ?>
                <div class="alert alert-warning text-center">Không tìm thấy đơn hàng nào.</div>
            <?php endif; ?>
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
                        <?php if (in_array(session('role'), ['Quản lý', 'Kế toán'])): ?>
                            <button type="submit" class="btn btn-success mb-3">
                                <i class="mdi mdi-update mr-1"></i> Cập nhật giá
                            </button>
                        <?php endif; ?>
                        <a href="#" class="btn btn-warning mb-3" data-toggle="modal" data-target="#exportExcelModal">
                            <i class="mdi mdi-file-export mr-1"></i> Xuất Excel Đơn hàng
                        </a>
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
                        <?php if (in_array(session('role'), ['Quản lý', 'Kế toán'])): ?>
                            <a href="<?= base_url('/orders/zero-price') ?>" class="btn btn-outline-warning mb-3">
                                <i class="mdi mdi-alert"></i> Đơn chưa có giá
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <table id="datatable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nhập TQ</th>
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
                            <th>Phí CN</th>
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
                            $phichinhngach = $order['official_quota_fee'] + $order['vat_tax'] + $order['import_tax'] + $order['other_tax'];
                            ?>
                            <tr>
                                <td class="text-center"><?= $order['id'] ?></td>

                                <td class="text-center" style="font-size: 13px; width:160px;">
                                    <span class="badge badge-primary">TQ</span> <?= $order['created_at'] ?><br>
                                    <?php if ($order['vietnam_stock_date']): ?>

                                        <span class="badge badge-success">VN</span> <?= $order['vietnam_stock_date'] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= $order['tracking_code'] ?>
                                    <?php if (!empty($order['notes'])): ?>
                                        <span class="badge badge-info" data-toggle="tooltip" data-placement="top" title="<?= $order['notes'] ?>">
                                            <i class="mdi mdi-pencil"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
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
                                <td class="text-center"><a href="<?= base_url() ?>orders?customer_code=<?= $order['customer_code'] ?>"><?= $order['customer_code'] ?></a><br>
                                    <?php if ($order['sub_customer_code']): ?>
                                        <span style="font-size: 10px;"><?= $order['sub_customer_code'] ?? '-' ?></span>
                                    <?php endif; ?>
                                </td>
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
                                <td class="text-right"><?= number_format($phichinhngach, 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($gianoidia_trung + $gia_cuoi_cung + $phichinhngach, 0, ',', '.') ?></td>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy số lượng giỏ hàng ban đầu khi trang được tải
        fetchCartCount();

        // Xử lý nút thêm vào giỏ hàng
        document.querySelectorAll(".add-to-cart").forEach(function(button) {
            // Kiểm tra nếu đơn hàng đã có trong giỏ hàng khi tải trang
            checkIfOrderInCart(button);

            button.addEventListener("click", async function(e) {
                e.preventDefault();
                const orderId = this.dataset.orderId;

                try {
                    const formData = new FormData();
                    formData.append('order_id', orderId);

                    const response = await fetch('<?= base_url('invoices/cart/add') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(data.message, 'success');
                        updateCartCount(data.cart_count); // Cập nhật số lượng giỏ hàng
                        this.disabled = true;
                        this.classList.add('added-to-cart'); // Thêm class để đánh dấu
                    } else {
                        showToast(data.message, 'danger');
                    }
                } catch (error) {
                    console.error('Lỗi:', error);
                    showToast('Lỗi kết nối. Vui lòng thử lại.', 'danger');
                }
            });
        });

        // Xử lý nút xóa đơn hàng
        document.querySelectorAll(".delete-btn").forEach(function(button) {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const orderId = this.dataset.orderId;
                const invoiceId = this.dataset.invoiceId;
                const modalId = invoiceId === null ? `deleteConfirmModal${orderId}` : `cannotDeleteModal${orderId}`;
                const modal = document.getElementById(modalId);

                if (modal) {
                    // Hiển thị modal bằng Bootstrap
                    $(modal).modal('show');
                }
            });
        });

        // Xử lý nút xác nhận xóa trong modal
        document.querySelectorAll(".confirm-delete").forEach(function(button) {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const orderId = this.dataset.orderId;

                // Đóng modal trước khi chuyển hướng
                const modal = $(this).closest('.modal');
                modal.modal('hide');

                // Chuyển hướng sau khi modal đã đóng
                setTimeout(function() {
                    window.location.href = `<?= base_url('orders/delete') ?>/${orderId}`;
                }, 500);
            });
        });

        // Hàm kiểm tra nếu đơn hàng đã có trong giỏ hàng
        async function checkIfOrderInCart(button) {
            const orderId = button.dataset.orderId;

            try {
                const response = await fetch('<?= base_url('invoices/cart/check') ?>', {
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

                if (data.exists) {
                    button.disabled = true;
                    button.classList.add('added-to-cart');
                }
            } catch (error) {
                console.error('Lỗi khi kiểm tra giỏ hàng:', error);
            }
        }

        // Hàm lấy số lượng giỏ hàng ban đầu
        async function fetchCartCount() {
            try {
                const response = await fetch('<?= base_url('invoices/cart/count') ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    updateCartCount(data.cart_count);
                }
            } catch (error) {
                console.error('Lỗi khi lấy số lượng giỏ hàng:', error);
            }
        }

        // Hàm cập nhật số lượng giỏ hàng trên giao diện
        function updateCartCount(count) {
            const cartCountElement = document.querySelector('#cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
            }
        }
    });
</script>
<script>
    $(document).ready(function() {
        // Lấy giá trị sub_customer_id từ URL
        const urlParams = new URLSearchParams(window.location.search);
        const selectedSubCustomerId = urlParams.get('sub_customer_id') || 'ALL';

        // Xử lý khi customer_code thay đổi
        $('#customer_code_select').change(function() {
            var customer_code = $(this).val();

            // Ẩn dropdown sub_customer nếu chọn "All"
            if (customer_code === 'ALL') {
                $('#sub_customer_filter').hide();
                $('#sub_customer_id_select').val('ALL');
                return;
            }

            // Lấy danh sách sub_customer cho customer này
            fetch('<?= base_url('orders/get-sub-customers-by-code') ?>?customer_code=' + customer_code)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.data && data.data.length > 0) {
                        // Có sub_customers, hiển thị dropdown
                        var subCustomerSelect = $('#sub_customer_id_select');
                        subCustomerSelect.empty();

                        // Thêm option "Tất cả"
                        subCustomerSelect.append('<option value="ALL">TẤT CẢ MÃ PHỤ</option>');

                        // Thêm option "Không có mã phụ"
                        subCustomerSelect.append('<option value="NONE">KHÔNG CÓ MÃ PHỤ</option>');

                        // Thêm các sub_customer
                        data.data.forEach(function(sub) {
                            subCustomerSelect.append('<option value="' + sub.id + '">' + sub.sub_customer_code + ' (' + sub.fullname + ')</option>');
                        });

                        // Hiển thị phần dropdown
                        $('#sub_customer_filter').show();

                        // Tự động chọn giá trị sub_customer_id từ URL
                        if (selectedSubCustomerId) {
                            subCustomerSelect.val(selectedSubCustomerId);
                        }
                    } else {
                        // Không có sub_customers, ẩn dropdown
                        $('#sub_customer_filter').hide();
                        $('#sub_customer_id_select').val('ALL');
                    }
                })
                .catch(error => {
                    console.error('Error fetching sub customers:', error);
                    $('#sub_customer_filter').hide();
                    $('#sub_customer_id_select').val('ALL');
                });
        });

        // Kích hoạt sự kiện change nếu đã có customer_code được chọn
        if ($('#customer_code_select').val() !== 'ALL') {
            $('#customer_code_select').trigger('change');
        }

        // Tự động focus vào ô tracking_code nếu có
        if ($('#tracking_code_input').length) {
            $('#tracking_code_input').focus();
            $('#tracking_code_input').select();
        }
    });
</script>

<!-- Modal Export Excel -->
<div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportExcelModalLabel">
                    <i class="mdi mdi-file-export mr-2"></i>Xuất Excel Đơn hàng
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary" role="status" id="loadingSpinner" style="display: none;">
                        <span class="sr-only">Đang tải...</span>
                    </div>
                </div>

                <div id="orderCountInfo" class="alert alert-info" style="display: none;">
                    <i class="mdi mdi-information mr-2"></i>
                    <span id="orderCountText"></span>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Chọn cách xuất:</label>
                    <div class="mt-3">
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="exportRecent1000" name="exportType" value="recent_1000" class="custom-control-input" checked>
                            <label class="custom-control-label" for="exportRecent1000">
                                <strong>Xuất 1000 đơn hàng gần nhất</strong>
                                <br><small class="text-muted">Phù hợp cho hầu hết trường hợp, tránh quá tải hệ thống</small>
                            </label>
                        </div>

                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="exportAll" name="exportType" value="all" class="custom-control-input">
                            <label class="custom-control-label" for="exportAll">
                                <strong>Xuất tất cả đơn hàng</strong>
                                <br><small class="text-muted">Có thể mất nhiều thời gian nếu có nhiều dữ liệu</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="mdi mdi-alert mr-2"></i>
                    <strong>Lưu ý:</strong> Việc xuất quá nhiều dữ liệu có thể gây chậm hoặc lỗi hệ thống.
                    Khuyến nghị sử dụng bộ lọc để giảm số lượng dữ liệu trước khi xuất.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="mdi mdi-close mr-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-warning" id="exportExcelBtn">
                    <i class="mdi mdi-file-export mr-1"></i>Xuất Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Khi modal mở, kiểm tra số lượng đơn hàng
        $('#exportExcelModal').on('show.bs.modal', function() {
            checkOrderCount();
        });

        // Hàm kiểm tra số lượng đơn hàng
        function checkOrderCount() {
            const loadingSpinner = $('#loadingSpinner');
            const orderCountInfo = $('#orderCountInfo');
            const orderCountText = $('#orderCountText');

            loadingSpinner.show();
            orderCountInfo.hide();

            // Lấy các tham số tìm kiếm hiện tại
            const currentUrl = new URL(window.location);
            const params = new URLSearchParams(currentUrl.search);

            fetch('<?= base_url('orders/get-order-count') ?>?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    loadingSpinner.hide();

                    if (data.success) {
                        const count = data.total_count;
                        orderCountText.text(`Tìm thấy ${count.toLocaleString('vi-VN')} đơn hàng phù hợp với bộ lọc hiện tại.`);
                        orderCountInfo.show();

                        // Cập nhật trạng thái radio button dựa trên số lượng
                        if (count > 1000) {
                            $('#exportRecent1000').prop('checked', true);
                            $('#exportAll').prop('disabled', false);
                        } else {
                            $('#exportAll').prop('checked', true);
                            $('#exportRecent1000').prop('disabled', true);
                        }
                    } else {
                        orderCountText.text('Không thể kiểm tra số lượng đơn hàng.');
                        orderCountInfo.show();
                    }
                })
                .catch(error => {
                    loadingSpinner.hide();
                    orderCountText.text('Lỗi khi kiểm tra số lượng đơn hàng.');
                    orderCountInfo.show();
                    console.error('Error:', error);
                });
        }

        // Cập nhật JS để truyền đủ filter khi export Excel
        $('#exportExcelBtn').click(function() {
            const exportType = $('input[name="exportType"]:checked').val();
            // Lấy toàn bộ filter từ form
            var params = $('form[action$="/orders"]').serialize();
            // Thêm export_type
            if (params.length > 0) {
                params += '&export_type=' + exportType;
            } else {
                params = 'export_type=' + exportType;
            }
            // Tạo URL xuất Excel
            const exportUrl = '<?= base_url('orders/export-excel-by-filter') ?>?' + params;
            // Đóng modal
            $('#exportExcelModal').modal('hide');
            // Hiển thị thông báo
            if (typeof showToast === 'function') showToast('Đang xuất Excel, vui lòng đợi...', 'info');
            // Chuyển hướng để tải file
            window.location.href = exportUrl;
        });
    });
</script>

<?= $this->endSection() ?>
<style>
    .stat-box {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 10px 0 4px 0;
        margin: 2px 0;
    }

    .stat-label {
        font-size: 13px;
        color: #666;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: bold;
    }
</style>