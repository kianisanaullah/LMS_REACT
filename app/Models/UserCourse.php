<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{
    protected $table = 'LMS.USER_COURSES';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'USER_ID',
        'COURSE_ID',
        'ENROLLED_AT',
    ];


}
