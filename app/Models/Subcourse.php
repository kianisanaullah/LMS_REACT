<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcourse extends Model
{
   
    protected $table = 'LMS.SUBCOURSES';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public $incrementing = true;
    protected $keyType = 'int';

protected $fillable = [
    'COURSE_ID',
    'SUBCOURSE_NAME',
    'DESCRIPTION',
    'CREATED_BY',
    'CREATED_AT',
    'UPDATED_BY',
    'UPDATED_AT',
    'DELETED_BY',
    'DELETED_AT',
    'USER_ID',
    'ATTACHMENTS', 
    'APPROVED', 
];


    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'DELETED_AT' => 'datetime',
        'APPROVED'   => 'boolean',

        'ATTACHMENTS' => 'string', 
    
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'COURSE_ID', 'ID');
    }
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

