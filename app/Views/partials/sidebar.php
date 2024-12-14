<?php
$session = session();
$username = $session->get('username');
$role = $session->get('role');
?>
<div class="left-side-menu">
    <div class="user-box">
        <div class="float-left">
            <img src="<?= base_url('assets/images/users/avatar-1.jpg') ?>" alt="" class="avatar-md rounded-circle">
        </div>
        <div class="user-info">
            <a href="#"><?= $username ?></a>
            <p class="text-muted m-0"><?= $role ?></p>
        </div>
    </div>
    <div id="sidebar-menu">
        <ul class="metismenu" id="side-menu">
            <li class="menu-title">Navigation</li>
            <li><a href="<?= base_url('/dashboard') ?>"><i class="mdi mdi-home-outline"></i> <span> Dashboard </span></a></li>
            <li>
                <a href="javascript: void(0);">
                    <i class=" fas fa-address-book"></i>
                    <span> Quản lý nhân viên </span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/user') ?>">Nhân viên</a></li>
                    <li><a href="<?= base_url('/user/manageRoles') ?>">Vai trò</a></li>
                </ul>
            </li>
            <li>
                <a href="javascript: void(0);">
                    <i class="far fa-address-book"></i>
                    <span> Khách hàng</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/customers') ?>">Danh sách</a></li>
                    <li><a href="<?= base_url('/customers/create') ?>">Thêm mới</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-format-list-bulleted-type"></i>
                    <span> Loại sản phẩm</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/product-types') ?>">Danh sách</a></li>
                    <li><a href="#">Thêm mới</a></li>
                </ul>
            </li>



            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-cube-outline"></i>
                    <span> Đơn hàng</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/orders') ?>">Danh sách</a></li>
                    <li><a href="#">Thêm mới</a></li>
                </ul>
            </li>
            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-format-list-bulleted-type"></i>
                    <span> Cài đặt</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/exchange-rates/update-form') ?>">Tỷ giá</a></li>
                    <li><a href="<?= base_url('/exchange-rates') ?>">Lịch sự cập nhật</a></li>
                </ul>
            </li>
            <li><a href="<?= base_url('/profile') ?>"><i class="ti-user"></i> <span> Profile </span></a></li>
        </ul>
    </div>
</div>