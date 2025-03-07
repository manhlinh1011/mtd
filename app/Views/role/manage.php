<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <h3>#vai trò</h3>
                <p class="sub-header">
                    Chỉ admin mới có quyền thêm sửa xóa nhân viên
                </p>
                <div>
                    <a class="btn btn-primary" href="<?= base_url('user/create') ?>">Thêm chức vụ</a>
                </div>
                <br />
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap dataTable no-footer dtr-inline collapsed" style="border-collapse: collapse; border-spacing: 0px; width: 100%;" role="grid" aria-describedby="datatable_info">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Vai trò</th>
                                        <th>Mô tả</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td class="text-center"><?= $role['id'] ?></td>
                                            <td class=""><?= $role['role_name'] ?></td>
                                            <td><?= $role['description'] ?></td>
                                            <td class="text-center">
                                                <a href="/user/edit/<?= $role['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                                <a href="/user/delete/<?= $role['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                                            </td>
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
</div>
<?= $this->endSection() ?>