<?php
$session = session();
$username = $session->get('username');
?>
<!-- Toast Notification -->


<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">
        <!-- Cart Icon -->
        <li class="dropdown notification-list">
            <a href="<?= base_url('invoices/cart') ?>" class="nav-link nav-user">
                <i class="mdi mdi-cart" style="font-size: 20px;"></i>
                <span id="cart-count" class="badge bg-danger">
                    <?php
                    if ($session->get('cart') !== null && is_array($session->get('cart'))) {
                        echo count($session->get('cart'));
                    } else {
                        echo '0';
                    }

                    ?>
                </span>
            </a>
        </li>

        <!-- User Dropdown -->
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user" data-toggle="dropdown" href="#">
                <img src="<?= base_url('assets/images/users/avatar-1.jpg') ?>" alt="user-image" class="rounded-circle">
                <span class="pro-user-name ml-1"><?= esc($username) ?> <i class="mdi mdi-chevron-down"></i></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-dropdown">
                <a href="/logout" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout"></i> Đăng xuất
                </a>
            </div>
        </li>

    </ul>

    <div class="logo-box">
        <a href="<?= base_url() ?>" class="logo text-center">
            <span class="logo-lg">
                <img src="<?= base_url() ?>assets\images\logo.png" alt="" height="26">
                <!-- <span class="logo-lg-text-light">Simple</span> -->
            </span>
            <span class="logo-sm">
                <!-- <span class="logo-sm-text-dark">S</span> -->
                <img src="<?= base_url() ?>assets\images\logo.png" alt="" height="22">
            </span>
        </a>
    </div>

    <div id="navigation" class="active" style="color:#ffffff">
        <!-- Navigation Menu-->
        <ul class="navigation-menu">

            <li class="has-submenu">
                <a href="<?= base_url() ?>">
                    <i class="ti-home"></i>Dashboard
                </a>
            </li>

            <li class="has-submenu">
                <a href="https://gokien247.com/" target="_blank"> <i class="mdi mdi-web"></i></i>Web Gõ Kiến</a>
            </li>

            <li class="has-submenu">

                <?php if (isset($exchangeRate)) : ?>
                    <a class="text-danger" href="<?= base_url() ?>exchange-rates"><i class="mdi mdi-bell-outline"></i>Tỷ giá hôm nay: <?= number_format($exchangeRate, 0, ',', '.') ?>đ</a>
                <?php endif; ?>
            </li>
        </ul>
        <!-- End navigation menu -->
        <div style="padding-top: 16px; ">
            <a href="<?= base_url() ?>invoices/cart" class="btn btn-outline-success"><i class="mdi mdi-barcode-scan"></i> Bắn mã tạo phiếu xuất</a>
        </div>

        <div class="clearfix"></div>
    </div>
</div>
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <!-- Các thông báo sẽ được thêm vào đây -->
</div>