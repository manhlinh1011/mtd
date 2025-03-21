<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách phiếu xuất của khách hàng <?= $customer['customer_code'] ?> - <?= $customer['fullname'] ?></h3>
                </div>
                <div class="card-body">
                    <!-- Bộ lọc -->
                    <form action="" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="shipping_status" class="form-control">
                                    <option value="">Tất cả trạng thái giao hàng</option>
                                    <option value="pending" <?= $shipping_status === 'pending' ? 'selected' : '' ?>>Chờ giao</option>
                                    <option value="confirmed" <?= $shipping_status === 'confirmed' ? 'selected' : '' ?>>Đã giao</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="payment_status" class="form-control">
                                    <option value="">Tất cả trạng thái thanh toán</option>
                                    <option value="unpaid" <?= $payment_status === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                                    <option value="paid" <?= $payment_status === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Lọc</button>
                            </div>
                        </div>
                    </form>

                    <!-- Bảng danh sách phiếu xuất -->
                    <div class="table-responsive">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="text-muted">
                                    Trang <?= $pager->getCurrentPage() ?>/<?= $pager->getPageCount() ?>
                                    (Tổng số: <?= $pager->getTotal() ?> phiếu xuất)
                                </span>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Mã phiếu</th>
                                    <th class="text-center">Ngày tạo</th>
                                    <th class="text-center">Tổng tiền</th>
                                    <th class="text-center">Trạng thái giao hàng</th>
                                    <th class="text-center">Trạng thái thanh toán</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td class="text-center"><a href="/invoices/detail/<?= $invoice['id'] ?>">#<?= $invoice['id'] ?></a></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></td>
                                        <td class="text-center"><?= number_format($invoice['dynamic_total'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                        <td class="text-center">
                                            <span class="badge <?= $invoice['shipping_confirmed_at'] ? 'bg-success' : 'bg-warning' ?>">
                                                <?= $invoice['shipping_confirmed_at'] ? 'Đã giao' : 'Chờ giao' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $invoice['payment_status'] == 'unpaid' ? 'bg-danger' : 'bg-primary' ?>">
                                                <?= $invoice['payment_status'] == 'unpaid' ? 'Chưa thanh toán' : 'Đã thanh toán' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/invoices/detail/<?= $invoice['id'] ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($pager) : ?>
                        <div class="mt-3">
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>