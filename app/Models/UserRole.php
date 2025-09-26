<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'LMS.USER_ROLE';
    protected $primaryKey = 'ID';
    public $timestamps = false; 

    protected $fillable = [
        'USER_ID',
        'ROLE_ID',
        'CREATED_BY', 'CREATED_AT',
        'UPDATED_BY', 'UPDATED_AT',
        'DELETED_BY', 'DELETED_AT',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'ID');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'ROLE_ID', 'ROLE_ID');
    }
}
