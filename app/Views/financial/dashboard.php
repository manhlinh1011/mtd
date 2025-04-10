<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h1>Tổng hợp tài chính</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title text-white">Tổng thu</h5>
                    <p class="card-text text-white"><?= number_format($totalIncome, 2) ?> VNĐ</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title text-white">Tổng chi</h5>
                    <p class="card-text text-white"><?= number_format($totalExpense, 2) ?> VNĐ</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title text-white">Số dư</h5>
                    <p class="card-text text-white"><?= number_format($balance, 2) ?> VNĐ</p>
                </div>
            </div>
        </div>
    </div>
    <a href="/financial" class="btn btn-primary">Xem danh sách thu chi</a>
</div>

<?= $this->endSection() ?>