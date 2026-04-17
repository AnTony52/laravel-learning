<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    //
    protected $fillable = [ // chỉ định các trường có thể được gán hàng loạt (mass assignable) để bảo vệ chống lại việc gán hàng loạt không mong muốn
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
