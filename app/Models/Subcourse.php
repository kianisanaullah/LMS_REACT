<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcourse extends Model
{
    protected $table = 'LMS.SUBCOURSES';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'ID',
        'COURSE_ID',
        'SUBCOURSE_NAME',
        'DESCRIPTION',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATED_BY',
        'UPDATED_AT',
        'DELETED_BY',
        'DELETED_AT',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'COURSE_ID', 'ID');
    }
}
