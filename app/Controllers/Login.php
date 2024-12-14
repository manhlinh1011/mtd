<?php


namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;

class Login extends BaseController
{
    public function index()
    {
        $session = session();

        // Nếu đã đăng nhập, chuyển hướng về Dashboard
        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('login'); // Hiển thị giao diện đăng nhập
    }

    public function authenticate()
    {

        $session = session();
        $model = new UserModel();
        $roleModel = new UserRoleModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $model->where('username', $username)->first();

        if ($user && password_verify($password, $user['password'])) {

            $role = $roleModel->select('roles.role_name')
                          ->join('roles', 'roles.id = user_roles.role_id')
                          ->where('user_roles.user_id', $user['id'])
                          ->first();

            $session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $role['role_name'], // Lưu role vào session
                'logged_in' => true
            ]);
            return redirect()->to('/dashboard');
        } else {
            $session->setFlashdata('error', 'Invalid Username or Password');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }
}
