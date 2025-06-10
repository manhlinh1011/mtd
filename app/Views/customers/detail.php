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
        </div>
        <div class="col-6">
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
                    </table>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#depositModal">Nạp tiền</button>
                    <a href="/customers/edit/<?= $customer['id'] ?>" class="btn btn-warning">Sửa thông tin</a>
                </div>
            </div>
        </div>
        <div class="col-6">
            <!-- Thống kê đơn hàng và phiếu xuất -->
            <div class="card mb-4">
                <div class="card-header">Thống kê</div>
                <div class="card-body">
                    <h3 class="mt-1">Số dư: <strong class="text-success"><?= number_format($customer['balance'], 0, ',', '.') ?> VNĐ</strong>

                    </h3>
                    <a href="/customers/update-balance/<?= $customer['id'] ?>" class="btn btn-primary btn-sm">Cập nhật số dư</a>
                    <p>Ngày tạo: <strong><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></strong></p>
                    <hr />
                    <div class="row">
                        <!-- Cột trái: Thông tin đơn hàng -->
                        <div class="col-6">
                            <h6>Thông tin đơn hàng</h6>
                            <p>
                                Tổng số đơn hàng: <strong><?= $orderStats['total_orders'] ?></strong><br />
                                Đơn hàng kho Trung Quốc: <strong><?= $orderStats['china_stock'] ?></strong><br />
                                Đơn hàng tồn kho: <strong class="text-danger"><?= $orderStats['in_stock'] ?></strong><br />
                                Đơn hàng chờ giao: <strong class="text-warning"><?= $orderStats['pending_shipping'] ?></strong><br />
                                Đơn hàng đã giao: <strong class="text-success"><?= $orderStats['shipped'] ?></strong>
                            </p>
                        </div>
                        <!-- Cột phải: Thông tin phiếu xuất -->
                        <div class="col-6">
                            <h6>Thông tin phiếu xuất</h6>
                            <p>
                                Tổng phiếu xuất: <strong><?= $invoiceStats['total_invoices'] ?></strong><br />
                                Phiếu xuất đã thanh toán: <strong><?= $invoiceStats['paid_invoices'] ?></strong><br />
                                Phiếu xuất chưa thanh toán: <strong class="text-danger"><?= $invoiceStats['unpaid_invoices'] ?></strong><br />
                                Phiếu xuất chờ giao: <strong class="text-warning"><?= $invoiceStats['pending_invoices'] ?></strong><br />
                                Phiếu xuất đã giao: <strong class="text-success"><?= $invoiceStats['delivered_invoices'] ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($subCustomers) && count($subCustomers) > 0): ?>
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách khách hàng phụ</h3>
                        <p>Tổng số khách hàng phụ: <strong><?= count($subCustomers) ?></strong></p>
                    </div>
                    <div class="card-body" style="height: 500px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Ngày tạo</th>
                                    <th>Mã phụ</th>
                                    <th>Họ và tên</th>
                                    <th>Số điện thoại</th>
                                    <th>Email</th>
                                    <th>Địa chỉ</th>
                                    <th>Số đơn hàng</th>
                                    <th>Số phiếu xuất</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subCustomers as $subCustomer): ?>
                                    <tr>
                                        <td class="text-center"><?= $subCustomer['id'] ?></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($subCustomer['created_at'])) ?></td>
                                        <td class="text-center"><?= $subCustomer['sub_customer_code'] ?></td>
                                        <td class="text-center"><?= $subCustomer['fullname'] ?></td>
                                        <td class="text-center"><?= $subCustomer['phone'] ?></td>
                                        <td class="text-center"><?= $subCustomer['email'] ?></td>
                                        <td class="text-center"><?= $subCustomer['address'] ?></td>
                                        <td class="text-center"><a href="<?= base_url() ?>orders?sub_customer_id=<?= $subCustomer['id'] ?>"><?= $subCustomer['order_count'] ?? 0 ?></a></td>
                                        <td class="text-center"><?= ($subCustomer['paid_invoice_count'] ?? 0) ?>/<?= ($subCustomer['invoice_count'] ?? 0) ?></td>
                                        <td class="text-center"><a href="/customers/sub-edit/<?= $subCustomer['id'] ?>" class="btn btn-primary btn-sm">Xem chi tiết</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-12">
            <?php if (isset($recentInvoices) && count($recentInvoices) > 0): ?>
                <!-- 10 phiếu xuất gần nhất -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">10 phiếu xuất gần nhất</h3>
                        <div class="card-tools">
                            <a href="/customers/invoices/<?= $customer['id'] ?>" class="btn btn-primary btn-sm">Xem tất cả</a>
                        </div>
                    </div>
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
                                        <td class="text-center"><?= number_format($invoice['total_amount'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                        <td class="text-center">
                                            <span class="badge <?= $invoice['shipping_confirmed_at'] === null ? 'bg-warning' : 'bg-success' ?>">
                                                <?= $invoice['shipping_confirmed_at'] === null ? 'Chưa xuất' : 'Đã xuất' ?>
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
                </div>
            <?php endif; ?>
            <!-- Lịch sử giao dịch -->
            <div class="card mb-4">
                <div class="card-header">Lịch sử giao dịch</div>
                <div class="card-body">
                    <p>Tổng số tiền nạp: <strong class="text-success"><?= number_format($totalDeposit, 0, ',', '.') ?> VNĐ</strong> Tổng số tiền thanh toán: <strong class="text-danger"><?= number_format($totalPayment, 0, ',', '.') ?> VNĐ</strong></p>
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
                                    <td class="text-center"><?= esc($transaction['created_by_name'] ?? 'Không rõ') ?></td>
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
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="fund_id">Chọn quỹ nạp <span class="text-danger">*</span></label>
                        <select name="fund_id" id="fund_id" class="form-control" required>
                            <option value="">-- Chọn quỹ --</option>
                            <?php foreach ($funds as $fund): ?>
                                <option value="<?= $fund['id'] ?>">
                                    <?= esc($fund['name']) ?>
                                    <?php if ($fund['account_number']): ?>
                                        (<?= esc($fund['bank_name']) ?> - <?= esc($fund['account_number']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">Số tiền <span class="text-danger">*</span></label>
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