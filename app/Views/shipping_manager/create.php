<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                </div>
                <div class="card-body">
                    <?php if (session()->has('errors')) : ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session('errors') as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Thông tin phiếu xuất -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4>Thông tin phiếu xuất #<?= $invoice['id'] ?></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="text-center" style="width: 200px;">Khách hàng</th>
                                        <td><?= esc($invoice['customer_name']) ?></td>
                                        <th class="text-center" style="width: 200px;">Ngày tạo</th>
                                        <td><?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Địa chỉ</th>
                                        <td><?= esc($invoice['customer_address']) ?></td>
                                        <th class="text-center">Số điện thoại</th>
                                        <td><?= esc($invoice['customer_phone']) ?></td>
                                    </tr>
                                    <?php if (!empty($invoice['sub_customer_id'])) : ?>
                                        <tr class="table-info">
                                            <th class="text-center">Khách hàng phụ</th>
                                            <td><?= esc($invoice['sub_customer_name']) ?></td>
                                            <th class="text-center">Số điện thoại</th>
                                            <td><?= esc($invoice['sub_customer_phone']) ?></td>
                                        </tr>
                                        <tr class="table-info">
                                            <th class="text-center">Địa chỉ</th>
                                            <td colspan="3"><?= esc($invoice['sub_customer_address']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Form tạo ship -->
                    <form action="<?= base_url('shipping-manager/store') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                        <input type="hidden" name="customer_id" value="<?= $invoice['customer_id'] ?>">
                        <input type="hidden" name="created_by" value="<?= session()->get('user_id') ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receiver_name">Tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= session('errors.receiver_name') ? 'is-invalid' : '' ?>"
                                        id="receiver_name" name="receiver_name"
                                        value="<?= old('receiver_name', !empty($invoice['sub_customer_id']) ? $invoice['sub_customer_name'] : $invoice['customer_name']) ?>" required>
                                    <?php if (session('errors.receiver_name')) : ?>
                                        <div class="invalid-feedback"><?= session('errors.receiver_name') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receiver_phone">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= session('errors.receiver_phone') ? 'is-invalid' : '' ?>"
                                        id="receiver_phone" name="receiver_phone"
                                        value="<?= old('receiver_phone', !empty($invoice['sub_customer_id']) ? $invoice['sub_customer_phone'] : $invoice['customer_phone']) ?>" required>
                                    <?php if (session('errors.receiver_phone')) : ?>
                                        <div class="invalid-feedback"><?= session('errors.receiver_phone') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="receiver_address">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= session('errors.receiver_address') ? 'is-invalid' : '' ?>"
                                id="receiver_address" name="receiver_address" rows="2" required><?= old('receiver_address', !empty($invoice['sub_customer_id']) ? $invoice['sub_customer_address'] : $invoice['customer_address']) ?></textarea>
                            <?php if (session('errors.receiver_address')) : ?>
                                <div class="invalid-feedback"><?= session('errors.receiver_address') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping_provider_id">
                                        Đơn vị vận chuyển
                                        <a href="<?= base_url('shipping-provider/create') ?>" target="_blank" style="font-size: 0.9em; margin-left: 8px;">
                                            (Thêm đơn vị vận chuyển)
                                        </a>
                                    </label>
                                    <select class="form-control <?= session('errors.shipping_provider_id') ? 'is-invalid' : '' ?>"
                                        id="shipping_provider_id" name="shipping_provider_id">
                                        <option value="">-- Chọn đơn vị vận chuyển --</option>
                                        <?php foreach ($providers as $provider) : ?>
                                            <option value="<?= $provider['id'] ?>" <?= old('shipping_provider_id') == $provider['id'] ? 'selected' : '' ?>>
                                                <?= esc($provider['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (session('errors.shipping_provider_id')) : ?>
                                        <div class="invalid-feedback"><?= session('errors.shipping_provider_id') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tracking_number">Mã vận đơn</label>
                                    <input type="text" class="form-control <?= session('errors.tracking_number') ? 'is-invalid' : '' ?>"
                                        id="tracking_number" name="tracking_number" value="<?= old('tracking_number') ?>">
                                    <?php if (session('errors.tracking_number')) : ?>
                                        <div class="invalid-feedback"><?= session('errors.tracking_number') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="shipping_fee">Phí vận chuyển (VNĐ)</label>
                            <input type="number" class="form-control <?= session('errors.shipping_fee') ? 'is-invalid' : '' ?>"
                                id="shipping_fee" name="shipping_fee" value="<?= old('shipping_fee', 0) ?>">
                            <?php if (session('errors.shipping_fee')) : ?>
                                <div class="invalid-feedback"><?= session('errors.shipping_fee') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="notes">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Tạo giao hàng</button>
                            <a href="<?= base_url('shipping-manager') ?>" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>