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
                                <button type="submit" class="btn btn-success mb-3">Cập Nhật</button>
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nhập TQ</th>
                                            <th>Nhập VN</th>
                                            <th>Xuất VN</th>
                                            <th>Mã vận chuyển</th>
                                            <th>Mã lô</th>
                                            <th>Khách hàng</th>
                                            <th>Loại hàng</th>
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
                                                <td class="text-center"><a href="#"><?= $order['customer_code'] ?></a></td>
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
                                                <th class="text-center" style="color: green;">Đã xuất</th>
                                                <td class="text-center" style="padding: 2px;">
                                                    <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-warning btn-sm" style="padding: 2px 8px;">Sửa</a>
                                                    <a href="/orders/delete/<?= $order['id'] ?>" class="btn btn-danger btn-sm" style="padding: 2px 8px;" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
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
        // Định dạng số có dấu . cách phần nghìn khi mất focus
        document.querySelectorAll(".price-input").forEach(function(input) {
            input.addEventListener("focus", function() {
                // Loại bỏ dấu . để nhập liệu dễ dàng hơn
                let rawValue = input.getAttribute("data-raw") || input.value.replace(/\./g, "");
                if (rawValue === "0") {
                    input.select(); // Bôi đen toàn bộ để dễ nhập liệu
                } else {
                    input.value = rawValue; // Hiển thị giá trị không có dấu .
                }
            });

            input.addEventListener("blur", function() {
                let value = parseInt(input.value.replace(/\./g, "")) || 0; // Lấy giá trị số
                input.setAttribute("data-raw", value); // Lưu giá trị raw
                input.value = value.toLocaleString("vi-VN"); // Định dạng lại có dấu .
            });
        });

        document.querySelectorAll(".fee-input").forEach(function(input) {
            input.addEventListener("focus", function() {
                if (input.value === "0.00") {
                    input.select(); // Bôi đen toàn bộ nếu giá trị là 0
                }
            });

            input.addEventListener("blur", function() {
                let value = parseFloat(input.value.replace(",", ".")) || 0; // Lấy giá trị số
                input.value = value.toFixed(2); // Định dạng lại với 2 chữ số thập phân
            });
        });
    });
</script>
<?= $this->endSection() ?>