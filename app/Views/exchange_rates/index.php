<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12 bangdonhang">
                            <h3 class="mb-4">#lịch sử thay đổi giá</h3>

                            <a href="/exchange-rates/update-form" class="btn btn-primary mb-3">Cập Nhật Tỷ Giá</a>

                            <?php if (session()->has('success')): ?>
                                <div class="alert alert-success">
                                    <?= session('success') ?>
                                </div>
                            <?php endif; ?>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tỷ Giá</th>
                                        <th>Thời Gian Cập Nhật</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rates as $rate): ?>
                                        <tr>
                                            <td class="text-center"><?= $rate['id'] ?></td>
                                            <td class="text-center"><?= number_format($rate['rate'], 0, ',', '.') ?></td>
                                            <td class="text-center"><?= $rate['updated_at'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>