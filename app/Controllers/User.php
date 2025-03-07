<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;

class User extends BaseController
{
    public function index()
    {
        $userModel = new \App\Models\UserModel();
        $userRoleModel = new \App\Models\UserRoleModel();
        $roleModel = new \App\Models\RoleModel();

        // Lấy danh sách người dùng cùng với vai trò
        $users = $userModel->findAll();

        foreach ($users as &$user) {
            $userRoles = $userRoleModel->where('user_id', $user['id'])->findAll();
            $roleIds = array_column($userRoles, 'role_id');

            if (!empty($roleIds)) {
                $roles = $roleModel->whereIn('id', $roleIds)->findAll();
            } else {
                $roles = []; // Không có vai trò
            }

            $user['roles'] = $roles;
        }


        return view('user/index', ['users' => $users]);
    }



    // Thêm người dùng mới
    public function create()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();
        return view('user/create', ['roles' => $roles]);
    }

    public function store()
    {
        $session = session();

        // Kiểm tra quyền của người dùng
        if ($session->get('role') !== 'Quản lý') {
            return redirect()->to('/login')->with('error', 'Bạn không có quyền thêm người dùng.');
        }

        $userModel = new UserModel();
        $userRoleModel = new UserRoleModel();

        // Lấy dữ liệu từ request
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $role = $this->request->getPost('role');

        // Kiểm tra dữ liệu hợp lệ
        if (empty($username) || empty($email) || empty($password) || empty($role)) {
            return redirect()->back()->withInput()->with('error', 'Vui lòng điền đầy đủ thông tin.');
        }

        // Kiểm tra email hợp lệ
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Email không hợp lệ.');
        }

        // Kiểm tra nếu username hoặc email đã tồn tại
        if ($userModel->where('username', $username)->first() || $userModel->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('error', 'Username hoặc email đã tồn tại.');
        }



        // Bắt đầu transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Lưu thông tin user
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];
            $userModel->save($userData);
            $userId = $userModel->getInsertID();

            // Lưu vai trò user
            $userRoleModel->save([
                'user_id' => $userId,
                'role_id' => $role
            ]);

            // Commit transaction
            $db->transComplete();

            // Kiểm tra nếu có lỗi
            if ($db->transStatus() === false) {
                throw new \Exception('Không thể lưu dữ liệu. Vui lòng thử lại.');
            }

            return redirect()->to('/user')->with('success', 'Thêm người dùng thành công.');
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    // Sửa thông tin người dùng
    public function edit($id)
    {
        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();

        // Truy vấn thông tin người dùng, bao gồm vai trò
        $user = $userModel
            ->select('users.*, user_roles.role_id')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left') // Join với bảng user_roles
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return redirect()->to('/user')->with('error', 'Người dùng không tồn tại.');
        }

        // Lấy danh sách roles
        $roles = $roleModel->findAll();

        $data = [
            'user'  => $user,
            'roles' => $roles,
        ];

        return view('user/edit', $data);
    }



    public function update($id)
    {
        $userModel = new \App\Models\UserModel();
        $userRoleModel = new \App\Models\UserRoleModel();

        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to('/user')->with('error', 'Người dùng không tồn tại.');
        }

        // Dữ liệu người dùng
        $data = [
            'fullname' => $this->request->getPost('fullname'),
            'email'    => $this->request->getPost('email'),
        ];

        // Xử lý mật khẩu nếu nhập mới
        if ($password = $this->request->getPost('password')) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Trong phương thức update()
        $file = $this->request->getFile('profile_picture');
        if (
            $file && $file->isValid() && !$file->hasMoved()
        ) {
            $fileName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads', $fileName); // Thay đổi đường dẫn thành public/uploads
            $data['profile_picture'] = $fileName;
        }

        $userModel->update($id, $data);

        // Cập nhật role
        $userRoleModel->where('user_id', $id)->delete();
        $userRoleModel->save([
            'user_id' => $id,
            'role_id' => $this->request->getPost('role'),
        ]);

        return redirect()->to('/user')->with('success', 'Cập nhật thông tin thành công.');
    }


    // Xóa người dùng
    public function delete($id)
    {
        $session = session();
        if ($session->get('role') !== 'Quản lý') {
            return redirect()->to('/login');
        }

        $model = new UserModel();
        $model->delete($id);
        return redirect()->to('/user');
    }

    // Quản lý vai trò: Thêm, sửa, xóa vai trò
    public function manageRoles()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();
        return view('role/manage', ['roles' => $roles]);
    }

    public function createRole()
    {
        return view('role/create');
    }

    public function storeRole()
    {
        $roleModel = new RoleModel();
        $roleModel->save([
            'role_name' => $this->request->getPost('role_name'),
            'description' => $this->request->getPost('description')
        ]);
        return redirect()->to('/user/manageRoles');
    }

    public function editRole($id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);
        return view('role/edit', ['role' => $role]);
    }

    public function updateRole($id)
    {
        $roleModel = new RoleModel();
        $roleModel->save([
            'id' => $id,
            'role_name' => $this->request->getPost('role_name'),
            'description' => $this->request->getPost('description')
        ]);
        return redirect()->to('/user/manageRoles');
    }

    public function deleteRole($id)
    {
        $roleModel = new RoleModel();
        $roleModel->delete($id);
        return redirect()->to('/user/manageRoles');
    }
}
