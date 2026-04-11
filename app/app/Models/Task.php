<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $fillable = [ // chỉ định các trường có thể được gán hàng loạt (mass assignable) để bảo vệ chống lại việc gán hàng loạt không mong muốn
        'title',
        'description',
        'status',
        'priority',
        'due_date'
    ];
}
