<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="container mt-5">
            <h2>Chỉnh sửa đơn hàng</h2>

            <form action="<?= base_url('orders/update/' . $order['id']) ?>" method="post">
                <?= csrf_field() ?>

                <!-- Mã vận chuyển -->
                <div class="form-group">
                    <label for="tracking_code">Mã vận chuyển</label>
                    <input type="text" class="form-control" id="tracking_code" name="tracking_code" value="<?= $order['tracking_code'] ?>" disabled>
                    <input type="hidden" name="tracking_code" value="<?= $order['tracking_code'] ?>">
                </div>


                <!--Mã bao-->
                <div class="form-group">
                    <label for="package_code">Mã bao</label>
                    <input type="text" class="form-control" id="package_code" name="package_code" value="<?= $order['package_code'] ?>" required>
                </div>

                <!-- Khách hàng -->
                <div class="form-group">
                    <label for="customer_id">Khách hàng</label>
                    <select class="form-control" id="customer_id" name="customer_id" required>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $order['customer_id'] ? 'selected' : '' ?>>
                                <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?> )
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Loại hàng -->
                <div class="form-group">
                    <label for="product_type_id">Loại hàng</label>
                    <select class="form-control" id="product_type_id" name="product_type_id" required>
                        <?php foreach ($productTypes as $productType): ?>
                            <option value="<?= $productType['id'] ?>" <?= $productType['id'] == $order['product_type_id'] ? 'selected' : '' ?>>
                                <?= $productType['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Số lượng -->
                <div class="form-group">
                    <label for="quantity">Số lượng</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $order['quantity'] ?>" required>
                </div>

                <!-- Cân nặng -->
                <div class="form-group">
                    <label for="total_weight">Cân nặng (kg)</label>
                    <input type="number" class="form-control" id="total_weight" name="total_weight" value="<?= $order['total_weight'] ?>" step="0.01" required>
                </div>

                <!-- Kích thước -->
                <div class="form-group">
                    <label>Kích thước (cm)</label>
                    <div class="d-flex">
                        <input type="number" class="form-control mr-2" name="length" value="<?= $order['length'] ?>" placeholder="Dài (cm)" required>
                        <input type="number" class="form-control mr-2" name="width" value="<?= $order['width'] ?>" placeholder="Rộng (cm)" required>
                        <input type="number" class="form-control" name="height" value="<?= $order['height'] ?>" placeholder="Cao (cm)" required>
                    </div>
                </div>

                <!-- Giá -->
                <div class="form-group">
                    <label for="price_per_kg">Giá (VNĐ/kg)</label>
                    <input type="number" class="form-control" id="price_per_kg" name="price_per_kg" value="<?= $order['price_per_kg'] ?>">
                </div>

                <div class="form-group">
                    <label for="price_per_cubic_meter">Giá (VNĐ/khối)</label>
                    <input type="number" class="form-control" id="price_per_cubic_meter" name="price_per_cubic_meter" value="<?= $order['price_per_cubic_meter'] ?>">
                </div>

                <!-- Nút lưu -->
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="<?= base_url('/orders') ?>" class="btn btn-secondary">Hủy</a>
            </form>
        </div>

    </div>
    <?= $this->endSection() ?>