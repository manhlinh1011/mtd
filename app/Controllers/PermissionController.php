<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;

class PermissionController extends BaseController
{
    protected $permissionModel;
    protected $roleModel;
    protected $rolePermissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->roleModel = new RoleModel();
        $this->rolePermissionModel = new RolePermissionModel();
    }

    // Danh sách quyền
    public function index()
    {
        $permissions = $this->permissionModel->findAll();
        return view('permissions/index', ['permissions' => $permissions]);
    }

    // Tạo quyền mới
    public function create()
    {
        return view('permissions/create');
    }

    public function store()
    {
        $this->permissionModel->save($this->request->getPost());
        return redirect()->to('/permissions')->with('success', 'Quyền mới đã được thêm thành công.');
    }

    public function assign()
    {


        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $rolePermissionModel = new RolePermissionModel();

        // Lấy danh sách vai trò
        $roles = $roleModel->findAll();

        // Lấy danh sách quyền
        $permissions = $permissionModel->findAll();

        // Xác định vai trò được chọn
        $selectedRole = $this->request->getPost('role_id');

        $assignedPermissions = [];
        if ($selectedRole) {
            // Lấy danh sách quyền đã gán cho vai trò
            $rolePermissions = $rolePermissionModel->getPermissionsByRole($selectedRole);

            // Chuyển đổi mảng quyền thành một mảng đơn giản chứa ID
            $assignedPermissions = array_column($rolePermissions, 'permission_id');
        }


        // Truyền dữ liệu ra view
        return view('permissions/assign', [
            'roles' => $roles,
            'permissions' => $permissions,
            'selectedRole' => $selectedRole,
            'assignedPermissions' => $assignedPermissions,
        ]);
    }

    public function saveAssignedPermissions()
    {
        $db = \Config\Database::connect();

        // Lấy dữ liệu từ request
        $roleId = $this->request->getPost('role_id');
        $permissionIds = $this->request->getPost('permissions') ?? [];

        if ($roleId) {
            // Xóa quyền cũ
            $db->table('role_permissions')->where('role_id', $roleId)->delete();

            // Thêm quyền mới
            foreach ($permissionIds as $permissionId) {
                $db->table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        return redirect()->to('/permissions/assign')->with('success', 'Quyền đã được gán thành công!');
    }

    public function storeAssignment()
    {
        $roleId = $this->request->getPost('role_id');
        $permissionIds = $this->request->getPost('permission_ids');

        // Xóa quyền cũ
        $this->rolePermissionModel->where('role_id', $roleId)->delete();

        // Thêm quyền mới
        foreach ($permissionIds as $permissionId) {
            $this->rolePermissionModel->save([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }

        return redirect()->to('/permissions/assign')->with('success', 'Quyền đã được gán thành công.');
    }
}
