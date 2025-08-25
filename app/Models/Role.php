<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'ROLES';          
    protected $primaryKey = 'ROLE_ID';    // Primary key
    public $timestamps = false;          

    public function users()
    {
        return $this->belongsToMany(User::class, 'ROLE_USER', 'ROLE_ID', 'USER_ID');
    }
}
