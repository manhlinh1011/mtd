<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <a href="<?= base_url('invoices') ?>" class="btn btn-secondary mb-3">
                <i class="mdi mdi-format-list-bulleted"></i> Tất cả phiếu xuất
            </a>
            <a href="<?= base_url('invoices/pending') ?>" class="btn btn-warning mb-3">
                <i class="mdi mdi-clock"></i> Phiếu xuất đang chờ giao
            </a>
            <a href="<?= base_url('invoices/overdue') ?>" class="btn btn-secondary mb-3">
                <i class="mdi mdi-calendar-alert"></i> Phiếu xuất quá hạn
            </a>

            <h4 class="card-title">Phiếu xuất đang chờ giao</h4>

            <!-- Bộ lọc khách hàng -->
            <form action="<?= base_url('invoices/pending') ?>" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="customer_code" class="form-control" placeholder="Tìm kiếm khách hàng" id="customer-search">
                        <div id="customer-suggestions" class="list-group" style="display: none;"></div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ngày tạo</th>
                            <th>Khách hàng</th>
                            <th>Số đơn hàng</th>
                            <th>Tổng tiền</th>
                            <th>Số ngày chờ giao</th>
                            <th>Người tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td class="text-center"><?= $invoice['id'] ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('invoices/pending/?customer_code=' . $invoice['customer_code']) ?>">
                                        <?= $invoice['customer_code'] ?> - <?= $invoice['customer_name'] ?>
                                    </a>
                                </td>
                                <td class="text-center"><?= $invoice['total_orders'] ?></td>
                                <td class="text-center"><?= number_format($invoice['total_amount'], 0, ',', '.') ?> đ</td>
                                <td class="text-center"><?= $invoice['waiting_days'] ?></td>
                                <td class="text-center"><?= $invoice['creator_name'] ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('invoices/detail/' . $invoice['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Phân trang -->
    <?php if (isset($pager)): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Hiển thị <?= count($invoices) ?> / <?= $total ?> phiếu xuất đang chờ giao
            </div>
            <div>
                <?= $pager->links('default', 'bootstrap_pagination') ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .list-group-item.active {
        background-color: #007bff;
        /* Màu nền khi được chọn */
        color: white;
        /* Màu chữ khi được chọn */
    }
</style>

<script>
    let currentIndex = -1; // Biến để theo dõi chỉ số gợi ý hiện tại

    document.getElementById('customer-search').addEventListener('input', function() {
        const query = this.value;
        const suggestions = document.getElementById('customer-suggestions');

        if (query.length > 0) {
            // Gửi yêu cầu AJAX để tìm kiếm khách hàng theo customer_code
            fetch(`<?= base_url('customers/search') ?>?name=${query}`)
                .then(response => response.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    currentIndex = -1; // Reset chỉ số khi có kết quả mới
                    if (data.length > 0) {
                        data.forEach((customer, index) => {
                            const item = document.createElement('a');
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = `${customer.customer_code} - ${customer.fullname}`;
                            item.onclick = () => {
                                document.getElementById('customer-search').value = customer.customer_code; // Cập nhật giá trị ô tìm kiếm
                                suggestions.style.display = 'none';
                            };
                            item.setAttribute('data-index', index); // Lưu chỉ số vào thuộc tính
                            suggestions.appendChild(item);
                        });
                        suggestions.style.display = 'block';
                    } else {
                        suggestions.style.display = 'none';
                    }
                });
        } else {
            suggestions.style.display = 'none';
        }
    });

    // Thêm sự kiện keydown để xử lý phím xuống và phím lên
    document.getElementById('customer-search').addEventListener('keydown', function(e) {
        const suggestions = document.getElementById('customer-suggestions');
        const items = suggestions.getElementsByClassName('list-group-item');

        if (e.key === 'ArrowDown') {
            currentIndex = (currentIndex + 1) % items.length; // Tăng chỉ số
            highlightSuggestion(items);
        } else if (e.key === 'ArrowUp') {
            currentIndex = (currentIndex - 1 + items.length) % items.length; // Giảm chỉ số
            highlightSuggestion(items);
        } else if (e.key === 'Enter') {
            if (currentIndex >= 0 && currentIndex < items.length) {
                items[currentIndex].click(); // Nhấp vào gợi ý hiện tại
            }
        }
    });

    function highlightSuggestion(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('active'); // Xóa lớp active khỏi tất cả gợi ý
        }
        if (currentIndex >= 0 && currentIndex < items.length) {
            items[currentIndex].classList.add('active'); // Thêm lớp active cho gợi ý hiện tại
        }
    }
</script>

<?= $this->endSection() ?>