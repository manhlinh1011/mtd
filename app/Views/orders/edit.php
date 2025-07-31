<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-3">
            <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mt-2">#<?= $order['id'] ?> - Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
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
                    <form action="<?= base_url('orders/update/' . $order['id']) ?>" method="post" class="form-horizontal">
                        <?= csrf_field() ?>

                        <!-- Mã vận chuyển -->
                        <div class="form-group row">
                            <label for="tracking_code" class="col-sm-3 col-form-label text-right">Mã vận chuyển</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="tracking_code" name="tracking_code" value="<?= $order['tracking_code'] ?>" disabled>
                                <input type="hidden" name="tracking_code" value="<?= $order['tracking_code'] ?>">
                            </div>
                        </div>

                        <!-- Khách hàng (autocomplete) -->
                        <div class="form-group row">
                            <label for="customer_autocomplete" class="col-sm-3 col-form-label text-right">Khách hàng</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="customer_autocomplete" placeholder="Nhập mã hoặc tên khách hàng..." value="<?= $order['customer_code'] ?> (<?= $order['fullname'] ?>)" autocomplete="off" required>
                                <input type="hidden" name="customer_id" id="customer_id" value="<?= $order['customer_id'] ?>">
                                <small id="customer_warning" class="form-text text-danger" style="display:none;"></small>
                            </div>
                        </div>

                        <!-- Mã phụ (autocomplete) -->
                        <div class="form-group row" id="subCustomerRow" style="display: none;">
                            <label for="sub_customer_autocomplete" class="col-sm-3 col-form-label text-right">Mã phụ</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="sub_customer_autocomplete" placeholder="Nhập mã phụ hoặc tên..." value="<?php if (isset($order['sub_customer_code'])): ?><?= $order['sub_customer_code'] ?> (<?= $order['sub_customer_name'] ?>)<?php endif; ?>" autocomplete="off">
                                <input type="hidden" name="sub_customer_id" id="sub_customer_id" value="<?= $order['sub_customer_id'] ?>">
                                <small id="sub_customer_warning" class="form-text text-danger" style="display:none;"></small>
                            </div>
                        </div>

                        <!-- Mã bao -->
                        <div class="form-group row">
                            <label for="package_code" class="col-sm-3 col-form-label text-right">Mã bao</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="package_code" name="package_code" value="<?= $order['package_code'] ?>">
                            </div>
                            <label for="order_code" class="col-sm-2 col-form-label text-right">Mã lô</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="order_code" name="order_code" value="<?= $order['order_code'] ?>">
                            </div>
                        </div>

                        <!-- Số lượng -->
                        <div class="form-group row">
                            <label for="quantity" class="col-sm-3 col-form-label text-right">Số lượng</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $order['quantity'] ?>" required>
                            </div>
                            <label for="product_type_id" class="col-sm-2 col-form-label text-right">Loại hàng</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="product_type_id" name="product_type_id" required>
                                    <?php foreach ($productTypes as $productType): ?>
                                        <option value="<?= $productType['id'] ?>" <?= $productType['id'] == $order['product_type_id'] ? 'selected' : '' ?>>
                                            <?= $productType['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <hr />
                        <div class="form-group row">
                            <label for="official_quota_fee" class="col-sm-3 col-form-label text-right">Phí Chính Ngạch</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="official_quota_fee" name="official_quota_fee" value="<?= number_format($order['official_quota_fee'] ?? 0, 0, ',', '.') ?>" step="0.01" required>
                            </div>
                            <label for="import_tax" class="col-sm-2 col-form-label text-right">Thuế NK</label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control" id="import_tax" name="import_tax" value="<?= number_format($order['import_tax'] ?? 0, 0, ',', '.') ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="vat_tax" class="col-sm-3 col-form-label text-right">Thuế VAT</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="vat_tax" name="vat_tax" value="<?= number_format($order['vat_tax'] ?? 0, 0, ',', '.') ?>" step="0.01" required>
                            </div>
                            <label for="other_tax" class="col-sm-2 col-form-label text-right">Thuế khác</label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control" id="other_tax" name="other_tax" value="<?= number_format($order['other_tax'] ?? 0, 0, ',', '.') ?>">
                            </div>
                        </div>
                        <hr />
                        <!-- Giá -->
                        <div class="form-group row">
                            <label for="domestic_fee" class="col-sm-3 col-form-label text-right">Phí nội địa</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="domestic_fee" name="domestic_fee" value="<?= $order['domestic_fee'] ?? 0 ?>" step="0.01" required>
                            </div>
                            <label for="exchange_rate" class="col-sm-2 col-form-label text-right">Tỷ giá</label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" value="<?= $order['exchange_rate'] ?? 0 ?>">
                            </div>
                        </div>

                        <!-- Cân nặng -->
                        <div class="form-group row">
                            <label for="total_weight" class="col-sm-3 col-form-label text-right">Cân nặng (kg)</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="total_weight" name="total_weight" value="<?= $order['total_weight'] ?>" step="0.01" required>
                            </div>
                            <label for="price_per_kg" class="col-sm-2 col-form-label text-right">Giá kg</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="price_per_kg" name="price_per_kg" value="<?= number_format($order['price_per_kg'] ?? 0, 0, ',', '.') ?>" required>
                            </div>
                        </div>

                        <!-- Thể tích -->
                        <div class="form-group row">
                            <label for="volume" class="col-sm-3 col-form-label text-right">Thể tích (m³)</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="volume" name="volume" value="<?= $order['volume'] ?>" step="0.01" required>
                            </div>
                            <label for="price_per_cubic_meter" class="col-sm-2 col-form-label text-right">Giá khối</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="price_per_cubic_meter" name="price_per_cubic_meter" value="<?= number_format($order['price_per_cubic_meter'] ?? 0, 0, ',', '.') ?>" required>
                            </div>
                        </div>

                        <!-- Kích thước -->
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right">Kích thước (cm)</label>
                            <div class="col-sm-9 d-flex">
                                <input type="number" class="form-control mr-2" name="length" value="<?= $order['length'] ?>" placeholder="Dài (cm)">
                                <input type="number" class="form-control mr-2" name="width" value="<?= $order['width'] ?>" placeholder="Rộng (cm)">
                                <input type="number" class="form-control" name="height" value="<?= $order['height'] ?>" placeholder="Cao (cm)">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="notes" class="col-sm-3 col-form-label text-right">Ghi chú</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="notes" name="notes"><?= $order['notes'] ?></textarea>
                            </div>
                        </div>
                        <!-- Nút lưu -->
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <a href="<?= base_url('/orders') ?>" class="btn btn-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mt-2">Thông tin thêm</h5>
                </div>
                <div class="card-body">
                    <?php if ($orderValueError): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($orderValueError) ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="mt-1">Chi phí: <strong class="text-success"><?= number_format($totalOrderValue, 0, ',', '.') ?> VNĐ</strong></h3>
                    <hr />
                    <!-- Trạng thái giao hàng (Timeline) -->
                    <h6>Trạng thái giao hàng</h6>
                    <?php if (!empty($statusHistory)): ?>
                        <ul class="timeline">
                            <?php foreach ($statusHistory as $status): ?>
                                <li>
                                    <strong><?= date('d/m/Y H:i', strtotime($status['time'])) ?>:</strong>
                                    <?= htmlspecialchars($status['status']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Chưa có thông tin trạng thái.</p>
                    <?php endif; ?>
                    <hr />
                    <!-- Thông tin phiếu xuất -->
                    <h6 class="mt-4">Thông tin phiếu xuất</h6>
                    <?php if ($order['invoice_id'] && $invoiceDetails): ?>

                        Thuộc phiếu xuất:
                        <a href="<?= base_url('invoices/detail/' . $order['invoice_id']) ?>" target="_blank">
                            #<?= $order['invoice_id'] ?>
                        </a>
                        <br />

                        <strong><?= htmlspecialchars($invoiceDetails['creator_name'] ?? 'N/A') ?></strong>
                        tạo phiếu lúc
                        <?= date('d/m/Y H:i', strtotime($invoiceDetails['invoice_created_at'])) ?>
                        <br />
                        <?php if ($invoiceDetails['shipping_status'] === 'confirmed'): ?>

                            <strong><?= htmlspecialchars($invoiceDetails['confirmer_name'] ?? 'N/A') ?></strong>
                            xác nhận giao lúc
                            <?= $invoiceDetails['shipping_confirmed_at'] ? date('d/m/Y H:i', strtotime($invoiceDetails['shipping_confirmed_at'])) : 'N/A' ?>

                        <?php endif; ?>
                        <br />Trạng thái giao:
                        <span class="badge <?= $order['invoice_shipping_status'] === 'confirmed' ? 'badge-success' : 'badge-info' ?>">
                            <?= $order['invoice_shipping_status'] === 'confirmed' ? 'Đã giao' : 'Đang chờ' ?>
                        </span>
                        <br />
                        Trạng thái thanh toán:
                        <span class="badge <?= $order['invoice_payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $order['invoice_payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                        </span>
                        <?php if ($order['invoice_payment_status'] === 'paid' && isset($invoiceDetails['payment_confirmer_name'])): ?>
                            <p>
                                <?= htmlspecialchars($invoiceDetails['payment_confirmer_name'] ?? 'N/A') ?>
                                xác nhận thanh toán lúc
                                <?= $invoiceDetails['payment_date'] ? date('d/m/Y H:i', strtotime($invoiceDetails['payment_date'])) : 'N/A' ?>
                            </p>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>Chưa thuộc phiếu xuất nào.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mt-2">Thiết lập hoa hồng</h5>
                </div>
                <div class="card-body">
                    <!-- Cân nặng -->
                    <form action="<?= base_url('orders/update-affiliate-pricing/' . $order['id']) ?>" method="post">
                        <div class="form-group row">
                            <label for="aff_price_per_kg" class="col-sm-3 col-form-label text-right">Giá Kg</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="aff_price_per_kg" name="aff_price_per_kg" value="<?= $order['aff_price_per_kg'] ?>" step="0.01" required>
                            </div>
                            <label for="aff_price_per_cubic_meter" class="col-sm-2 col-form-label text-right">Giá m³</label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control" id="aff_price_per_cubic_meter" name="aff_price_per_cubic_meter" value="<?= number_format($order['aff_price_per_cubic_meter'], 0, ',', '.') ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS cho timeline -->
<style>
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
        margin-bottom: 0;
    }

    .timeline:before {
        content: "";
        position: absolute;
        top: 0;
        left: 10px;
        width: 2px;
        height: 95%;
        background: #007bff;
    }

    .timeline li {
        margin-bottom: 15px;
        position: relative;
        padding-left: 30px;
    }

    .timeline li:before {
        content: "";
        position: absolute;
        left: 3px;
        top: 5px;
        width: 16px;
        height: 16px;
        background: #007bff;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 5px #007bff;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>
    // Hàm định dạng số với phần nghìn
    function formatCurrency(input) {
        let value = input.value.replace(/[^0-9]/g, ''); // Lọc chỉ giữ số
        if (value === '') value = '0';
        let formattedValue = Number(value).toLocaleString('vi-VN');
        input.value = formattedValue;
    }

    // Áp dụng cho các input giá
    document.addEventListener('DOMContentLoaded', function() {
        const pricePerKgInput = document.getElementById('price_per_kg');
        const pricePerCubicMeterInput = document.getElementById('price_per_cubic_meter');

        // Định dạng giá trị ban đầu
        if (pricePerKgInput) {
            formatCurrency(pricePerKgInput);
        }
        if (pricePerCubicMeterInput) {
            formatCurrency(pricePerCubicMeterInput);
        }

        // Định dạng khi nhập
        if (pricePerKgInput) {
            pricePerKgInput.addEventListener('input', function() {
                formatCurrency(this);
            });
        }
        if (pricePerCubicMeterInput) {
            pricePerCubicMeterInput.addEventListener('input', function() {
                formatCurrency(this);
            });
        }

        // Khi submit form, chuyển về số nguyên để lưu vào database
        document.querySelector('form').addEventListener('submit', function() {
            if (pricePerKgInput) {
                pricePerKgInput.value = pricePerKgInput.value.replace(/\./g, '');
            }
            if (pricePerCubicMeterInput) {
                pricePerCubicMeterInput.value = pricePerCubicMeterInput.value.replace(/\./g, '');
            }
        });
    });
</script>

<script>
    $(function() {
        console.log('Script autocomplete đã chạy');
        // Autocomplete cho khách hàng
        $("#customer_autocomplete").autocomplete({
            minLength: 2,
            source: function(request, response) {
                $.ajax({
                    url: "<?= base_url('orders/search-customers') ?>",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                $("#customer_id").val(ui.item.id);
                if (ui.item.has_sub) {
                    $("#subCustomerRow").show();
                    $("#sub_customer_autocomplete").val('');
                    $("#sub_customer_id").val('');
                } else {
                    $("#subCustomerRow").hide();
                    $("#sub_customer_autocomplete").val('');
                    $("#sub_customer_id").val('');
                }
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $("#customer_id").val('');
                    $("#subCustomerRow").hide();
                    $("#sub_customer_autocomplete").val('');
                    $("#sub_customer_id").val('');
                }
            }
        });

        // Autocomplete cho mã phụ
        $("#sub_customer_autocomplete").autocomplete({
            minLength: 1,
            source: function(request, response) {
                var customerId = $("#customer_id").val();
                if (!customerId) {
                    response([]);
                    return;
                }
                $.ajax({
                    url: "<?= base_url('orders/search-sub-customers') ?>",
                    dataType: "json",
                    data: {
                        term: request.term,
                        customer_id: customerId
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                $("#sub_customer_id").val(ui.item.id);
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $("#sub_customer_id").val('');
                }
            }
        });

        <?php if (!empty($order['sub_customer_id'])): ?>
            $("#subCustomerRow").show();
        <?php endif; ?>

        // Cảnh báo khi blur input khách hàng
        $("#customer_autocomplete").on('blur', function() {
            setTimeout(function() { // Đợi autocomplete xử lý xong
                if (!$("#customer_id").val()) {
                    $("#customer_warning").text("Vui lòng chọn đúng khách hàng từ danh sách gợi ý!").show();
                } else {
                    $("#customer_warning").hide();
                }
            }, 200);
        });

        // Ẩn cảnh báo khi bắt đầu gõ lại
        $("#customer_autocomplete").on('input', function() {
            $("#customer_warning").hide();
        });

        // Cảnh báo khi blur input mã phụ
        $("#sub_customer_autocomplete").on('blur', function() {
            setTimeout(function() {
                if (!$("#sub_customer_id").val() && $("#subCustomerRow").is(":visible")) {
                    $("#sub_customer_warning").text("Vui lòng chọn đúng mã phụ từ danh sách gợi ý!").show();
                } else {
                    $("#sub_customer_warning").hide();
                }
            }, 200);
        });
        // Ẩn cảnh báo khi bắt đầu gõ lại mã phụ
        $("#sub_customer_autocomplete").on('input', function() {
            $("#sub_customer_warning").hide();
        });

        // Tự động select all khi focus/click vào input khách hàng
        $("#customer_autocomplete").on('focus click', function() {
            $(this).select();
        });
        // Tự động select all khi focus/click vào input mã phụ
        $("#sub_customer_autocomplete").on('focus click', function() {
            $(this).select();
        });
    });
</script>
<?= $this->endSection() ?>