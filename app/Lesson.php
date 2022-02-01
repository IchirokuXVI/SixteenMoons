<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['title', 'body'];

    public function course() {
        return $this->belongsTo('App\Course', 'course_id');
    }

    public function files() {
        return $this->hasMany('App\File', 'lesson_id');
    }

    public function comments() {
        return $this->hasMany('App\Comment', 'lesson_id');
    }
}
