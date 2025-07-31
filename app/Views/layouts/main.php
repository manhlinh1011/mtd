<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>MTD - Quản Lý</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Responsive bootstrap 4 admin template" name="description">
    <meta content="Coderthemes" name="author">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/css/icons.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/css/app.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url() ?>assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">

    <link href="<?= base_url('assets/libs/datatables/dataTables.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/datatables/buttons.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/datatables/responsive.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/datatables/select.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/bootstrap-datepicker/bootstrap-datepicker.css') ?>" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/4.1.5/css/flag-icons.min.css">
    <!-- Thêm jQuery -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        a,
        p,
        b,
        strong,
        h1,
        h2,
        h3,
        h4,
        h5,
        label {
            font-family: 'Arial', sans-serif !important;
        }
    </style>
    <?= $this->renderSection('styles') ?>
    <!-- Custom Scripts -->


    <script>
        // Hiển thị Toast Notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');

            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                    </div>
                </div>
            `;

            // Thêm thông báo vào container
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            // Tự động xóa thông báo sau 3 giây
            setTimeout(() => {
                const toastElement = toastContainer.querySelector('.toast');
                if (toastElement) {
                    toastElement.remove();
                }
            }, 3000);
        }

        // Cập nhật số lượng giỏ hàng
        function updateCartCount(newCount) {
            const cartCountElement = document.getElementById('cart-count');
            cartCountElement.textContent = newCount;
        }
    </script>
</head>

<body>
    <div id="wrapper">
        <!-- Header -->
        <?= $this->include('partials/header') ?>

        <!-- Sidebar -->
        <?= $this->include('partials/sidebar') ?>

        <!-- Content -->
        <div class="content-page">
            <div class="content">
                <?= $this->renderSection('content') ?>
            </div>

            <!-- Footer -->
            <?= $this->include('partials/footer') ?>
        </div>
    </div>


    <!-- Scripts -->
    <script src="<?= base_url('') ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url('') ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?= base_url('') ?>assets/js/app.min.js"></script>
    <script src="<?= base_url('') ?>assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>


</html>