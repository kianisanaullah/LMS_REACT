<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'LMS.ROLES';
    protected $primaryKey = 'ROLE_ID';
    public $timestamps = false;
    protected $connection = 'oracle';

    protected $fillable = [
        'ROLE_NAME',
        'CREATED_AT',
        'UPDATED_AT',
        'CREATED_BY',
        'UPDATED_BY',
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
    ];

    /** ======================
     *   RELATIONS
     *  ====================== */

    // A role can belong to many users through USER_ROLE
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'LMS.USER_ROLE', // pivot table
            'ROLE_ID',       // FK in USER_ROLE
            'USER_ID'        // FK in USER_ROLE
        )->withPivot([
            'CREATED_BY', 'CREATED_AT',
            'UPDATED_BY', 'UPDATED_AT',
            'DELETED_BY', 'DELETED_AT'
        ])->wherePivotNull('DELETED_AT');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'LMS.PERMISSION_ROLE',   // Pivot table
            'ROLE_ID',               // FK in PERMISSION_ROLE
            'PERMISSION_ID'          // FK in PERMISSION_ROLE
        )->withPivot([
            'USER_ID',
            'CREATED_BY', 'CREATED_AT',
            'UPDATED_BY', 'UPDATED_AT',
            'DELETED_BY', 'DELETED_AT'
        ])->wherePivotNull('DELETED_AT');
    }
}
