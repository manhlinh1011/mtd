<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <h4 class="m-t-0 header-title">Thêm mới bảng giá cộng tác viên</h4>
                <p class="text-muted m-b-30 font-14">
                    Điền đầy đủ thông tin sau
                </p>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('affiliate-pricing/store') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group row">
                        <label for="aff_id" class="col-sm-2 col-form-label">Cộng tác viên:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="aff_id" name="aff_id" required>
                                <option value="">-- Chọn cộng tác viên --</option>
                                <?php foreach ($affiliates as $affiliate): ?>
                                    <option value="<?= $affiliate['id'] ?>"><?= $affiliate['fullname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="order_code" class="col-sm-2 col-form-label">Mã lô:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="order_code" name="order_code" required>
                                <option value="">-- Chọn mã lô --</option>
                                <?php foreach ($orderCodes as $order): ?>
                                    <option value="<?= $order['order_code'] ?>"><?= $order['order_code'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="aff_price_per_kg" class="col-sm-2 col-form-label">Giá/kg:</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="aff_price_per_kg" name="aff_price_per_kg" required min="0">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="aff_price_per_cubic_meter" class="col-sm-2 col-form-label">Giá/m³:</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" id="aff_price_per_cubic_meter" name="aff_price_per_cubic_meter" required min="0">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="start_date" class="col-sm-2 col-form-label">Ngày bắt đầu:</label>
                        <div class="col-sm-4">
                            <input type="date" class="form-control" id="start_date" name="start_date" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <label for="end_date" class="col-sm-2 col-form-label">Ngày kết thúc:</label>
                        <div class="col-sm-4">
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10 offset-sm-2">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Lưu</button>
                            <a href="<?= base_url('affiliate-pricing') ?>" class="btn btn-secondary waves-effect m-l-5">Hủy</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>