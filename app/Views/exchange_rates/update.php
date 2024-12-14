<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12 bangdonhang">
                            <h1 class="mb-4">Cập Nhật Tỷ Giá</h1>

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

                            <form action="/exchange-rates/update" method="POST">
                                <?= csrf_field() ?>
                                <div class="form-group mb-3">
                                    <label for="rate">Tỷ Giá</label>
                                    <input type="text" name="rate" id="rate" class="form-control"
                                        value="<?= isset($latestRate['rate']) ? $latestRate['rate'] : '' ?>"
                                        placeholder="Nhập tỷ giá mới" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Cập Nhật</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>