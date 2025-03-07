<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <h5 class="font-14">Danh sách nhân viên</h5>
                <div class="alert alert-warning">
                    Chỉ admin mới có quyền thêm sửa xóa nhân viên <br />
                    Hạn chế xóa nhân viên để tránh rủi ro
                </div>
                <div>
                    <a class="btn btn-primary" href="<?= base_url('user/create') ?>">Thêm nhân viên</a>
                </div>
                <br />
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php if (session()->has('success')): ?>
                                <div class="alert alert-success"><?= session('success') ?></div>
                            <?php endif; ?>

                            <?php if (session()->has('error')): ?>
                                <div class="alert alert-danger"><?= session('error') ?></div>
                            <?php endif; ?>
                            <table id="datatable" class="table table-bordered dt-responsive nowrap dataTable no-footer dtr-inline collapsed" style="border-collapse: collapse; border-spacing: 0px; width: 100%;" role="grid" aria-describedby="datatable_info">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Họ và tên</th>
                                        <th>Email</th>
                                        <th>Vai trò</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="text-center"><?= $user['id'] ?></td>
                                            <td class="text-center"><?= $user['username'] ?></td>
                                            <td class="text-center"><?= $user['fullname'] ?></td>
                                            <td class="text-center"><?= $user['email'] ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($user['roles'])): ?>
                                                    <?= implode(', ', array_map(fn($role) => $role['role_name'], $user['roles'])) ?>
                                                <?php else: ?>
                                                    Không có vai trò
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center">
                                                <a href="/user/edit/<?= $user['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                                <a href="/user/delete/<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
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