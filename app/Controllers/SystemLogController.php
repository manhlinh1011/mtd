<?php

namespace App\Controllers;

class SystemLogController extends BaseController
{
    public function index()
    {
        if (!in_array(session('role'), ['Quản lý'])) {
            return redirect()->to('/dashboard')->with('error', 'Bạn không có quyền truy cập.');
        }

        $systemLogModel = new \App\Models\SystemLogModel();
        $userModel = new \App\Models\UserModel();

        $logs = $systemLogModel->orderBy('created_at', 'DESC')->findAll();

        // Lấy thông tin người thực hiện
        foreach ($logs as &$log) {
            $log['created_by_user'] = $userModel->find($log['created_by']);
        }

        return view('system_logs/index', [
            'logs' => $logs
        ]);
    }
}
