<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportedFormat extends Model
{
    public $timestamps = false;

    public function files() {
        return $this->hasMany('App\File', 'supported_format_id');
    }
}
