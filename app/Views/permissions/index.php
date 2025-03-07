<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h1>Danh sách Quyền</h1>
                            <?php if (session()->getFlashdata('success')): ?>
                                <p style="color: green;"><?= session()->getFlashdata('success') ?></p>
                            <?php endif; ?>
                            <div>
                                <a class="btn btn-primary" href="<?= base_url('permissions/create') ?>">Thêm quyền mới</a>
                                <a class="btn btn-success" href="<?= base_url('permissions/assign') ?>">Gán quyền</a>
                            </div>

                            <br />
                            <table id="datatable" class="table table-bordered dt-responsive nowrap no-footer dtr-inline collapsed" style="border-collapse: collapse; border-spacing: 0px; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên quyền</th>
                                        <th>Mô tả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($permissions as $permission): ?>
                                        <tr>
                                            <td><?= $permission['id'] ?></td>
                                            <td><?= $permission['permission_name'] ?></td>
                                            <td><?= $permission['description'] ?></td>
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