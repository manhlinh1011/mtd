<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <h3>#gán quyền</h3>
                </div>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <!-- Form chọn vai trò -->
                <form method="POST" action="<?= base_url('/permissions/assign') ?>">
                    <div class="row">
                        <!-- Cột 1: Danh sách vai trò -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <h5>Vai trò:</h5>
                                <select name="role_id" id="role" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- Chọn vai trò --</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>" <?= ($selectedRole == $role['id']) ? 'selected' : '' ?>>
                                            <?= $role['role_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <h5>Danh sách quyền:</h5>
                            <div>
                                <?php foreach ($permissions as $permission): ?>
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            name="permissions[]"
                                            value="<?= $permission['id'] ?>"
                                            id="perm_<?= $permission['id'] ?>"
                                            <?= in_array($permission['id'], $assignedPermissions) ? 'checked' : '' ?>>
                                        <div class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                            <b><?= $permission['permission_name'] ?></b> (<?= $permission['description'] ?>)
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" formaction="<?= base_url('/permissions/saveAssignedPermissions') ?>" class="btn btn-primary">
                                    Set Quyền
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>