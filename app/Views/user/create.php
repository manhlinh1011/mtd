<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script>
    function validateForm(event) {
        event.preventDefault();

        const fullname = document.getElementById("fullname").value.trim();
        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        const usernameRegex = /^[a-zA-Z0-9]+$/;

        if (fullname === "") {
            alert("Full name is required.");
            return false;
        }

        if (username === "") {
            alert("Username is required.");
            return false;
        }
        if (!usernameRegex.test(username)) {
            alert("Username must be alphanumeric and contain no spaces or special characters.");
            return false;
        }

        if (email === "") {
            alert("Email is required.");
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        if (password === "") {
            alert("Password is required.");
            return false;
        }

        if (role === "") {
            alert("Please select a role.");
            return false;
        }

        document.getElementById("createForm").submit();
    }
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <h4 class="m-t-0 header-title">Thêm mới nhân viên</h4>
                <p class="text-muted m-b-30 font-14">
                    Điền đầy đủ thông tin sau
                </p>
                <?php if (session('error')): ?>
                    <div class="alert alert-danger">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>
                <form action="<?= base_url('/user/store') ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group row">
                        <label for="fullname" class="col-sm-2 col-form-label">Full Name:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Họ và tên" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="username" class="col-sm-2 col-form-label">Username:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">Email:</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Password:</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="role" class="col-sm-2 col-form-label">Role:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= $role['role_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="profile_picture" class="col-sm-2 col-form-label">Profile Picture:</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control-file" id="profile_picture" name="profile_picture" accept="image/*">
                        </div>
                    </div>

                    <!-- Thông tin cộng tác viên -->
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Thông tin cộng tác viên:</label>
                        <div class="col-sm-10">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label for="affiliate_balance" class="col-sm-3 col-form-label">Số dư hoa hồng:</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="affiliate_balance" name="affiliate_balance" value="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="bank_account" class="col-sm-3 col-form-label">Số tài khoản:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="bank_account" name="bank_account" placeholder="Nhập số tài khoản">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="bank_name" class="col-sm-3 col-form-label">Tên ngân hàng:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Nhập tên ngân hàng">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="account_holder" class="col-sm-3 col-form-label">Tên chủ tài khoản:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="account_holder" name="account_holder" placeholder="Nhập tên chủ tài khoản">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10 offset-sm-2">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Submit</button>
                            <button type="reset" class="btn btn-secondary waves-effect m-l-5">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>