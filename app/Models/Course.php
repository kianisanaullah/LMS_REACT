<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model

{ 
    
   
    protected $table = 'LMS.COURSES';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'COURSE_NAME',
        'SHORT_NAME',
        'DESCRIPTION',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATED_BY',
        'UPDATED_AT',
        'DELETED_BY',
        'DELETED_AT',
        'USER_ID',
        'ATTACHMENTS',  
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',

        'ATTACHMENTS' => 'string', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'ID');
    }

    public function softDelete($userId)
    {
        $this->DELETED_BY = $userId;
        $this->DELETED_AT = now();
        $this->save();
    }
}
