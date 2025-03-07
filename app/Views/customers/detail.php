<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Chi tiết khách hàng: <?= esc($customer['fullname']) ?></h3>

            <!-- Thông báo -->
            <?php if (session()->has('success')): ?>
                <div class="alert alert-success"><?= session('success') ?></div>
            <?php endif; ?>
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif; ?>

            <!-- Thông tin cơ bản -->
            <div class="card mb-4">
                <div class="card-header">Thông tin cơ bản</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Mã khách hàng</th>
                            <td><?= esc($customer['customer_code']) ?></td>
                        </tr>
                        <tr>
                            <th>Họ và tên</th>
                            <td><?= esc($customer['fullname']) ?></td>
                        </tr>
                        <tr>
                            <th>Số điện thoại</th>
                            <td><?= esc($customer['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Địa chỉ</th>
                            <td><?= esc($customer['address']) ?></td>
                        </tr>
                        <tr>
                            <th>Link Zalo</th>
                            <td><?= $customer['zalo_link'] ? '<a href="' . esc($customer['zalo_link']) . '" target="_blank">Zalo</a>' : 'Không có' ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= esc($customer['email']) ?: 'Không có' ?></td>
                        </tr>
                        <tr>
                            <th>Số dư</th>
                            <td><?= number_format($balance, 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    </table>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#depositModal">Nạp tiền</button>
                    <a href="/customers/edit/<?= $customer['id'] ?>" class="btn btn-warning">Sửa thông tin</a>
                </div>
            </div>

            <!-- Thống kê đơn hàng -->
            <div class="card mb-4">
                <div class="card-header">Thống kê đơn hàng</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Tổng số đơn hàng</th>
                            <td><?= $orderStats['total_orders'] ?></td>
                        </tr>
                        <tr>
                            <th>Số đơn tồn kho</th>
                            <td><?= $orderStats['in_stock'] ?></td>
                        </tr>
                        <tr>
                            <th>Số đơn đang xuất</th>
                            <td><?= $orderStats['shipping'] ?></td>
                        </tr>
                        <tr>
                            <th>Số đơn đã xuất</th>
                            <td><?= $orderStats['shipped'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- 10 phiếu thu gần nhất -->
            <div class="card mb-4">
                <div class="card-header">10 phiếu thu gần nhất</div>
                <div class="card-body">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Ngày tạo</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái xuất</th>
                                <th>Thanh toán</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInvoices as $invoice): ?>
                                <tr>
                                    <td class="text-center"><a href="/invoices/detail/<?= $invoice['id'] ?>">#<?= $invoice['id'] ?></a></td>
                                    <td class="text-center"><?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></td>
                                    <td class="text-center"><?= number_format($invoice['dynamic_total'] ?? 0, 0, ',', '.') ?> VNĐ</td> <!-- Sử dụng dynamic_total -->
                                    <td class="text-center">
                                        <!-- Trạng thái xuất hàng -->
                                        <span class="badge <?= $invoice['shipping_status'] == 'pending' ? 'bg-warning' : 'bg-success' ?>">
                                            <?= $invoice['shipping_status'] == 'pending' ? 'Chưa xuất' : 'Đã xuất' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- Trạng thái thanh toán -->
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
            </div>

            <!-- Lịch sử giao dịch -->
            <div class="card mb-4">
                <div class="card-header">Lịch sử giao dịch</div>
                <div class="card-body">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Loại giao dịch</th>
                                <th>Số tiền</th>
                                <th>Ngày giao dịch</th>
                                <th>Nhân viên thực hiện</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td class="text-center"><?= $transaction['id'] ?></td>
                                    <td class="text-center"><?= $transaction['transaction_type'] == 'deposit' ? 'Nạp tiền' : 'Thanh toán' ?></td>
                                    <td class="text-center"><?= number_format($transaction['amount'], 0, ',', '.') ?> VNĐ</td>
                                    <td class="text-center"><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
                                    <td class="text-center"><?= esc($transaction['employee_name'] ?? 'Không rõ') ?></td> <!-- Hiển thị tên nhân viên -->
                                    <td class="text-center"><?= esc($transaction['notes']) ?: 'Không có' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal nạp tiền -->
<div class="modal fade" id="depositModal" tabindex="-1" role="dialog" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/customers/deposit/<?= $customer['id'] ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="depositModalLabel">Nạp tiền cho khách hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">Số tiền</label>
                        <input type="number" name="amount" id="amount" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Ghi chú</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Nạp tiền</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>