<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                </div>
                <div class="card-body">
                    <?php if (session()->has('errors')) : ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session('errors') as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('shipping-provider/update/' . $provider['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="name">Tên đơn vị vận chuyển <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                                id="name" name="name" value="<?= old('name', $provider['name']) ?>" required>
                            <?php if (session('errors.name')) : ?>
                                <div class="invalid-feedback"><?= session('errors.name') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả</label>
                            <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                                id="description" name="description" rows="3"><?= old('description', $provider['description']) ?></textarea>
                            <?php if (session('errors.description')) : ?>
                                <div class="invalid-feedback"><?= session('errors.description') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                            <a href="<?= base_url('shipping-provider') ?>" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>