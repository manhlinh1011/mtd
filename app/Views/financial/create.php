<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3>Tạo phiếu thu/chi</h3>
            <div class="alert alert-warning">
                <strong>Lưu ý:</strong> Phiếu chi cần qua phê duyệt của quản lý.
            </div>
            <form method="post" action="<?= base_url('financial/store') ?>">
                <div class="form-group">
                    <label>Loại phiếu</label>
                    <select name="type" class="form-control">
                        <option value="income">Thu</option>
                        <option value="expense">Chi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số tiền</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>