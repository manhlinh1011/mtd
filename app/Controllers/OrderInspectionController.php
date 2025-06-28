<?php

namespace App\Controllers;

use App\Models\OrderInspectionModel;
use App\Models\SystemLogModel;

class OrderInspectionController extends BaseController
{
    protected $orderInspectionModel;
    protected $systemLogModel;

    public function __construct()
    {
        $this->orderInspectionModel = new OrderInspectionModel();
        $this->systemLogModel = new SystemLogModel();
    }

    /**
     * Hiển thị danh sách đơn hàng cần kiểm tra
     */
    public function index()
    {
        $perPage = 50;

        $data['inspections'] = $this->orderInspectionModel
            ->select('order_inspections.*, orders.id as order_id, orders.vietnam_stock_date')
            ->join('orders', 'orders.tracking_code = order_inspections.tracking_code', 'left')
            ->orderBy('order_inspections.created_at', 'DESC')
            ->paginate($perPage);

        $data['pager'] = $this->orderInspectionModel->pager;

        // Thống kê
        $data['totalPending'] = $this->orderInspectionModel->where('notify_checked', 0)->countAllResults();
        $data['totalNotified'] = $this->orderInspectionModel->where('notify_checked', 1)->countAllResults();

        return view('order_inspections/index', $data);
    }

    /**
     * Form tạo yêu cầu kiểm tra mới
     */
    public function create()
    {
        return view('order_inspections/create');
    }

