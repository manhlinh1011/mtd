<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table = 'role_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'permission_id'];

    public function getPermissionsByRole($roleId)
    {
        return $this->select('permission_id')
            ->where('role_id', $roleId)
            ->findAll();
    }
}
