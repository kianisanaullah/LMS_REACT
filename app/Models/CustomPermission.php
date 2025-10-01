<?php
namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class CustomPermission extends SpatiePermission
{
    protected $table = 'LMS.PERMISSIONS';
    protected $primaryKey = 'PERMISSION_ID';
    protected $connection = 'oracle';

    public function getIdAttribute()
    {
        return $this->attributes['PERMISSION_ID'];
    }
}
