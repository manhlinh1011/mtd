<?php

function hasPermission($userId, $permissionName)
{
    $db = \Config\Database::connect();

    $query = $db->table('permissions')
        ->select('permissions.id')
        ->join('role_permissions', 'permissions.id = role_permissions.permission_id')
        ->join('user_roles', 'role_permissions.role_id = user_roles.role_id')
        ->where('user_roles.user_id', $userId)
        ->where('permissions.permission_name', $permissionName)
        ->get();

    return $query->getNumRows() > 0;
}
