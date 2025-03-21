<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mt-2">Import đơn hàng từ Excel</h5>
                </div>
                <div class="card-body">
                    <!-- Hiển thị thông báo nếu có -->
                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success">
                            <?= session('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Nút tải file mẫu -->
                    <div style="margin-top: 10px;">
                        <a href="<?= base_url('uploads/Import_Mau.xlsx') ?>" class="btn-download" download>
                            <button class="btn btn-secondary">Tải File Mẫu</button>
                        </a>
                    </div>

                    <!-- Form import Excel -->
                    <form action="<?= base_url('orders/preview') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="form-group row">
                            <label for="excel_file" class="col-sm-3 col-form-label text-right">Chọn file Excel</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls, .csv" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">Import</button>
                                <a href="<?= base_url('/orders') ?>" class="btn btn-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>

                    <!-- Hiển thị dữ liệu preview nếu có -->
                    <?php if (isset($displayHeader) && isset($data) && isset($technicalHeader)): ?>
                        <div class="mt-4">
                            <h6>Dữ liệu từ file Excel:</h6>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <?php foreach ($displayHeader as $index => $head): ?>
                                            <th>
                                                <?= esc($head) ?><br>
                                                <small class="text-muted"><?= esc($technicalHeader[$index]) ?></small>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $cell): ?>
                                                <td><?= esc($cell) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <form action="<?= base_url('orders/import') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="temp_file_path" value="<?= $tempFilePath ?>">
                                <button type="submit" class="btn btn-success">Nhập dữ liệu</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>