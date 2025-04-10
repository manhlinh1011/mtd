<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <h4 class="mb-4">#Đơn hàng quá <span class="text-danger"><?= $days ?></span> ngày đã về kho Việt Nam nhưng chưa giao</h4>
                    <!-- Form lọc đơn giản -->
                    <form action="<?= base_url('/orders/vietnam-stock') ?>" method="GET" class="mb-3">
                        <div class="row">
                            <!-- Lọc theo số ngày đã về kho VN -->
                            <div class="col-md-2">
                                <input type="number" name="days" class="form-control" placeholder="Số ngày đã về kho" value="<?= esc($days ?? 4) ?>" min="1">
                            </div>
                            <!-- Lọc theo Mã Khách Hàng -->
                            <div class="col-md-2">
                                <select name="customer_code" class="form-control">
                                    <option value="ALL" <?= ($customer_code ?? 'ALL') === 'ALL' ? 'selected' : '' ?>>TẤT CẢ KHÁCH HÀNG</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['customer_code'] ?>" <?= ($customer_code ?? '') === $customer['customer_code'] ? 'selected' : '' ?>>
                                            <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Lọc</button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($orders)): ?>
                        <div class="alert alert-warning text-center">Không tìm thấy đơn hàng nào đã về kho Việt Nam nhưng chưa giao.</div>
                    <?php endif; ?>

                    <!-- Danh sách đơn hàng -->
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
                        <form action="<?= base_url('/orders/update-bulk') ?>" method="POST" style="width: 100%;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="current_page" value="<?= isset($_GET['page']) ? $_GET['page'] : 1 ?>" />
                            <div class="col-12 bangdonhang">
                                <div class="row">
                                    <div class="col">
                                        <a href="/orders" class="btn btn-dark mb-3">
                                            <i class="mdi mdi-format-list-bulleted mr-1"></i> Danh sách tất cả
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
                                            <th>Số ngày tồn kho</th> <!-- Thêm cột số ngày tồn kho -->
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

                                            // Tính số ngày tồn kho từ vietnam_stock_date đến hiện tại
                                            $vnStockDate = new DateTime($order['vietnam_stock_date']);
                                            $currentDate = new DateTime(); // Lấy ngày hiện tại từ hệ thống
                                            $daysPending = $vnStockDate->diff($currentDate)->days;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $order['id'] ?></td>
                                                <td class="text-center"><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                                                <td class="text-center"><?= date('Y-m-d H:i', strtotime($order['vietnam_stock_date'])) ?></td>
                                                <td class="text-center"><?= $daysPending ?></td> <!-- Hiển thị số ngày tồn kho -->
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
                                                <td class="text-center">
                                                    <a href="<?= base_url('orders/vietnam-stock?customer_code=' . $order['customer_code']) ?>">
                                                        <?= $order['customer_code'] ?>
                                                    </a>
                                                </td>
                                                <td class="text-center"><?= $order['product_type_name'] ?></td>
                                                <td class="text-center"><?= $order['quantity'] ?></td>
                                                <td class="text-center"><?= $order['total_weight'] ?></td>
                                                <td class="text-center"><?= $order['length'] ?>x<?= $order['width'] ?>x<?= $order['height'] ?></td>
                                                <td class="text-center"><?= $order['volume'] ?></td>
                                                <td class="text-center">
                                                    <input name="orders[<?= $order['id'] ?>][price_per_kg]" class="price-input" style="width: 60px; text-align: center;"
                                                        type="text" value="<?= number_format($order['price_per_kg'], 0, ',', '.') ?>"
                                                        data-raw="<?= $order['price_per_kg'] ?>" placeholder="Nhập giá 1kg">
                                                </td>
                                                <td class="text-center">
                                                    <input name="orders[<?= $order['id'] ?>][price_per_cubic_meter]" class="price-input" style="width: 80px; text-align: center;"
                                                        type="text" value="<?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?>"
                                                        data-raw="<?= $order['price_per_cubic_meter'] ?>" placeholder="Nhập giá 1 mét khối">
                                                </td>
                                                <td class="text-center">
                                                    <input name="orders[<?= $order['id'] ?>][domestic_fee]" class="fee-input" style="width: 50px; text-align: center;"
                                                        type="text" value="<?= number_format($order['domestic_fee'], 2, '.', '') ?>"
                                                        placeholder="Nhập phí tệ">
                                                </td>
                                                <td class="text-right"><?= number_format($order['exchange_rate'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($gianoidia_trung, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($gianoidia_trung + $gia_cuoi_cung, 0, ',', '.') ?></td>
                                                <td class="text-right" data-toggle="tooltip" data-placement="top" title="KG: <?= number_format($gia_theo_cannang, 0, ',', '.') ?> - TT: <?= number_format($gia_theo_khoi, 0, ',', '.') ?>">
                                                    <?= $cach_tinh_gia ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger">Tồn kho</span>
                                                </td>
                                                <td class="text-center" style="padding: 2px; width: 120px;">
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
                                                        data-target="#deleteConfirmModal<?= $order['id'] ?>">
                                                        <i class="mdi mdi-trash-can"></i>
                                                    </button>

                                                    <!-- Modal xác nhận xóa -->
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
        // Xử lý nút xóa
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const orderId = this.dataset.orderId;
                $(`#deleteConfirmModal${orderId}`).modal('show');
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