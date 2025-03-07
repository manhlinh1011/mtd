<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h1>Thêm Quyền Mới</h1>
                            <form method="post" action="/permissions/store">
                                <?= csrf_field() ?>
                                <label for="permission_name">Tên quyền:</label>
                                <input type="text" name="permission_name" id="permission_name" required>
                                <br>
                                <label for="description">Mô tả:</label>
                                <textarea name="description" id="description"></textarea>
                                <br>
                                <button type="submit">Lưu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>