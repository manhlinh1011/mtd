<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Đơn hàng chưa có giá</h4>

                    <!-- Thống kê -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-white">Tổng đơn hàng chưa có cả giá cân và giá khối</h5>
                                    <p class="card-text h3 text-white"><?= $stats['total'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Các nút chức năng -->
                    <div class="row mb-3">
                        <div class="col">
                            <a href="/orders" class="btn btn-dark">
                                <i class="mdi mdi-format-list-bulleted mr-1"></i> Quay lại
                            </a>
                            <button type="submit" form="updateZeroPriceForm" class="btn btn-success">
                                <i class="mdi mdi-update mr-1"></i> Cập nhật giá
                            </button>
                        </div>
                    </div>

                    <!-- Bộ lọc giống index.php -->
                    <form action="<?= base_url('/orders/zero-price') ?>" method="GET" class="mb-3">
                        <div class="row">
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
                            <div class="col-md-2" id="sub_customer_filter" style="display: none;">
                                <select id="sub_customer_id_select" name="sub_customer_id" class="form-control">
                                    <option value="ALL" <?= $sub_customer_id === 'ALL' || empty($sub_customer_id) ? 'selected' : '' ?>>
                                        TẤT CẢ MÃ PHỤ
                                    </option>
                                    <option value="NONE" <?= $sub_customer_id === 'NONE' ? 'selected' : '' ?>>
                                        KHÔNG CÓ MÃ PHỤ
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="from_date" name="from_date" class="form-control"
                                    value="<?= esc($from_date ?? '') ?>" placeholder="Từ ngày">
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="to_date" name="to_date" class="form-control"
                                    value="<?= esc($to_date ?? '') ?>" placeholder="Đến ngày">
                            </div>
                            <div class="col-md-2">
                                <select name="order_code" class="form-control">
                                    <option value="">Mã lô</option>
                                    <?php foreach ($order_codes as $code): ?>
                                        <option value="<?= $code ?>" <?= $order_code === $code ? 'selected' : '' ?>>
                                            <?= $code ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="shipping_status" class="form-control">
                                    <option value="ALL" <?= $shipping_status === 'ALL' || empty($shipping_status) ? 'selected' : '' ?>>TẤT CẢ TRẠNG THÁI</option>
                                    <option value="china_stock" <?= $shipping_status === 'china_stock' ? 'selected' : '' ?>>Kho TQ</option>
                                    <option value="in_stock" <?= $shipping_status === 'in_stock' ? 'selected' : '' ?>>Tồn kho</option>
                                    <option value="pending_shipping" <?= $shipping_status === 'pending_shipping' ? 'selected' : '' ?>>Chờ giao</option>
                                    <option value="shipped" <?= $shipping_status === 'shipped' ? 'selected' : '' ?>>Đã giao</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" id="tracking_code_input" name="tracking_code" class="form-control"
                                    placeholder="Nhập mã vận chuyển" value="<?= esc($tracking_code ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary mt-2">Tìm Kiếm</button>
                            </div>
                        </div>
                    </form>
                    <!-- Kết thúc bộ lọc -->

                    <form id="updateZeroPriceForm" action="<?= base_url() ?>/orders/update-bulk" method="POST">
                        <?= csrf_field() ?>
                        <!-- Bảng danh sách -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
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
                                                <span class="badge badge-primary">CN</span> <?= $order['created_at'] ?><br>
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
                                                <a href="<?= base_url("/orders/edit/{$order['id']}") ?>" class="btn btn-sm btn-primary">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Phân trang -->
                        <div class="d-flex justify-content-center mt-3">
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>