<?php
$session = session();
$username = $session->get('username');
?>
<div class="navbar-custom">
    <ul class="list-unstyled topnav-menu float-right mb-0">
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user" data-toggle="dropdown" href="#">
                <img src="<?=base_url('assets/images/users/avatar-1.jpg')?>" alt="user-image" class="rounded-circle">
                <span class="pro-user-name ml-1"><?= esc($username) ?> <i class="mdi mdi-chevron-down"></i></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?= base_url('/logout') ?>" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</div>
