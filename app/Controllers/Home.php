<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $session = session();

        // Kiểm tra trạng thái đăng nhập
        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        return redirect()->to('/login');
        //return view('welcome_message');
    }
}
