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

    <link href="<?= base_url('assets/libs/datatables/dataTables.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/datatables/buttons.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/datatables/responsive.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/libs/datatables/select.bootstrap4.css') ?>" rel="stylesheet" type="text/css">
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

    <script src="<?= base_url('') ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url('') ?>assets/libs/morris-js/morris.min.js"></script>
    <script src="<?= base_url('') ?>assets/libs/raphael/raphael.min.js"></script>
    <script src="<?= base_url('') ?>assets/js/pages/dashboard.init.js"></script>
    <script src="<?= base_url('') ?>assets/js/app.min.js"></script>



</body>

</html>