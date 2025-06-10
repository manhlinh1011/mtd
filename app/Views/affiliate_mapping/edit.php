<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <h4 class="m-t-0 header-title">Chỉnh sửa liên kết cộng tác viên</h4>
                <p class="text-muted m-b-30 font-14">
                    Điền đầy đủ thông tin sau
                </p>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('affiliate-mapping/update/' . $mapping['id']) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group row">
                        <label for="aff_id" class="col-sm-2 col-form-label">Cộng tác viên:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="aff_id" name="aff_id" required>
                                <option value="">-- Chọn cộng tác viên --</option>
                                <?php foreach ($affiliates as $affiliate): ?>
                                    <option value="<?= $affiliate['id'] ?>" <?= ($affiliate['id'] == $mapping['aff_id']) ? 'selected' : '' ?>>
                                        <?= $affiliate['fullname'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="customer_id" class="col-sm-2 col-form-label">Khách hàng:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="customer_id" name="customer_id" required>
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>" <?= ($customer['id'] == $mapping['customer_id']) ? 'selected' : '' ?>>
                                        <?= $customer['fullname'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" id="sub_customer_group" style="display: none;">
                        <label for="sub_customer_id" class="col-sm-2 col-form-label">Khách hàng phụ:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="sub_customer_id" name="sub_customer_id">
                                <option value="">-- Chọn khách hàng phụ --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10 offset-sm-2">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Cập nhật</button>
                            <a href="<?= base_url('affiliate-mapping') ?>" class="btn btn-secondary waves-effect m-l-5">Hủy</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerId = document.getElementById('customer_id').value;
        if (customerId) {
            loadSubCustomers(customerId);
        }
    });

    document.getElementById('customer_id').addEventListener('change', function() {
        const customerId = this.value;
        loadSubCustomers(customerId);
    });

    function loadSubCustomers(customerId) {
        const subCustomerGroup = document.getElementById('sub_customer_group');
        const subCustomerSelect = document.getElementById('sub_customer_id');

        // Reset sub customer select
        subCustomerSelect.innerHTML = '<option value="">-- Chọn khách hàng phụ --</option>';

        if (customerId) {
            // Gọi API để lấy danh sách khách hàng phụ
            fetch(`<?= base_url('affiliate-mapping/get-customer-sub-customers/') ?>${customerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        // Nếu có khách hàng phụ, hiển thị select box
                        data.forEach(subCustomer => {
                            const option = document.createElement('option');
                            option.value = subCustomer.id;
                            option.textContent = subCustomer.fullname;
                            // Nếu là khách hàng phụ hiện tại của mapping, chọn nó
                            if (subCustomer.id == <?= $mapping['sub_customer_id'] ?? 'null' ?>) {
                                option.selected = true;
                            }
                            subCustomerSelect.appendChild(option);
                        });
                        subCustomerGroup.style.display = 'flex';
                    } else {
                        // Nếu không có khách hàng phụ, ẩn select box
                        subCustomerGroup.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    subCustomerGroup.style.display = 'none';
                });
        } else {
            // Nếu chưa chọn khách hàng, ẩn select box
            subCustomerGroup.style.display = 'none';
        }
    }
</script>

<?= $this->endSection() ?>