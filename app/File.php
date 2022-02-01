<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public function lesson() {
        return $this->belongsTo('App\Lesson', 'lesson_id');
    }

    public function format() {
        return $this->belongsTo('App\SupportedFormat', 'supported_format_id');
    }
}
