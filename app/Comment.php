<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function lesson() {
        return $this->belongsTo('App\Lesson', 'lesson_id');
    }
}
