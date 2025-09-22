<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'LMS.PERMISSIONS';
    protected $primaryKey = 'PERMISSION_ID';
    public $timestamps = false;
    protected $connection = 'oracle';

    protected $fillable = [
        'PERMISSION_NAME',
        'USER_ID',
        'CREATED_AT',
        'UPDATED_AT',
        'CREATED_BY',
        'UPDATED_BY',
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
    ];

    // A permission belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'ID');
    }

public function roles()
{
    return $this->belongsToMany(
        Role::class,
        'LMS.PERMISSION_ROLE',   // Pivot
        'PERMISSION_ID',         // FK for Permission
        'ROLE_ID'                // FK for Role
    )
    ->withPivot([
        'USER_ID',
        'CREATED_BY', 'CREATED_AT',
        'UPDATED_BY', 'UPDATED_AT',
        'DELETED_BY', 'DELETED_AT'
    ])
    ->wherePivotNull('DELETED_AT');
}




}
