<?php
namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class CustomRole extends SpatieRole
{
    protected $table = 'LMS.ROLES';
    protected $primaryKey = 'ROLE_ID';
    protected $connection = 'oracle';

    // Map Spatie's expected 'id' to your ROLE_ID
    public function getIdAttribute()
    {
        return $this->attributes['ROLE_ID'];
    }
}
