<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <h5 class="font-14">Danh sách Role</h5>
                <p class="sub-header">
                    Chỉ admin mới có quyền thêm sửa xóa nhân viên
                </p>
                <div>
                    <a class="btn btn-primary" href="<?=base_url('user/create')?>">Thêm chức vụ</a>
                </div>
                <br/>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap dataTable no-footer dtr-inline collapsed" style="border-collapse: collapse; border-spacing: 0px; width: 100%;" role="grid" aria-describedby="datatable_info">
                             <thead>
                                <tr role="row">
                                    <th>ID</th>
                                    <th>Vai trò</th>
                                    <th>Mô tả</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr role="row">
                                <td><?= $role['id'] ?></td>
                                <td><?= $role['role_name'] ?></td>
                                <td><?= $role['description'] ?></td>
                                <td>
                                    <a href="/user/edit/<?= $role['id'] ?>">Sửa</a>
                                    <a href="/user/delete/<?= $role['id'] ?>">Xóa</a>
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