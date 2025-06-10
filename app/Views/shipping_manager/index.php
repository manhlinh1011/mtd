<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                    <div class="card-tools">
                        <a href="<?= base_url('shipping-manager/delivered') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-check-circle"></i> Đã giao
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <form action="<?= base_url('shipping-manager/search') ?>" method="get" class="form-inline">
                            <div class="input-group">
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm theo tên người nhận, số điện thoại hoặc mã vận đơn..." value="<?= isset($keyword) ? $keyword : '' ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Ngày tạo</th>
                                    <th class="text-center">Mã phiếu xuất</th>
                                    <th class="text-center">Khách hàng</th>
                                    <th class="text-center">Người nhận</th>
                                    <th class="text-center">Đơn vị vận chuyển</th>
                                    <th class="text-center">Mã vận đơn</th>
                                    <th class="text-center">Phí vận chuyển</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center">Nhân viên tạo</th>
                                    <th class="text-center">Người xác nhận</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($shippings)) : ?>
                                    <tr>
                                        <td colspan="12" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($shippings as $shipping) : ?>
                                        <tr>
                                            <td class="text-center"><?= $shipping['id'] ?></td>
                                            <td class="text-center"><?= date('d/m/Y H:i', strtotime($shipping['created_at'])) ?></td>
                                            <td class="text-center"><a target="_blank" href="<?= base_url('invoices/detail/' . $shipping['invoice']['id']) ?>">#<?= $shipping['invoice']['id'] ?? '' ?></a></td>
                                            <td class="text-center" style="color:rgb(26, 26, 27);">
                                                <?= esc($shipping['customer']['customer_code'] ?? '') ?>
                                                <?php if ($shipping['sub_customer_id']): ?>
                                                    <br>
                                                    <span style="font-size: 12px; color:rgb(163, 170, 177);"><?= esc($shipping['sub_customer']['customer_code'] ?? '') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-left">
                                                <strong><?= esc($shipping['receiver_name']) ?></strong><br>
                                                <?= esc($shipping['receiver_phone']) ?><br>
                                                <?= esc($shipping['receiver_address']) ?>
                                                <?php if ($shipping['notes']): ?>
                                                    <br>
                                                    <i style="font-size: 12px; color:rgb(248, 106, 11);">Ghi chú: <?= esc($shipping['notes']) ?></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= esc($shipping['shipping_provider']['name'] ?? '') ?></td>
                                            <td class="text-center"><?= esc($shipping['tracking_number']) ?></td>
                                            <td class="text-center"><?= number_format($shipping['shipping_fee'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <?php if ($shipping['status'] === 'pending'): ?>
                                                    <span class="badge badge-warning">Chờ xác nhận</span>
                                                <?php elseif ($shipping['status'] === 'delivered'): ?>
                                                    <span class="badge badge-success">Đã giao</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= esc($shipping['creator']['fullname'] ?? '') ?></td>
                                            <td class="text-center"><?= esc($shipping['confirmer']['fullname'] ?? '') ?></td>
                                            <td class="text-center">
                                                <?php if ($shipping['status'] === 'pending'): ?>
                                                    <a href="#" class="btn btn-success btn-sm confirm-btn"
                                                        data-toggle="modal"
                                                        data-target="#confirmDeliveryModal"
                                                        data-id="<?= $shipping['id'] ?>"
                                                        data-receiver_name="<?= esc($shipping['receiver_name']) ?>"
                                                        data-receiver_phone="<?= esc($shipping['receiver_phone']) ?>"
                                                        data-receiver_address="<?= esc($shipping['receiver_address']) ?>"
                                                        data-shipping_provider_id="<?= $shipping['shipping_provider_id'] ?>"
                                                        data-tracking_number="<?= esc($shipping['tracking_number']) ?>"
                                                        data-shipping_fee="<?= esc($shipping['shipping_fee']) ?>">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
        </div>
    </div>
</div>

<!-- Confirm Delivery Modal -->
<div class="modal fade" id="confirmDeliveryModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeliveryModalLabel">Xác nhận giao hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="confirmDeliveryForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="shipping_id" id="modal_shipping_id">

                    <!-- CSRF token -->
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

                    <div class="form-group">
                        <label for="modal_receiver_name">Tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_receiver_name" name="receiver_name" required>
                    </div>
                    <div class="form-group">
                        <label for="modal_receiver_phone">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_receiver_phone" name="receiver_phone" required>
                    </div>
                    <div class="form-group">
                        <label for="modal_receiver_address">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_receiver_address" name="receiver_address" rows="2" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="modal_shipping_provider_id">Đơn vị vận chuyển <span class="text-danger">*</span></label>
                        <select class="form-control" id="modal_shipping_provider_id" name="shipping_provider_id" required>
                            <option value="">-- Chọn đơn vị vận chuyển --</option>
                            <?php foreach ($providers as $provider) : ?>
                                <option value="<?= $provider['id'] ?>"><?= esc($provider['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_tracking_number">Mã vận đơn</label>
                        <input type="text" class="form-control" id="modal_tracking_number" name="tracking_number">
                    </div>

                    <div class="form-group">
                        <label for="modal_shipping_fee">Phí vận chuyển (VNĐ)</label>
                        <input type="number" class="form-control" id="modal_shipping_fee" name="shipping_fee" value="0">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Xác nhận đã giao</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Khi modal được hiển thị
        $('#confirmDeliveryModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Nút đã kích hoạt modal
            var shippingId = button.data('id');
            var receiverName = button.data('receiver_name');
            var receiverPhone = button.data('receiver_phone');
            var receiverAddress = button.data('receiver_address');
            var shippingProviderId = button.data('shipping_provider_id');
            var trackingNumber = button.data('tracking_number');
            var shippingFee = button.data('shipping_fee');

            var modal = $(this);
            modal.find('#modal_shipping_id').val(shippingId);
            modal.find('#modal_receiver_name').val(receiverName);
            modal.find('#modal_receiver_phone').val(receiverPhone);
            modal.find('#modal_receiver_address').val(receiverAddress);
            modal.find('#modal_shipping_provider_id').val(shippingProviderId);
            modal.find('#modal_tracking_number').val(trackingNumber);
            modal.find('#modal_shipping_fee').val(shippingFee);

            // Cập nhật action của form
            $('#confirmDeliveryForm').attr('action', '<?= base_url('shipping-manager/confirm/') ?>' + shippingId);
        });

        $('#confirmDeliveryForm').submit(function(e) {
            e.preventDefault(); // Ngăn chặn submit form truyền thống

            var form = $(this);
            var url = form.attr('action');
            var formData = form.serialize();

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: "json", // Mong đợi phản hồi dạng JSON
                success: function(response) {
                    if (response.success) {
                        // Hiển thị thông báo thành công
                        alert(response.message);
                        // Đóng modal
                        $('#confirmDeliveryModal').modal('hide');
                        // Tải lại trang
                        location.reload();
                    } else {
                        // Hiển thị thông báo lỗi
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Log chi tiết lỗi để debug
                    console.error('Lỗi AJAX:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    // Hiển thị thông báo lỗi cho người dùng
                    alert('Đã xảy ra lỗi khi gửi yêu cầu: ' + error + '\nChi tiết: ' + xhr.responseText.substring(0, 500));
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>