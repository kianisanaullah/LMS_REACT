<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'oracle'; 
    protected $table = 'LMS.USERS';
    protected $primaryKey = 'ID';
    public $timestamps = false;

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

    /** ======================
     *   RELATIONS
     *  ====================== */

    // A user can have many roles through USER_ROLE
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'LMS.USER_ROLE', // pivot table
            'USER_ID',       // FK in USER_ROLE
            'ROLE_ID'        // FK in USER_ROLE
        )->withPivot([
            'CREATED_BY', 'CREATED_AT',
            'UPDATED_BY', 'UPDATED_AT',
            'DELETED_BY', 'DELETED_AT'
        ])->wherePivotNull('DELETED_AT');
    }

    /** ======================
     *   HELPER METHODS
     *  ====================== */

    public function hasRole($roleName)
    {
        return $this->roles()->where('ROLE_NAME', $roleName)->exists();
    }

    public function hasPermission($permissionName)
    {
        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permissionName) {
                $q->where('PERMISSION_NAME', $permissionName);
            })->exists();
    }
}
