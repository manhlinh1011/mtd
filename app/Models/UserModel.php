<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'fullname',
        'username',
        'email',
        'password',
        'profile_picture',
        'affiliate_balance',
        'bank_account',
        'bank_name',
        'account_holder',
        'created_at',
        'updated_at'
    ];
}