    /**
     * Lưu yêu cầu kiểm tra mới
     */
    public function store()
    {
        $trackingCode = trim($this->request->getPost('tracking_code'));
        $notes = trim($this->request->getPost('notes'));

        // Validation cơ bản - chỉ kiểm tra tracking_code
        if (empty($trackingCode)) {
            session()->setFlashdata('error', 'Mã vận chuyển không được để trống!');
            return redirect()->back()->withInput();
        }

        // Kiểm tra xem tracking code đã tồn tại chưa
        if ($this->orderInspectionModel->isTrackingCodeExists($trackingCode)) {
            session()->setFlashdata('error', 'Mã vận chuyển này đã có trong danh sách kiểm tra!');
            return redirect()->back()->withInput();
        }

        $data = [
            'tracking_code' => $trackingCode,
            'notes' => $notes ?: '', // Đảm bảo notes không null
            'notify_checked' => 0
        ];

        try {
            $result = $this->orderInspectionModel->insert($data);

            if ($result) {
                // Ghi log
                $this->systemLogModel->addLog([
                    'entity_type' => 'order_inspection',
                    'entity_id' => $result,
                    'action_type' => 'create',
                    'created_by' => session()->get('user_id'),
                    'details' => json_encode($data),
                    'notes' => "Tạo yêu cầu kiểm tra cho mã vận chuyển: {$trackingCode}"
                ]);

                session()->setFlashdata('success', 'Đã thêm yêu cầu kiểm tra thành công!');
                return redirect()->to('/order-inspections');
            } else {
                session()->setFlashdata('error', 'Có lỗi xảy ra khi tạo yêu cầu kiểm tra!');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Xóa yêu cầu kiểm tra
     */
    public function delete($id)
    {
        log_message('info', 'Delete method called with ID: ' . $id);

        $inspection = $this->orderInspectionModel->find($id);

        if (!$inspection) {
            log_message('error', 'Inspection not found with ID: ' . $id);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Yêu cầu kiểm tra không tồn tại!'
            ]);
        }

        log_message('info', 'Found inspection: ' . json_encode($inspection));

        if ($this->orderInspectionModel->delete($id)) {
            log_message('info', 'Successfully deleted inspection with ID: ' . $id);

            // Ghi log
            $this->systemLogModel->addLog([
                'entity_type' => 'order_inspection',
                'entity_id' => $id,
                'action_type' => 'delete',
                'created_by' => session()->get('user_id'),
                'details' => json_encode($inspection),
                'notes' => "Xóa yêu cầu kiểm tra cho mã vận chuyển: {$inspection['tracking_code']}"
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã xóa yêu cầu kiểm tra thành công!'
            ]);
        }

        log_message('error', 'Failed to delete inspection with ID: ' . $id);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi xóa!'
        ]);
    }

    /**
     * Đánh dấu đã thông báo
     */
    public function markAsNotified($id)
    {
        if ($this->orderInspectionModel->markAsNotified($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo thành công!'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Có lỗi xảy ra!'
        ]);
    }

    /**
     * Đánh dấu tất cả đã thông báo
     */
    public function markAllAsNotified()
    {
        if ($this->orderInspectionModel->markAllAsNotified()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả thông báo thành công!'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Có lỗi xảy ra!'
        ]);
    }

    /**
     * API lấy 1 đơn hàng cần kiểm tra (cho client C#)
     * Trả về đơn cũ nhất chưa thông báo
     */
    public function getPendingInspections()
    {
        $pendingInspections = $this->orderInspectionModel->getPendingNotifications();

        return $this->response->setJSON([
            'success' => true,
            'count' => count($pendingInspections),
            'data' => $pendingInspections
        ]);
    }

    /**
     * API xác nhận đã thông báo dựa trên tracking_code
     */
    public function confirmNotification()
    {
        // Kiểm tra method POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method không được hỗ trợ'
            ])->setStatusCode(405);
        }

        $trackingCode = trim($this->request->getPost('tracking_code'));

        // Validation
        if (empty($trackingCode)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tracking code không được để trống'
            ])->setStatusCode(400);
        }

        try {
            // Tìm yêu cầu kiểm tra theo tracking_code
            $inspection = $this->orderInspectionModel
                ->where('tracking_code', $trackingCode)
                ->where('notify_checked', 0)
                ->first();

            if (!$inspection) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu kiểm tra cho tracking code này hoặc đã được xác nhận'
                ])->setStatusCode(404);
            }

            // Cập nhật trạng thái đã thông báo
            $result = $this->orderInspectionModel->update($inspection['id'], [
                'notify_checked' => 1
            ]);

            if ($result) {


                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Đã xác nhận thông báo thành công',
                    'data' => [
                        'id' => $inspection['id'],
                        'tracking_code' => $trackingCode
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật trạng thái'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * API cho n8n: Tạo mới đơn kiểm tra qua API
     * Input: tracking_code, notes (POST hoặc JSON)
     * Output: JSON
     */
    public function setInspectionByApi()
    {
        // Ưu tiên lấy từ POST (form-data, x-www-form-urlencoded)
        $trackingCode = $this->request->getPost('tracking_code');
        $notes = $this->request->getPost('notes');

        // Nếu không có, thử lấy từ JSON (chỉ khi Content-Type là application/json)
        if ($trackingCode === null) {
            $json = $this->request->getJSON(true);
            if ($json) {
                $trackingCode = $json['tracking_code'] ?? null;
                $notes = $json['notes'] ?? '';
            }
        }

        if (empty($trackingCode)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Thiếu tracking_code!'
            ])->setStatusCode(400);
        }

        // Kiểm tra tracking_code đã tồn tại trong bảng order_inspections (bất kể notify_checked)
        $inspection = $this->orderInspectionModel->where('tracking_code', $trackingCode)->first();
        if ($inspection) {
            if ($inspection['notify_checked'] == 0) {
                $msg = sprintf(
                    'Mã vận đơn %s đã được set chờ kiểm lúc %s. Đang đợi về kho Trung Quốc.',
                    $trackingCode,
                    isset($inspection['created_at']) ? date('d/m/Y H:i', strtotime($inspection['created_at'])) : 'N/A'
                );
            } else {
                // Lấy thời gian created_at trong bảng orders nếu có
                $orderModel = new \App\Models\OrderModel();
                $order = $orderModel->where('tracking_code', $trackingCode)->first();
                $checkedTime = $order && isset($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : (isset($inspection['created_at']) ? date('d/m/Y H:i', strtotime($inspection['created_at'])) : 'N/A');
                $msg = sprintf(
                    'Mã vận đơn %s đã về kho trung quốc và kiểm lúc %s.',
                    $trackingCode,
                    $checkedTime
                );
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => $msg
            ])->setStatusCode(200);
        }

        $data = [
            'tracking_code' => $trackingCode,
            'notes' => $notes ?: '',
            'notify_checked' => 0
        ];

        try {
            $result = $this->orderInspectionModel->insert($data);
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Đã thêm yêu cầu kiểm tra thành công cho mã vận đơn ' . $trackingCode . '!',
                    'id' => $result
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo yêu cầu kiểm tra!'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
