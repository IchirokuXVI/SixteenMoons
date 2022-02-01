<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    public $timestamps = false;

    public function notifications() {
        return $this->hasMany('App\Notification', 'type_id');
    }
}
