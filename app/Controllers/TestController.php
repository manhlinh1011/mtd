<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TestController extends Controller
{
    public function index()
    {
        $client = \Config\Services::curlrequest();

        try {
            $response = $client->request('POST', 'https://ac890f.n8nrun.site/webhook/thongbaodon', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'name' => 'Test Customer',
                    'code' => 'TEST001',
                    'total_weight' => 10.5,
                    'thread_id_zalo_notify_order' => '123456',
                    'msg_zalo_type_notify_order' => 'test_type',
                    'tracking_code' => 'TEST123456'
                ]
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Test request sent successfully',
                'response' => $response->getBody()
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
