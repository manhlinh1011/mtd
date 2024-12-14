<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Nếu chưa đăng nhập và truy cập các route không được phép
        if (!$session->get('logged_in') && !in_array($request->getPath(), ['login', 'register'])) {
            return redirect()->to('/login');
        }

        // Nếu đã đăng nhập và cố gắng truy cập trang login
        if ($session->get('logged_in') && $request->getPath() === 'login') {
            return redirect()->to('/dashboard');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Không cần xử lý sau
    }
}
