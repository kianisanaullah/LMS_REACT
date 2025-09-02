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
//    protected $guarded=[];

    protected $fillable = [
        'name', 'username', 'email', 'password',
        'empid', 'remember_token', 'created_at', 'updated_at',
        'office_id', 'created_by', 'updated_by',
        'active', 'online', 'last_login', 'last_session',
        'forgetpass_token', 'password_updatedat',
        'designation_roles_checked', 'password_updatedby',
        'password_force_reset', 'otp', 'otp_sent_at',
        'otp_expiry', 'otp_verified',
    ];

    protected $hidden = [
        'password', 'remember_token', 'forgetpass_token', 'otp',
    ];


    public function getAuthPassword()
    {
        return $this->password;
    }


    public function getAuthIdentifierName()
    {
        return 'email';
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'LMS.ROLE_USER', 'USER_ID', 'ROLE_ID');
    }
    
}
