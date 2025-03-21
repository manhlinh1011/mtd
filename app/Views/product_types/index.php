<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div>
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="mb-4">#phân loại hàng hóa</h3>
                            <a href="<?= base_url('/product-types/create') ?>" class="btn btn-primary mb-3"><i class="mdi mdi-tag-plus"></i> Thêm loại hàng</a>
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">STT</th>
                                        <th class="text-center">ID</th>
                                        <th>Tên Loại Hàng</th>
                                        <th>Mô Tả</th>
                                        <th class="text-center">Số Order</th>
                                        <th class="text-center">Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $index = 1; ?>
                                    <?php foreach ($product_types as $type): ?>
                                        <tr>
                                            <td class="text-center"><?= $index++ ?></td>
                                            <td class="text-center"><?= $type['id'] ?></td>
                                            <td class="text-center"><?= $type['name'] ?></td>
                                            <td><?= $type['description'] ?></td>
                                            <td class="text-center"><?= $type['total_orders'] ?></td>
                                            <td class="text-center">
                                                <a href="/product-types/edit/<?= $type['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                                <a href="/product-types/delete/<?= $type['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
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
<?= $this->endSection() ?>