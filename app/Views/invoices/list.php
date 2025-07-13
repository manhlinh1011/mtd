<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <a href="<?= base_url('invoices') ?>" class="btn btn-primary mb-3">
                <i class="mdi mdi-format-list-bulleted"></i> Tất cả phiếu xuất
            </a>
            <a href="<?= base_url('invoices/pending') ?>" class="btn btn-secondary mb-3">
                <i class="mdi mdi-clock"></i> Phiếu xuất đang chờ giao
            </a>
            <a href="<?= base_url('invoices/overdue') ?>" class="btn btn-secondary mb-3">
                <i class="mdi mdi-calendar-alert"></i> Phiếu xuất quá hạn
            </a>
            <h3>Danh sách phiếu xuất</h3>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div>
                <form action="<?= base_url('/invoices') ?>" method="GET" class="mb-3">
                    <div class="row">
                        <!-- Lọc theo Mã Khách Hàng -->
                        <div class="col-md-2">
                            <select name="customer_code" class="form-control">
                                <option value="ALL" <?= $customer_code === 'ALL' || empty($customer_code) ? 'selected' : '' ?>>TẤT CẢ KHÁCH HÀNG</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['customer_code'] ?>" <?= $customer['customer_code'] === $customer_code ? 'selected' : '' ?>>
                                        <?= $customer['customer_code'] ?> (<?= $customer['fullname'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Lọc theo Thời Gian -->
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="<?= esc($from_date ?? '') ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="<?= esc($to_date ?? '') ?>" placeholder="Đến ngày">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="invoice_id" class="form-control" placeholder="Nhập mã phiếu xuất" value="<?= esc($invoice_id ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="tracking_code" class="form-control" placeholder="Nhập mã vận chuyển" value="<?= esc($tracking_code ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
                            <a href="<?= base_url('invoices/export-excel-by-filter') . '?' . http_build_query($queryParams) ?>" class="btn btn-success">
                                <i class="mdi mdi-file-excel mr-1"></i> Xuất Excel
                            </a>
                        </div>
                    </div>
                </form>
                <?php if (empty($invoices)): ?>
                    <div class="alert alert-warning text-center">Không tìm thấy phiếu xuất nào.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <a href="#" class="btn btn-success" id="exportSelected">
                <i class="mdi mdi-file-excel"></i> Xuất Excel (Chọn)
            </a>
        </div>
        <div class="col-6 d-flex justify-content-end">
            <?= $pager->links('default', 'bootstrap_pagination') ?>
        </div>

    </div>
    <div class="row">
        <div class="col-12">
            <!-- Cập nhật phần bảng và phân trang -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" name="select_all" id="selectAll">
                        </th>
                        <th>#</th>
                        <th>Thời Gian Tạo</th>
                        <th>Khách Hàng</th>
                        <th>Mã Phụ</th>
                        <th>Phí Giao Hàng</th>
                        <th>Số Order</th>
                        <th>Số tiền</th>
                        <th>Giao hàng</th>
                        <th>Thao tác</th>
                        <th>Thanh Toán</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="invoice_ids[]" value="<?= $invoice['id'] ?>">
                                </td>
                                <td class="text-center"><?= $invoice['id'] ?></td>
                                <td class="text-center"><?= date('H:i d-m-Y', strtotime($invoice['created_at'])) ?></td>
                                <td class="text-center"><a href="<?= base_url('customers/detail/' . $invoice['customer_id']) ?>" target="_blank"><?= $invoice['customer_code'] ?> (<?= $invoice['fullname'] ?>)</a> </td>
                                <td class="text-center"><?= $invoice['sub_customer_code'] ?? '-' ?></td>
                                <td class="text-center"><?= number_format($invoice['shipping_fee'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= $invoice['order_count'] ?></td>
                                <td class="text-center" style="line-height: 1.2; margin: 0; font-size: 0.9rem;">
                                    <strong><?= number_format($invoice['total_amount'], 0, ',', '.') ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($invoice['has_order_without_price']): ?>
                                        <span class="badge badge-danger" style="background-color: #dc3545; color: #fff;">Chờ sửa giá</span>
                                    <?php elseif ($invoice['shipping_confirmed_at'] === null): ?>
                                        <span class="badge badge-warning">Đang xuất</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Đã xuất</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$invoice['has_shipping_request']): ?>
                                        <?php if ($invoice['has_order_without_price']): ?>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="showPriceWarning()" disabled style="cursor:not-allowed;opacity:0.7;">
                                                <i class="fas fa-truck"></i> Yêu cầu ship
                                            </button>
                                        <?php elseif ($invoice['customer_payment_type'] == 'prepaid' && $invoice['payment_status'] != 'paid'): ?>
                                            <button type="button" class="btn btn-warning btn-sm" onclick="showPaymentWarning()">
                                                <i class="fas fa-truck"></i> Yêu cầu ship
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= base_url('shipping-manager/create/' . $invoice['id']) ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-truck"></i> Yêu cầu ship
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="#" class="view-shipping-details" data-invoice-id="<?= $invoice['id'] ?>" data-status="<?= $invoice['shipping_confirmed_at'] ? 'delivered' : 'pending' ?>">
                                            <?php if ($invoice['shipping_confirmed_at']): ?>
                                                <i class="mdi mdi-check-circle"></i>
                                            <?php else: ?>
                                                <i class="fas fa-truck"></i>
                                            <?php endif; ?>
                                            Chi tiết
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge 
                                        <?php
                                        if ($invoice['payment_status'] === 'paid') {
                                            echo 'bg-success';
                                        } else {
                                            echo 'bg-danger';
                                        }
                                        ?>">
                                        <?php
                                        if ($invoice['payment_status'] === 'paid') {
                                            echo 'Đã thanh toán';
                                        } else {
                                            echo 'Chưa thanh toán';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url("invoices/detail/{$invoice['id']}") ?>" class="btn btn-info btn-sm">Chi Tiết</a>
                                    <a href="<?= base_url("invoices/export-excel/{$invoice['id']}") ?>" class="btn btn-success btn-sm"><i class="mdi mdi-file-excel"></i> Excel</a>
                                    <?php if (in_array(session('role'), ['Quản lý', 'admin']) && $invoice['payment_status'] !== 'paid' && $invoice['shipping_confirmed_at'] === null): ?>
                                        <button class="btn btn-danger btn-sm delete-invoice"
                                            data-invoice-id="<?= $invoice['id'] ?>"
                                            data-invoice-code="<?= $invoice['id'] ?>"
                                            data-toggle="modal"
                                            data-target="#deleteInvoiceModal<?= $invoice['id'] ?>">
                                            <i class="mdi mdi-trash-can"></i> Xóa
                                        </button>

                                        <!-- Modal xác nhận xóa phiếu xuất -->
                                        <div class="modal fade" id="deleteInvoiceModal<?= $invoice['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteInvoiceModalLabel<?= $invoice['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteInvoiceModalLabel<?= $invoice['id'] ?>">Xác nhận xóa phiếu xuất</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Bạn có chắc chắn muốn xóa phiếu xuất #<?= $invoice['id'] ?> của khách hàng <?= $invoice['fullname'] ?> không?
                                                        <div class="alert alert-warning mt-2">
                                                            <i class="mdi mdi-alert"></i> Lưu ý: Hành động này không thể hoàn tác. Tất cả dữ liệu liên quan đến phiếu xuất này sẽ bị xóa.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                        <button type="button" class="btn btn-danger confirm-delete-invoice" data-invoice-id="<?= $invoice['id'] ?>">Xóa</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">Không có phiếu xuất nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Hiển thị phân trang -->
            <div class="d-flex justify-content-center">
                <?= $pager->links('default', 'bootstrap_pagination') ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal thông báo -->
<div class="modal fade" id="noSelectionModal" tabindex="-1" role="dialog" aria-labelledby="noSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noSelectionModalLabel">Thông báo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn chưa chọn phiếu nào để xuất Excel.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal cảnh báo thanh toán -->
<div class="modal fade" id="paymentWarningModal" tabindex="-1" aria-labelledby="paymentWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentWarningModalLabel">Cảnh báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closePaymentWarning()"></button>
            </div>
            <div class="modal-body">
                <p>Khách hàng này phải thanh toán trước khi giao hàng. Vui lòng xác nhận thanh toán trước khi tạo yêu cầu giao hàng.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePaymentWarning()">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết phiếu ship -->
<div class="modal fade" id="shippingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="shippingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shippingDetailsModalLabel">Chi tiết phiếu giao hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Thông tin người nhận</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Họ tên</th>
                                <td id="receiver-name"></td>
                            </tr>
                            <tr>
                                <th>Số điện thoại</th>
                                <td id="receiver-phone"></td>
                            </tr>
                            <tr>
                                <th>Địa chỉ</th>
                                <td id="receiver-address"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Thông tin vận chuyển</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Đơn vị vận chuyển</th>
                                <td id="provider-name"></td>
                            </tr>
                            <tr>
                                <th>Mã tracking</th>
                                <td id="tracking-number"></td>
                            </tr>
                            <tr>
                                <th>Phí vận chuyển</th>
                                <td id="shipping-fee"></td>
                            </tr>
                            <tr>
                                <th>Trạng thái</th>
                                <td id="shipping-status"></td>
                            </tr>
                            <tr>
                                <th>Ghi chú</th>
                                <td id="shipping-notes"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal cảnh báo sửa giá -->
<div class="modal fade" id="priceWarningModal" tabindex="-1" aria-labelledby="priceWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="priceWarningModalLabel">Cảnh báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closePriceWarning()"></button>
            </div>
            <div class="modal-body">
                <p>Phải sửa giá cho tất cả đơn hàng trong phiếu xuất thì mới được phép giao hàng.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePriceWarning()">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
    .view-shipping-details {
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 4px;
        color: #fff;
    }

    .view-shipping-details[data-status="delivered"] {
        background-color: #28a745;
    }

    .view-shipping-details[data-status="pending"] {
        background-color: #fd7e14;
    }

    .view-shipping-details:hover {
        color: #fff;
        text-decoration: none;
        opacity: 0.9;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const invoiceCheckboxes = document.querySelectorAll('input[name="invoice_ids[]"]');
        let lastChecked;

        invoiceCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function(e) {
                if (e.shiftKey && lastChecked) {
                    let inBetween = false;
                    invoiceCheckboxes.forEach(checkbox => {
                        if (checkbox === this || checkbox === lastChecked) {
                            inBetween = !inBetween;
                        }
                        if (inBetween) {
                            checkbox.checked = lastChecked.checked;
                        }
                    });
                }
                lastChecked = this;
            });
        });

        selectAllCheckbox.addEventListener('change', function() {
            invoiceCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Xử lý xuất Excel cho các phiếu đã chọn
        document.getElementById('exportSelected').addEventListener('click', function() {
            const selectedInvoices = Array.from(invoiceCheckboxes).filter(checkbox => checkbox.checked);
            if (selectedInvoices.length === 0) {
                $('#noSelectionModal').modal('show'); // Hiển thị modal nếu không có phiếu nào được chọn
            } else {
                const selectedIds = selectedInvoices.map(checkbox => checkbox.value);
                const queryString = selectedIds.join(',');
                window.location.href = `<?= base_url('invoices/export-excel-by-select') ?>?ids=${queryString}`;
            }
        });

        // Xử lý nút xóa phiếu xuất
        document.querySelectorAll(".delete-invoice").forEach(function(button) {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const invoiceId = this.dataset.invoiceId;
                const modalId = `deleteInvoiceModal${invoiceId}`;
                const modal = document.getElementById(modalId);

                if (modal) {
                    // Hiển thị modal bằng Bootstrap
                    $(modal).modal('show');
                }
            });
        });

        // Xử lý nút xác nhận xóa phiếu xuất trong modal
        document.querySelectorAll(".confirm-delete-invoice").forEach(function(button) {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const invoiceId = this.dataset.invoiceId;

                // Đóng modal trước khi chuyển hướng
                const modal = $(this).closest('.modal');
                modal.modal('hide');

                // Gửi yêu cầu xóa phiếu xuất
                fetch(`<?= base_url('invoices/delete') ?>/${invoiceId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hiển thị thông báo thành công
                            alert(data.message);
                            // Tải lại trang để cập nhật danh sách
                            window.location.reload();
                        } else {
                            // Hiển thị thông báo lỗi
                            alert(data.message || 'Có lỗi xảy ra khi xóa phiếu xuất');
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        alert('Có lỗi xảy ra khi xóa phiếu xuất');
                    });
            });
        });
    });

    let paymentWarningModal;

    function showPaymentWarning() {
        paymentWarningModal = new bootstrap.Modal(document.getElementById('paymentWarningModal'));
        paymentWarningModal.show();
    }

    function closePaymentWarning() {
        if (paymentWarningModal) {
            paymentWarningModal.hide();
        }
    }

    let priceWarningModal;

    function showPriceWarning() {
        priceWarningModal = new bootstrap.Modal(document.getElementById('priceWarningModal'));
        priceWarningModal.show();
    }

    function closePriceWarning() {
        if (priceWarningModal) {
            priceWarningModal.hide();
        }
    }

    $(document).ready(function() {
        $('.view-shipping-details').click(function(e) {
            e.preventDefault();
            var invoiceId = $(this).data('invoice-id');

            // Gọi API lấy thông tin chi tiết
            $.ajax({
                url: '<?= base_url('shipping-manager/get-shipping-details/') ?>' + invoiceId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        // Cập nhật thông tin trong modal
                        $('#receiver-name').text(data.receiver_name);
                        $('#receiver-phone').text(data.receiver_phone);
                        $('#receiver-address').text(data.receiver_address);
                        $('#provider-name').text(data.provider_name || 'Chưa chọn');
                        $('#tracking-number').text(data.tracking_number || 'Chưa có');
                        $('#shipping-fee').text(new Intl.NumberFormat('vi-VN').format(data.shipping_fee) + 'đ');

                        // Cập nhật trạng thái và màu sắc
                        var statusText = data.status === 'delivered' ? 'Đã giao' : 'Đang giao';
                        var statusClass = data.status === 'delivered' ? 'text-success' : 'text-warning';
                        $('#shipping-status').text(statusText).removeClass('text-success text-warning').addClass(statusClass);

                        $('#shipping-notes').text(data.notes || 'Không có');

                        // Hiển thị modal
                        $('#shippingDetailsModal').modal('show');
                    } else {
                        alert('Không thể lấy thông tin chi tiết giao hàng');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi lấy thông tin chi tiết giao hàng');
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>