<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'role_id', 'assigned_at'];

    // Hàm cập nhật role cho người dùng
    public function updateRole($user_id, $role_id)
    {
        return $this->update(['user_id' => $user_id], ['role_id' => $role_id]);
    }
}
