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
    <hr />
    <div id="sidebar-menu">
        <ul class="metismenu" id="side-menu">
            <li><a href="<?= base_url('/dashboard') ?>"><i class="mdi mdi-view-dashboard"></i></i> <span> Dashboard </span></a></li>
            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-account-multiple-outline"></i>
                    <span> Quản lý nhân viên </span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/user') ?>">Nhân viên</a></li>
                    <li><a href="<?= base_url('/user/manageRoles') ?>">Vai trò</a></li>
                    <li><a href="<?= base_url('/permissions') ?>">Chức năng</a></li>
                </ul>
            </li>
            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-account-outline"></i>
                    <span> Khách hàng</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/customers') ?>">Danh sách khách hàng</a></li>
                    <li><a href="<?= base_url('/customers/sub-customers') ?>">Danh sách mã phụ</a></li>
                    <li><a href="<?= base_url('/customers/create') ?>">Thêm mới</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-shape-outline"></i>
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
                    <i class="mdi mdi-cart-outline"></i>
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
                    <i class="mdi mdi-magnify"></i>
                    <span> Kiểm tra đơn hàng</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/order-inspections') ?>">Danh sách yêu cầu</a></li>
                    <li><a href="<?= base_url('/order-inspections/create') ?>">Thêm yêu cầu mới</a></li>
                </ul>
            </li>

            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-file-export-outline"></i>
                    <span> Phiếu xuất kho</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/invoices') ?>">Danh sách phiếu xuất</a></li>
                    <li><a href="<?= base_url('/invoices/cart') ?>">DS tạo phiếu</a></li>
                    <li><a href="<?= base_url('/shipping-provider') ?>">Danh sách nhà vận chuyển</a></li>
                    <li><a href="<?= base_url('/shipping-manager') ?>">Quản lý vận chuyển</a></li>
                </ul>
            </li>
            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-chart-bar "></i>
                    <span> Thống kê</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url() ?>accounting-statistics">Công nợ</a></li>
                    <li><a href="<?= base_url() ?>transactions">Lịch sử giao dịch</a></li>
                    <li><a href="<?= base_url() ?>invoices/overdue">Quá hạn thanh toán</a></li>
                    <li><a href="<?= base_url() ?>packages">Quản lý bao hàng</a></li>
                    <li><a href="<?= base_url() ?>financial">Quản lý thu chi</a></li>
                    <li><a href="<?= base_url() ?>funds">Quản lý quỹ</a></li>
                </ul>
            </li>
            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-account-group"></i>
                    <span> Cộng tác viên</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url() ?>affiliate-mapping">Danh sách Mapping</a></li>
                    <li><a href="<?= base_url() ?>affiliate-pricing">Danh sách Bảng giá</a></li>
                    <li><a href="<?= base_url() ?>affiliate-mapping/create">Thêm mới mapping</a></li>
                    <li><a href="<?= base_url() ?>affiliate-pricing/create">Thêm mới bảng giá</a></li>
                    <li><a href="<?= base_url() ?>affiliate-commission">Danh sách hoa hồng</a></li>
                </ul>
            </li>
            <li>
                <a href="javascript: void(0);">
                    <i class="mdi mdi-settings-outline"></i>
                    <span> Cài đặt</span>
                    <span class="menu-arrow"></span>
                </a>
                <ul class="nav-second-level" aria-expanded="false">
                    <li><a href="<?= base_url('/exchange-rates/update-form') ?>">Tỷ giá</a></li>
                    <li><a href="<?= base_url('/exchange-rates') ?>">Lịch sự cập nhật</a></li>
                </ul>
            </li>
            <li><a href="<?= base_url('/profile') ?>"><i class="mdi mdi-account-circle-outline"></i> <span> Profile </span></a></li>
            <li><a href="<?= base_url('/tracking') ?>" target="_blank"><i class="mdi mdi-package-variant"></i> Tra mã vận đơn</a></li>
            <?php if (in_array($role, ['Quản lý', 'Quản trị viên'])): ?>
                <li><a href="<?= base_url('/system-logs') ?>"><i class="mdi mdi-history"></i> <span> Lịch sử hệ thống</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>