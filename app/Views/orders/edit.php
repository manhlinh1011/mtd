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

                        <!-- Khách hàng -->
                        <div class="form-group row">
                            <label for="customer_id" class="col-sm-3 col-form-label text-right">Khách hàng</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="customer_id" name="customer_id" required>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $order['customer_id'] ? 'selected' : '' ?>>
                                            <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Mã phụ (Hiện tại với mã phụ của khách hàng được chọn) -->
                        <div class="form-group row" id="subCustomerRow" <?= (!isset($hasSubCustomers) || !$hasSubCustomers) ? 'style="display: none;"' : '' ?>>
                            <label for="sub_customer_id" class="col-sm-3 col-form-label text-right">Mã phụ</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="sub_customer_id" name="sub_customer_id">
                                    <option value="">-- Không chọn mã phụ --</option>
                                    <?php if (isset($subCustomers) && is_array($subCustomers)): ?>
                                        <?php foreach ($subCustomers as $subCustomer): ?>
                                            <option value="<?= $subCustomer['id'] ?>" <?= $order['sub_customer_id'] == $subCustomer['id'] ? 'selected' : '' ?>>
                                                <?= $subCustomer['sub_customer_code'] ?> (<?= $subCustomer['fullname'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
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

                        <!-- Giá -->
                        <div class="form-group row">
                            <label for="domestic_fee" class="col-sm-3 col-form-label text-right">Phí nội địa</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="domestic_fee" name="domestic_fee" value="<?= $order['domestic_fee'] ?>" step="0.01" required>
                            </div>
                            <label for="exchange_rate" class="col-sm-2 col-form-label text-right">Tỷ giá</label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" value="<?= $order['exchange_rate'] ?>">
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
                                <input type="text" class="form-control" id="price_per_kg" name="price_per_kg" value="<?= number_format($order['price_per_kg'], 0, ',', '.') ?>" required>
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
                                <input type="text" class="form-control" id="price_per_cubic_meter" name="price_per_cubic_meter" value="<?= number_format($order['price_per_cubic_meter'], 0, ',', '.') ?>" required>
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
<!-- JavaScript để định dạng số tiền -->
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

        // Xử lý hiển thị và cập nhật mã phụ khi thay đổi khách hàng
        const customerSelect = document.getElementById('customer_id');
        const subCustomerRow = document.getElementById('subCustomerRow');
        const subCustomerSelect = document.getElementById('sub_customer_id');

        // Khi thay đổi khách hàng
        if (customerSelect) {
            customerSelect.addEventListener('change', function() {
                const customerId = this.value;

                // Xóa tất cả các option cũ trừ option đầu tiên
                while (subCustomerSelect.options.length > 1) {
                    subCustomerSelect.remove(1);
                }

                if (customerId) {
                    // Gọi API để lấy danh sách mã phụ
                    fetch('<?= base_url('orders/get-sub-customers') ?>?customer_id=' + customerId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 200 && data.data.length > 0) {
                                // Hiển thị dòng chọn mã phụ
                                subCustomerRow.style.display = 'flex';

                                // Thêm các option mới
                                data.data.forEach(subCustomer => {
                                    const option = document.createElement('option');
                                    option.value = subCustomer.id;
                                    option.textContent = `${subCustomer.sub_customer_code} (${subCustomer.fullname})`;
                                    subCustomerSelect.appendChild(option);
                                });
                            } else {
                                // Ẩn dòng chọn mã phụ nếu không có mã phụ
                                subCustomerRow.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching sub customers:', error);
                            subCustomerRow.style.display = 'none';
                        });
                } else {
                    // Ẩn dòng chọn mã phụ nếu không chọn khách hàng
                    subCustomerRow.style.display = 'none';
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>