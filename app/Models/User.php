<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'oracle'; // your DB connection name in config/database.php
    protected $table = 'LMS.USERS';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'NAME', 'USERNAME', 'EMAIL', 'USER_PASSWORD',
        'EMPID', 'REMEMBER_TOKEN', 'CREATED_AT', 'UPDATED_AT',
        'OFFICE_ID', 'CREATED_BY', 'UPDATED_BY',
        'ACTIVE', 'ONLINE', 'LAST_LOGIN', 'LAST_SESSION',
        'FORGETPASS_TOKEN', 'PASSWORD_UPDATEDAT',
        'DESIGNATION_ROLES_CHECKED', 'PASSWORD_UPDATEDBY',
        'PASSWORD_FORCE_RESET', 'OTP', 'OTP_SENT_AT',
        'OTP_EXPIRY', 'OTP_VERIFIED',
    ];

    protected $hidden = [
        'USER_PASSWORD', 'REMEMBER_TOKEN', 'FORGETPASS_TOKEN', 'OTP',
    ];

   
    public function getAuthPassword()
    {
        return $this->USER_PASSWORD;
    }

   
    public function getAuthIdentifierName()
    {
        return 'EMAIL';
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'LMS.ROLE_USER', 'USER_ID', 'ROLE_ID');
    }
}
